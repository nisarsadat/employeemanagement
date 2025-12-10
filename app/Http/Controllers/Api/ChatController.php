<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * List all other users you can chat with.
     */
    public function users(Request $request)
    {
        $authId = $request->user()->id;

        $users = User::query()
            ->where('id', '!=', $authId)
            ->select('id', 'name', 'username') // username is optional for your UI search
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * List conversations for the authenticated user, including:
     * - other_user
     * - last_message
     * - unread_count (messages from others after last_read_at)
     */
    public function conversations(Request $request)
    {
        $auth = $request->user();

        $conversations = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->with([
                'participants:id,name',
                'lastMessage.sender:id,name',
            ])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->take(1)
            )
            ->get()
            ->map(function (Conversation $conversation) use ($auth) {
                // auth participant on pivot to get last_read_at
                $authParticipant = $conversation->participants
                    ->firstWhere('id', $auth->id);

                $lastReadAt = optional($authParticipant?->pivot)->last_read_at;

                // other user (for direct chat)
                $other = $conversation->participants
                    ->firstWhere('id', '!=', $auth->id);

                // count unread messages (from other users, after last_read_at)
                $unreadCount = Message::query()
                    ->where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $auth->id)
                    ->when($lastReadAt, function ($q) use ($lastReadAt) {
                        $q->where('created_at', '>', $lastReadAt);
                    })
                    ->count();

                return [
                    'id'           => $conversation->id,
                    'type'         => $conversation->type,
                    'other_user'   => $other ? [
                        'id'   => $other->id,
                        'name' => $other->name,
                    ] : null,
                    'last_message' => $conversation->lastMessage ? [
                        'id'         => $conversation->lastMessage->id,
                        'body'       => $conversation->lastMessage->body,
                        'sender'     => [
                            'id'   => $conversation->lastMessage->sender->id,
                            'name' => $conversation->lastMessage->sender->name,
                        ],
                        'created_at' => $conversation->lastMessage->created_at->toIso8601String(),
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            });

        return response()->json($conversations);
    }

    /**
     * Get (or create) direct conversation with a user + messages.
     */
    public function conversationWithUser(Request $request, User $user)
    {
        $auth = $request->user();

        if ($auth->id === $user->id) {
            return response()->json([
                'message' => 'You cannot chat with yourself.',
            ], 422);
        }

        $perPage = (int) $request->query('perPage', 30);

        $conversation = Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (! $conversation) {
            $conversation = DB::transaction(function () use ($auth, $user) {
                $conv = Conversation::create([
                    'type'       => 'direct',
                    'created_by' => $auth->id,
                ]);

                $conv->participants()->attach([
                    $auth->id => ['created_at' => now(), 'updated_at' => now()],
                    $user->id => ['created_at' => now(), 'updated_at' => now()],
                ]);

                return $conv;
            });
        }

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with('sender:id,name')
            ->orderByDesc('id')
            ->paginate($perPage);

        // mark as read for auth user
        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => now()]);

        return response()->json([
            'conversation' => [
                'id'   => $conversation->id,
                'type' => $conversation->type,
                'user' => [
                    'id'   => $user->id,
                    'name' => $user->name,
                ],
            ],
            'messages' => [
                'data' => $messages->getCollection()->map(function (Message $m) {
                    return [
                        'id'         => $m->id,
                        'body'       => $m->body,
                        'sender'     => [
                            'id'   => $m->sender->id,
                            'name' => $m->sender->name,
                        ],
                        'created_at' => $m->created_at->toIso8601String(),
                    ];
                }),
                'meta' => [
                    'current_page' => $messages->currentPage(),
                    'last_page'    => $messages->lastPage(),
                    'per_page'     => $messages->perPage(),
                    'total'        => $messages->total(),
                ],
            ],
        ]);
    }

    /**
     * Send a message (auth user → receiver).
     * Uses try/catch around broadcast so it NEVER crashes
     * even if Reverb / Pusher is not running.
     */
    public function sendMessage(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'body'        => ['required', 'string', 'max:5000'],
        ]);

        if ((int) $validated['receiver_id'] === $auth->id) {
            return response()->json([
                'message' => 'You cannot send messages to yourself.',
            ], 422);
        }

        $receiver = User::findOrFail($validated['receiver_id']);

        // find or create conversation
        $conversation = Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $auth->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $receiver->id))
            ->first();

        if (! $conversation) {
            $conversation = DB::transaction(function () use ($auth, $receiver) {
                $conv = Conversation::create([
                    'type'       => 'direct',
                    'created_by' => $auth->id,
                ]);

                $conv->participants()->attach([
                    $auth->id     => ['created_at' => now(), 'updated_at' => now()],
                    $receiver->id => ['created_at' => now(), 'updated_at' => now()],
                ]);

                return $conv;
            });
        }

        // create message
        $message = $conversation->messages()->create([
            'sender_id' => $auth->id,
            'body'      => $validated['body'],
        ]);

        // sender has read everything in this conversation
        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => now()]);

        // broadcast but DON'T break if Reverb/Pusher is down
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Chat broadcast failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $message->load('sender:id,name');

        return response()->json([
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'body'            => $message->body,
            'sender'          => [
                'id'   => $message->sender->id,
                'name' => $message->sender->name,
            ],
            'created_at'      => $message->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * Mark conversation as read for authenticated user.
     * (Used for badge / unread logic.)
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $auth = $request->user();

        if (! $conversation->participants()->where('user_id', $auth->id)->exists()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $conversation->participants()
            ->updateExistingPivot($auth->id, ['last_read_at' => now()]);

        return response()->json([
            'success'       => true,
            'conversation'  => $conversation->id,
            'last_read_at'  => now()->toIso8601String(),
        ]);
    }
}
