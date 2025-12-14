<?php
// app/Events/MessageDeleted.php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public int $conversation_id,
        public int $message_id,
        public ?string $deleted_at = null,
    ) {
        $this->deleted_at = $deleted_at ?? now()->toIso8601String();
    }

    public function broadcastOn()
    {
        return new PrivateChannel("conversation.{$this->conversation_id}");
    }

    public function broadcastAs()
    {
        return 'MessageDeleted';
    }
}
