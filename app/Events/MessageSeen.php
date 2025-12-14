<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $sender_id,
        public int $conversation_id,
        public array $message_ids,
        public string $seen_at,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation_id),
            new PrivateChannel('user.' . $this->sender_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSeen';
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->sender_id,
            'conversation_id' => $this->conversation_id,
            'message_ids' => array_values($this->message_ids),
            'seen_at' => $this->seen_at,
        ];
    }
}
