<?php

namespace App\Events;

use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ChatStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public ChatSession $session,
        public string $status,
        public ?int $assigned_to
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-chats'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'visitor_name' => $this->session->visitor_name,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
        ];
    }
}
