<?php

namespace App\Livewire;

use App\Events\ChatStatusChanged;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminChatPanel extends Component
{
    public $sessions = [];

    public $activeSessionId = null;

    public $messages = [];

    public $message = '';

    public $unreadCount = 0;

    public $search = '';

    public $tab = 'active';

    public $isAdminTyping = false;

    protected $rules = [
        'message' => 'nullable|string|max:2000',
        'search' => 'nullable|string|max:100',
        'tab' => 'in:active,waiting,closed',
    ];

    protected function getListeners()
    {
        return [
            'echo:admin-chats,ChatStatusChanged' => 'refreshSessions',
        ];
    }

    public function mount()
    {
        $this->refreshSessions();
    }

    public function refreshSessions()
    {
        $this->sessions = ChatSession::with('assignedAgent')
            ->whereIn('status', ['waiting', 'active'])
            ->orWhere(function ($q) {
                $q->where('status', 'closed')
                    ->whereDate('updated_at', today());
            })
            ->orderByRaw("CASE status WHEN 'waiting' THEN 0 WHEN 'active' THEN 1 WHEN 'closed' THEN 2 ELSE 3 END")
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

        $this->unreadCount = ChatMessage::whereHas('session', function ($q) {
            $q->where('assigned_to', Auth::id())->where('status', 'active');
        })
            ->where('is_read', false)
            ->where('is_from_admin', false)
            ->count();
    }

    public function selectSession($sessionId)
    {
        $this->activeSessionId = $sessionId;
        $this->loadMessages();

        $session = ChatSession::find($sessionId);
        if ($session && $session->status === 'waiting') {
            $session->assignTo(Auth::user());
            event(new ChatStatusChanged($session, 'active', Auth::id()));

            $msg = new ChatMessage();
            $msg->fill([
                'message' => 'المشرف '.Auth::user()->name.' انضم إلى المحادثة',
                'is_from_admin' => true,
            ]);
            $msg->chat_session_id = $session->id;
            $msg->user_id = Auth::id();
            $msg->save();

            $this->refreshSessions();
        }
    }

    public function loadMessages()
    {
        if (! $this->activeSessionId) {
            return;
        }

        $this->messages = ChatMessage::with('user')
            ->where('chat_session_id', $this->activeSessionId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'user_name' => $m->user?->name,
                'created_at' => $m->created_at->toISOString(),
            ])
            ->toArray();

        ChatMessage::where('chat_session_id', $this->activeSessionId)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->dispatch('messages-loaded');
    }

    public function sendMessage()
    {
        $this->validateOnly('message');

        if (! $this->message || ! $this->activeSessionId) {
            return;
        }

        $msg = new ChatMessage();
        $msg->fill([
            'message' => $this->message,
            'is_from_admin' => true,
        ]);
        $msg->chat_session_id = $this->activeSessionId;
        $msg->user_id = Auth::id();
        $msg->save();

        $this->messages[] = [
            'id' => time(),
            'message' => $this->message,
            'is_from_admin' => true,
            'user_name' => Auth::user()->name,
            'created_at' => now()->toISOString(),
        ];
        $this->message = '';
    }

    public function typing()
    {
        // Fired on keydown — used to broadcast typing indicator via Echo
    }

    public function closeSession()
    {
        if (! $this->activeSessionId) {
            return;
        }

        $session = ChatSession::find($this->activeSessionId);
        if ($session) {
            $session->close();
            event(new ChatStatusChanged($session, 'closed', Auth::id()));
        }

        $this->activeSessionId = null;
        $this->messages = [];
        $this->refreshSessions();
    }

    public function getFilteredSessionsProperty()
    {
        return collect($this->sessions)->filter(function ($s) {
            if ($this->search) {
                return str_contains($s['visitor_name'] ?? '', $this->search) ||
                       str_contains($s['visitor_email'] ?? '', $this->search);
            }
            if ($this->tab === 'active') {
                return $s['status'] === 'active';
            }
            if ($this->tab === 'waiting') {
                return $s['status'] === 'waiting';
            }
            if ($this->tab === 'closed') {
                return $s['status'] === 'closed';
            }

            return true;
        })->values()->toArray();
    }

    public function getWaitingCountProperty()
    {
        return collect($this->sessions)->where('status', 'waiting')->count();
    }

    public function render()
    {
        return view('livewire.admin-chat-panel')
            ->layout('layouts.admin-chat');
    }
}
