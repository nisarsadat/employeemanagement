<?php

namespace App\Http\Controllers\Api;

use App\Events\CallEnded;
use App\Events\CallIncoming;
use App\Events\CallSignal;
use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageSeen;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Support\Broadcasts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /* =========================================================
     | ONLINE STATUS
     ========================================================= */
    private int $onlineSeconds = 10;

    private function isOnline(User $u): bool
    {
        return $u->last_seen_at
            && $u->last_seen_at->diffInSeconds(now()) <= $this->onlineSeconds;
    }

    /* =========================================================
     | USERS LIST
     ========================================================= */
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

    /* =========================================================
     | CONVERSATIONS LIST
     ========================================================= */
    public function conversations(Request $request)
    {
        $auth = $request->user();

        $conversations = Conversation::whereHas('participants', fn ($q) =>
                $q->where('user_id', $auth->id)
            )
            ->with([
                'participants:id,name,image,last_seen_at',
                'lastMessage.sender:id,name,image',
            ])
            ->get();

        $unreadMap = $this->unreadCountsFor($auth->id, $conversations->pluck('id')->all());

        return $conversations->map(function ($conv) use ($auth, $unreadMap) {
                $other = $conv->participants->firstWhere('id', '!=', $auth->id);

                $unread = (int) ($unreadMap[$conv->id] ?? 0);

                return [
                    'id' => $conv->id,
                    'other_user' => $other ? [
                        'id' => $other->id,
                        'name' => $other->name,
                        'image_url' => $other->image_url,
                        'isOnline' => $this->isOnline($other),
                        'lastSeen' => $other->last_seen_at?->toIso8601String(),
                    ] : null,
                    'last_message' => $conv->lastMessage,
                    'unread_count' => $unread,
                ];
            });
    }

    /* =========================================================
     | FIND OR CREATE DIRECT CONVERSATION
     ========================================================= */
    private function unreadCountsFor(int $userId, array $conversationIds): array
    {
        if ($conversationIds === []) {
            return [];
        }

        return Message::query()
            ->join('conversation_participants as cp', function ($join) use ($userId) {
                $join->on('cp.conversation_id', '=', 'messages.conversation_id')
                    ->where('cp.user_id', '=', $userId);
            })
            ->whereIn('messages.conversation_id', $conversationIds)
            ->where('messages.sender_id', '!=', $userId)
            ->whereNull('messages.seen_at')
            ->where(function ($query) {
                $query->whereNull('cp.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'cp.last_read_at');
            })
            ->groupBy('messages.conversation_id')
            ->selectRaw('messages.conversation_id as conversation_id, COUNT(*) as unread_count')
            ->pluck('unread_count', 'conversation_id')
            ->all();
    }

    private function findDirectConversation(int $a, int $b): ?Conversation
    {
        return Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $a))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $b))
            ->first();
    }

    /* =========================================================
     | OPEN CONVERSATION
     ========================================================= */
    public function conversationWithUser(Request $request, User $user)
    {
        $auth = $request->user();

        $conversation = $this->findDirectConversation($auth->id, $user->id)
            ?? DB::transaction(function () use ($auth, $user) {
                $c = Conversation::create([
                    'type' => 'direct',
                    'created_by' => $auth->id,
                ]);
                $c->participants()->attach([$auth->id, $user->id]);
                return $c;
            });

        // mark as read (broadcasts MessageSeen)
        $this->markConversationAsRead($auth, $conversation);

        $messagesQuery = Message::where('conversation_id', $conversation->id)
            ->with(['sender:id,name,image', 'attachments', 'replyTo.sender:id,name,image']);

        $limit = (int) $request->query('limit', 0);
        $beforeId = (int) $request->query('before_id', 0);
        if ($limit > 0) {
            if ($beforeId > 0) {
                $messagesQuery->where('id', '<', $beforeId);
            }
            $messages = $messagesQuery->orderByDesc('id')->limit(min($limit, 100))->get()->sortBy('id')->values();
        } else {
            $messages = $messagesQuery->orderBy('id')->get();
        }

        return response()->json([
            'conversation' => ['id' => $conversation->id],
            'messages' => ['data' => $messages],
        ]);
    }

    /* =========================================================
     | SEND MESSAGE (API + REALTIME)
     ========================================================= */
    public function sendMessage(Request $request)
    {
        $auth = $request->user();
        $now  = now();

        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'nullable|string',
            'reply_to' => 'nullable|exists:messages,id',
            'images.*' => 'file|max:10240',
            'videos.*' => 'file|max:51200',
            'docs.*'   => 'file|max:51200',
            'voices.*' => 'file|max:20480',
        ]);

        $receiver = User::findOrFail($data['receiver_id']);

        $conversation = $this->findDirectConversation($auth->id, $receiver->id)
            ?? DB::transaction(function () use ($auth, $receiver) {
                $c = Conversation::create([
                    'type' => 'direct',
                    'created_by' => $auth->id,
                ]);
                $c->participants()->attach([$auth->id, $receiver->id]);
                return $c;
            });

        $message = $conversation->messages()->create([
            'sender_id'   => $auth->id,
            'body'        => $data['body'] ?? null,
            'reply_to_id' => $data['reply_to'] ?? null,
            'delivered_at' => $now,
        ]);

        $this->storeAttachments($request, $message);

        // 🔥 REALTIME
        Broadcasts::send(
            new MessageSent($message->load(['sender', 'attachments', 'replyTo.sender'])),
            toOthers: true
        );

        return response()->json($message);
    }

    private function storeAttachments(Request $request, Message $message): void
    {
        foreach (['images'=>'image','videos'=>'video','docs'=>'doc','voices'=>'voice'] as $key => $type) {
            if (!$request->hasFile($key)) continue;

            foreach ($request->file($key) as $file) {
                $path = $file->store("chat/{$message->conversation_id}", 'public');

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'type' => $type,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }
    }

    /* =========================================================
     | MARK AS READ (REALTIME)
     ========================================================= */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $this->markConversationAsRead($request->user(), $conversation);
        return response()->json(['ok' => true]);
    }

    private function markConversationAsRead(User $user, Conversation $conversation)
    {
        $now = now();
        $this->authorizeConversation($user, $conversation);

        $ids = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('seen_at')
            ->pluck('id')
            ->toArray();

        if (!empty($ids)) {
            Message::whereIn('id', $ids)->update(['seen_at' => $now]);

            // 🔥 REALTIME
            Broadcasts::send(new MessageSeen(
                sender_id: $user->id,
                conversation_id: $conversation->id,
                message_ids: $ids,
                seen_at: $now->toIso8601String()
            ), toOthers: true);
        }

        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => $now,
        ]);
    }

    /* =========================================================
     | READ ALL (REALTIME)
     ========================================================= */
    public function readAllConversations(Request $request)
    {
        $auth = $request->user();
        $now  = now();

        $conversations = Conversation::whereHas('participants', fn ($q) =>
            $q->where('user_id', $auth->id)
        )->get();

        $conversationIds = $conversations->pluck('id');
        $unread = $conversationIds->isEmpty()
            ? collect()
            : Message::query()
                ->whereIn('conversation_id', $conversationIds)
                ->where('sender_id', '!=', $auth->id)
                ->whereNull('seen_at')
                ->get(['id', 'conversation_id'])
                ->groupBy('conversation_id');

        $allIds = $unread->flatten()->pluck('id');
        if ($allIds->isNotEmpty()) {
            Message::whereIn('id', $allIds)->update(['seen_at' => $now]);
        }

        foreach ($conversations as $conversation) {
            $ids = ($unread->get($conversation->id) ?? collect())->pluck('id')->all();

            if ($ids) {
                Broadcasts::send(new MessageSeen(
                    sender_id: $auth->id,
                    conversation_id: $conversation->id,
                    message_ids: $ids,
                    seen_at: $now->toIso8601String()
                ), toOthers: true);
            }
        }

        if ($conversationIds->isNotEmpty()) {
            DB::table('conversation_participants')
                ->where('user_id', $auth->id)
                ->whereIn('conversation_id', $conversationIds)
                ->update([
                    'last_read_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        return response()->json(['ok' => true]);
    }

    /* =========================================================
     | CLEAR CONVERSATION (SOFT DELETE SAFE)
     ========================================================= */
    public function clearConversation(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($request->user(), $conversation);

        Message::where('conversation_id', $conversation->id)->update([
            'deleted_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /* =========================================================
     | DELETE MESSAGE (SOFT)
     ========================================================= */
    public function deleteMessage(Request $request, Message $message)
    {
        abort_if($message->sender_id !== $request->user()->id, 403);

        $conversation = Conversation::findOrFail($message->conversation_id);
        $this->authorizeConversation($request->user(), $conversation);

        $message->delete();

        Broadcasts::send(new MessageDeleted(
            conversation_id: $message->conversation_id,
            message_id: $message->id,
            deleted_at: $message->deleted_at?->toIso8601String()
        ), toOthers: true);

        return response()->json(['ok' => true]);
    }

    /* =========================================================
     | PRESENCE
     ========================================================= */
    public function ping(Request $request)
    {
        $request->user()->update(['last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    /* =========================================================
     | AUTH CHECK
     ========================================================= */
    private function authorizeConversation(User $user, Conversation $conversation): void
    {
        abort_unless(
            $conversation->participants()->where('user_id', $user->id)->exists(),
            403
        );
    }

    /* =========================================================
     | DELETE ALL CHATS (for current user)
     ========================================================= */
    public function deleteAllChats(Request $request)
    {
        $auth = $request->user();

        $conversationIds = Conversation::whereHas('participants', fn ($q) =>
            $q->where('user_id', $auth->id)
        )->pluck('id');

        if ($conversationIds->isEmpty()) {
            return response()->json(['ok' => true]);
        }

        Message::whereIn('conversation_id', $conversationIds)->delete();

        DB::table('conversation_participants')
            ->where('user_id', $auth->id)
            ->whereIn('conversation_id', $conversationIds)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /* =========================================================
     | SEARCH MESSAGES IN A CONVERSATION
     ========================================================= */
    public function searchMessages(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($request->user(), $conversation);

        $q = trim((string) $request->get('q', ''));
        if ($q === '') return response()->json([]);

        $items = Message::where('conversation_id', $conversation->id)
            ->whereNull('deleted_at')
            ->where(function ($qq) use ($q) {
                $qq->where('body', 'like', "%{$q}%");
            })
            ->with(['sender:id,name,image'])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json($items);
    }

    /* =========================================================
     | CALLS (VOICE/VIDEO)
     ========================================================= */
    public function startCall(Request $request)
    {
        $user = $request->user();

        $data = Validator::make($request->all(), [
            'to_user_id' => ['required', 'integer'],
            'conversation_id' => ['required', 'integer'],
            'call_id' => ['required', 'string'],
            'call_type' => ['required', 'in:voice,video'],
        ])->validate();

        Broadcasts::send(new CallIncoming(
            toUserId: (int)$data['to_user_id'],
            fromUserId: (int)$user->id,
            conversationId: (int)$data['conversation_id'],
            callId: (string)$data['call_id'],
            callType: (string)$data['call_type'],
        ), toOthers: true);

        return response()->json(['ok' => true]);
    }

    public function signalCall(Request $request)
    {
        $user = $request->user();

        $data = Validator::make($request->all(), [
            'to_user_id' => ['required', 'integer'],
            'conversation_id' => ['required', 'integer'],
            'call_id' => ['required', 'string'],
            'call_type' => ['required', 'in:voice,video'],
            'signal' => ['required', 'array'],
            'signal.type' => ['required', 'in:offer,answer,ice'],
        ])->validate();

        Broadcasts::send(new CallSignal(
            toUserId: (int)$data['to_user_id'],
            fromUserId: (int)$user->id,
            conversationId: (int)$data['conversation_id'],
            callId: (string)$data['call_id'],
            callType: (string)$data['call_type'],
            signal: (array)$data['signal'],
        ), toOthers: true);

        return response()->json(['ok' => true]);
    }

    public function endCall(Request $request)
    {
        $user = $request->user();

        $data = Validator::make($request->all(), [
            'to_user_id' => ['required', 'integer'],
            'conversation_id' => ['required', 'integer'],
            'call_id' => ['required', 'string'],
            'call_type' => ['required', 'in:voice,video'],
            'status' => ['required', 'in:ended,rejected,missed'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        Broadcasts::send(new CallEnded(
            toUserId: (int)$data['to_user_id'],
            fromUserId: (int)$user->id,
            conversationId: (int)$data['conversation_id'],
            callId: (string)$data['call_id'],
            callType: (string)$data['call_type'],
            status: (string)$data['status'],
            durationSeconds: (int)($data['duration_seconds'] ?? 0),
        ), toOthers: true);

        return response()->json(['ok' => true]);
    }
}