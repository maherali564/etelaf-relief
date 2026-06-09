<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $session_id,
        public bool $is_admin_typing
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
            'session_id' => $this->session_id,
            'is_admin_typing' => $this->is_admin_typing,
        ];
    }
}
