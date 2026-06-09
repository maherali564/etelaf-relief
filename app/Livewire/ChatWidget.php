<?php

namespace App\Livewire;

use App\Events\ChatStatusChanged;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Livewire\Component;

class ChatWidget extends Component
{
    public $showChat = false;

    public $step = 'prechat';

    public $visitorName = '';

    public $visitorEmail = '';

    public $sessionId = null;

    public $token = null;

    public $message = '';

    public $messages = [];

    public $isAdminTyping = false;

    public $activeSessions = 0;

    public $waitingSessions = 0;

    protected function getListeners()
    {
        return [
            'echo:admin-chats,ChatStatusChanged' => 'refreshStats',
        ];
    }

    public function mount()
    {
        if ($stored = session('chat_session')) {
            $session = ChatSession::find($stored['session_id']);
            if ($session && $session->status !== 'closed') {
                $this->sessionId = $stored['session_id'];
                $this->token = $stored['token'];
                $this->visitorName = $stored['visitor_name'];
                $this->visitorEmail = $stored['visitor_email'];
                $this->step = 'chat';
                $this->loadMessages();
            }
        }
    }

    public function startChat()
    {
        $this->validate([
            'visitorName' => 'required|string|max:255',
            'visitorEmail' => 'required|email|max:255',
        ]);

        $session = ChatSession::create([
            'visitor_name' => $this->visitorName,
            'visitor_email' => $this->visitorEmail,
            'visitor_ip' => request()->ip(),
            'visitor_url' => url()->current(),
            'status' => 'waiting',
        ]);

        $this->token = md5($session->id.$this->visitorEmail.time());
        $this->sessionId = $session->id;

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'visitor_token' => $this->token,
            'message' => 'مرحباً، أريد المساعدة',
            'is_from_admin' => false,
        ]);

        event(new ChatStatusChanged($session, 'waiting', null));

        $this->step = 'chat';

        session(['chat_session' => [
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'visitor_name' => $this->visitorName,
            'visitor_email' => $this->visitorEmail,
        ]]);

        $this->dispatch('chat-started', sessionId: $this->sessionId);
        $this->loadMessages();
    }

    public function sendMessage()
    {
        if (! $this->message || ! $this->sessionId) {
            return;
        }

        $msg = ChatMessage::create([
            'chat_session_id' => $this->sessionId,
            'visitor_token' => $this->token,
            'message' => $this->message,
            'is_from_admin' => false,
        ]);

        $this->messages[] = [
            'id' => $msg->id,
            'message' => $this->message,
            'is_from_admin' => false,
            'created_at' => $msg->created_at->toISOString(),
        ];
        $this->message = '';

        $this->dispatch('message-sent');
    }

    public function loadMessages()
    {
        if (! $this->sessionId) {
            return;
        }

        $this->messages = ChatMessage::where('chat_session_id', $this->sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'created_at' => $m->created_at->toISOString(),
            ])
            ->toArray();
    }

    public function closeChat()
    {
        if ($this->sessionId) {
            $session = ChatSession::find($this->sessionId);
            if ($session) {
                $session->close();
                event(new ChatStatusChanged($session, 'closed', null));
            }
        }

        $this->reset(['showChat', 'step', 'visitorName', 'visitorEmail', 'sessionId', 'token', 'messages', 'message']);
        session()->forget('chat_session');
    }

    public function refreshStats()
    {
        $this->activeSessions = ChatSession::active()->count();
        $this->waitingSessions = ChatSession::waiting()->count();
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
