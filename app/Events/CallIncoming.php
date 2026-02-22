<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class CallIncoming implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $toUserId,
        public int $fromUserId,
        public int $conversationId,
        public string $callId,
        public string $callType, // voice|video
    ) {}

    public function broadcastOn(): array
    {
        return [ new PrivateChannel('user.' . $this->toUserId) ];
    }

    public function broadcastAs(): string
    {
        return 'CallIncoming';
    }

    public function broadcastWith(): array
    {
        return [
            'call' => [
                'call_id' => $this->callId,
                'conversation_id' => $this->conversationId,
                'call_type' => $this->callType,
                'from_user_id' => $this->fromUserId,
                'to_user_id' => $this->toUserId,
            ],
        ];
    }
}