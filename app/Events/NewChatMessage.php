<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NewChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public ChatMessage $message,
        public int $session_id,
        public bool $is_from_admin
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.'.$this->session_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'is_from_admin' => $this->is_from_admin,
            'visitor_name' => $this->message->visitor_name,
            'created_at' => $this->message->created_at->toISOString(),
            'session_id' => $this->session_id,
        ];
    }
}
