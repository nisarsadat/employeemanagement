<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Message $message)
    {
        // load sender + attachments + replyTo.sender for broadcast payload
        $this->message->loadMissing([
            'sender:id,name,image',
            'attachments',
            'replyTo.sender:id,name,image',
        ]);
    }

    public function broadcastOn(): array
    {
        $conversationId = (int) $this->message->conversation_id;

        // other participant (direct chat)
        $otherUserId = DB::table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', '!=', (int) $this->message->sender_id)
            ->value('user_id');

        return array_filter([
            new PrivateChannel('conversation.' . $conversationId),
            $otherUserId ? new PrivateChannel('user.' . $otherUserId) : null,
        ]);
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        $m = $this->message;

        return [
            // ✅ keep consistent with your frontend parser: payload.message ?? payload
            'message' => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'body' => $m->body,
                'sender_id' => $m->sender_id,

                'sender' => [
                    'id' => $m->sender?->id,
                    'name' => $m->sender?->name,
                    'image_url' => $m->sender?->image_url,
                ],

                'reply_to_id' => $m->reply_to_id,
                'replyTo' => $m->replyTo ? [
                    'id' => $m->replyTo->id,
                    'body' => $m->replyTo->body,
                    'sender' => [
                        'id' => $m->replyTo->sender?->id,
                        'name' => $m->replyTo->sender?->name,
                        'image_url' => $m->replyTo->sender?->image_url,
                    ],
                ] : null,

                'attachments' => $m->attachments->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'name' => $a->name,
                    'mime' => $a->mime,
                    'size' => $a->size,
                    'url'  => $a->url,
                ])->values(),

                'created_at'   => $m->created_at?->toIso8601String(),
                'delivered_at' => optional($m->delivered_at)->toIso8601String(),
                'seen_at'      => optional($m->seen_at)->toIso8601String(),
                'deleted_at'   => optional($m->deleted_at)->toIso8601String(),
            ],
        ];
    }
}
