<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Events\MessageSent;
use App\Events\MessageSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // Consider user online if pinged within last N seconds
    private int $onlineSeconds = 10;

    private function isOnline(User $u): bool
    {
        if (!$u->last_seen_at) return false;
        return $u->last_seen_at->diffInSeconds(now()) <= $this->onlineSeconds;
    }

    public function users(Request $request)
    {
        $authId = $request->user()->id;

        return User::where('id', '!=', $authId)
            ->select('id', 'name', 'username', 'image', 'last_seen_at')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'username'  => $u->username,
                'image_url' => $u->image_url,
                'isOnline'  => $this->isOnline($u),
                'lastSeen'  => $u->last_seen_at?->toIso8601String(),
            ]);
    }

    public function conversations(Request $request)
    {
        $auth = $request->user();

        return Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->with([
                'participants' => fn ($q) => $q->select('users.id', 'users.name', 'users.username', 'users.image', 'users.last_seen_at'),
                // ✅ include sender image for UI avatar
                'lastMessage.sender:id,name,image',
            ])
            ->get()
            ->map(function ($conv) use ($auth) {
                $other = $conv->participants->firstWhere('id', '!=', $auth->id);
                $pivot = $conv->participants->firstWhere('id', $auth->id)?->pivot;

                $unread = Message::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $auth->id)
                    ->when($pivot?->last_read_at, fn ($q) =>
                        $q->where('created_at', '>', $pivot->last_read_at)
                    )
                    ->count();

                return [
                    'id' => $conv->id,
                    'other_user' => $other ? [
                        'id'        => $other->id,
                        'name'      => $other->name,
                        'image_url' => $other->image_url,
                        'isOnline'  => ($other->last_seen_at && $other->last_seen_at->diffInSeconds(now()) <= $this->onlineSeconds),
                        'lastSeen'  => $other->last_seen_at?->toIso8601String(),
                    ] : null,
                    'last_message' => $conv->lastMessage ? [
                        'id'          => $conv->lastMessage->id,
                        'body'        => $conv->lastMessage->body,
                        'sender'      => [
                            'id'        => $conv->lastMessage->sender->id,
                            'name'      => $conv->lastMessage->sender->name,
                            'image_url' => $conv->lastMessage->sender->image_url,
                        ],
                        'created_at'  => $conv->lastMessage->created_at->toIso8601String(),
                        'delivered_at'=> optional($conv->lastMessage->delivered_at)->toIso8601String(),
                        'seen_at'     => optional($conv->lastMessage->seen_at)->toIso8601String(),
                    ] : null,
                    'unread_count' => $unread,
                ];
            });
    }

    private function findDirectConversation(int $userA, int $userB): ?Conversation
    {
        return Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userA))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userB))
            ->first();
    }

    public function conversationWithUser(Request $request, User $user)
    {
        $auth = $request->user();
        $now  = now();

        $conversation = $this->findDirectConversation($auth->id, $user->id);

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($auth, $user) {
                $conv = Conversation::create([
                    'type' => 'direct',
                    'created_by' => $auth->id,
                ]);

                $conv->participants()->attach([$auth->id, $user->id]);
                return $conv;
            });
        }

        // ✅ mark seen for messages from other user
        $unseenIds = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $user->id)
            ->whereNull('seen_at')
            ->pluck('id')
            ->toArray();

        if (!empty($unseenIds)) {
            Message::whereIn('id', $unseenIds)->update(['seen_at' => $now]);

            // ✅ broadcast seen
            try {
                broadcast(new MessageSeen(
                    sender_id: $user->id,
                    conversation_id: $conversation->id,
                    message_ids: $unseenIds,
                    seen_at: $now->toIso8601String()
                ))->toOthers();
            } catch (\Throwable $e) {}
        }

        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => $now]);

        $messages = Message::where('conversation_id', $conversation->id)
            // ✅ include sender image
            ->with('sender:id,name,image')
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'sender'      => [
                    'id'        => $m->sender->id,
                    'name'      => $m->sender->name,
                    'image_url' => $m->sender->image_url,
                ],
                'created_at'  => $m->created_at->toIso8601String(),
                'delivered_at'=> optional($m->delivered_at)->toIso8601String(),
                'seen_at'     => optional($m->seen_at)->toIso8601String(),
            ]);

        return response()->json([
            'conversation' => ['id' => $conversation->id],
            'messages' => ['data' => $messages],
        ]);
    }

    public function sendMessage(Request $request)
    {
        $auth = $request->user();

        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string',
        ]);

        $receiver = User::findOrFail($data['receiver_id']);
        $now = now();

        // ✅ FIX: get conversation ONLY between auth & receiver
        $conversation = $this->findDirectConversation($auth->id, $receiver->id);

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($auth, $receiver) {
                $conv = Conversation::create([
                    'type' => 'direct',
                    'created_by' => $auth->id,
                ]);
                $conv->participants()->attach([$auth->id, $receiver->id]);
                return $conv;
            });
        }

        $message = $conversation->messages()->create([
            'sender_id' => $auth->id,
            'body' => $data['body'],
            'delivered_at' => $now,
        ]);

        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => $now]);

        // ✅ realtime broadcast
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json([
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'body' => $message->body,
            'sender' => [
                'id'        => $auth->id,
                'name'      => $auth->name,
                'image_url' => $auth->image_url,
            ],
            'created_at' => $message->created_at->toIso8601String(),
            'delivered_at' => $message->delivered_at?->toIso8601String(),
            'seen_at' => null,
        ]);
    }

    public function markAsRead(Request $request, Conversation $conversation)
    {
        $conversation->participants()
            ->updateExistingPivot($request->user()->id, ['last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ✅ Read all conversations (fix read-all not worked)
    public function readAllConversations(Request $request)
    {
        $auth = $request->user();
        $now  = now();

        $convs = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->get();

        foreach ($convs as $conv) {
            $conv->participants()->updateExistingPivot($auth->id, ['last_read_at' => $now]);
        }

        return response()->json(['ok' => true]);
    }

    // ✅ Clear conversation (works for any user -> clears for both)
    public function clearConversation(Request $request, Conversation $conversation)
    {
        $auth = $request->user();

        $isParticipant = $conversation->participants()
            ->where('user_id', $auth->id)
            ->exists();

        if (!$isParticipant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Message::where('conversation_id', $conversation->id)->delete();

        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ✅ Presence ping
    public function ping(Request $request)
    {
        $request->user()->update([
            'last_seen_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // Optional offline
    public function offline(Request $request)
    {
        $request->user()->update([
            'last_seen_at' => now()->subYears(5),
        ]);

        return response()->json(['ok' => true]);
    }

    // ✅ Unread counts only (FAST)
    public function unreadCounts(Request $request)
    {
        $auth = $request->user();

        $convs = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->with(['participants' => fn ($q) => $q->where('user_id', $auth->id)])
            ->get();

        $items = [];
        $total = 0;

        foreach ($convs as $conv) {
            $pivot = $conv->participants->first()?->pivot;

            $unread = Message::where('conversation_id', $conv->id)
                ->where('sender_id', '!=', $auth->id)
                ->when($pivot?->last_read_at, fn ($q) =>
                    $q->where('created_at', '>', $pivot->last_read_at)
                )
                ->count();

            $items[] = [
                'conversation_id' => $conv->id,
                'unread_count'    => $unread,
            ];

            $total += $unread;
        }

        return response()->json([
            'total_unread' => $total,
            'items'        => $items,
        ]);
    }
}
