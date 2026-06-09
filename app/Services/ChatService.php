<?php

namespace App\Services;

use App\Events\ChatStatusChanged;
use App\Events\NewChatMessage;
use App\Events\UserTyping;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Start a new chat session for a visitor
     *
     * @param  string  $visitorName  Visitor's name
     * @param  string  $visitorEmail  Visitor's email
     * @param  string  $visitorIp  Visitor's IP address
     * @param  string|null  $visitorUrl  The page URL where chat was initiated
     * @return array Contains 'session_id' and 'token'
     */
    public function startSession(string $visitorName, string $visitorEmail, string $visitorIp, ?string $visitorUrl = null): array
    {
        $session = ChatSession::create([
            'visitor_name' => $visitorName,
            'visitor_email' => $visitorEmail,
            'visitor_ip' => $visitorIp,
            'visitor_url' => $visitorUrl ?? url()->previous(),
            'status' => 'waiting',
        ]);

        $token = Str::random(64);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'visitor_token' => $token,
            'message' => 'مرحباً، أريد المساعدة',
            'is_from_admin' => false,
        ]);

        event(new ChatStatusChanged($session, 'waiting', null));

        Cache::flush();

        return ['session_id' => $session->id, 'token' => $token];
    }

    /**
     * Send a visitor message in a chat session
     *
     * @param  int  $sessionId  The chat session ID
     * @param  string  $token  Visitor authentication token
     * @param  string  $message  The message content
     * @return ChatMessage The created message
     */
    public function sendMessage(int $sessionId, string $token, string $message): ChatMessage
    {
        $session = ChatSession::findOrFail($sessionId);

        $msg = ChatMessage::create([
            'chat_session_id' => $session->id,
            'visitor_token' => $token,
            'message' => $message,
            'is_from_admin' => false,
        ]);

        broadcast(new NewChatMessage($msg, $session->id, false))->toOthers();

        if ($session->assigned_to) {
            Cache::forget('chat.sessions.'.$session->assigned_to);
            Cache::forget('chat.unread.'.$session->assigned_to);
        }

        return $msg;
    }

    public function closeSession(int $sessionId, ?User $user = null): void
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->close();

        event(new ChatStatusChanged($session, 'closed', $user?->id));
    }

    public function broadcastTyping(int $sessionId, bool $isTyping, bool $isAdmin = false): void
    {
        broadcast(new UserTyping($sessionId, $isAdmin))->toOthers();
    }

    public function assignSession(int $sessionId, User $user): ChatSession
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->assignTo($user);

        event(new ChatStatusChanged($session, 'active', $user->id));

        $session->messages()->create([
            'user_id' => $user->id,
            'message' => 'المشرف '.$user->name.' انضم إلى المحادثة',
            'is_from_admin' => true,
        ]);

        return $session;
    }

    public function sendAdminMessage(int $sessionId, User $user, string $message): ChatMessage
    {
        $session = ChatSession::findOrFail($sessionId);

        $msg = $session->messages()->create([
            'user_id' => $user->id,
            'message' => $message,
            'is_from_admin' => true,
        ]);

        broadcast(new NewChatMessage($msg, $session->id, true))->toOthers();

        Cache::forget('chat.sessions.'.$user->id);
        Cache::forget('chat.unread.'.$user->id);

        return $msg;
    }

    public function closeAdminSession(int $sessionId, User $user): void
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->close();

        $session->messages()->create([
            'user_id' => $user->id,
            'message' => 'تم إنهاء المحادثة',
            'is_from_admin' => true,
        ]);

        event(new ChatStatusChanged($session, 'closed', $user->id));
    }

    /**
     * Get all chat sessions with unread count, with caching
     */
    public function getSessions(User $user): array
    {
        $cacheKey = 'chat.sessions.'.$user->id;

        return Cache::remember($cacheKey, 30, function () use ($user) {
            $sessions = ChatSession::with('assignedAgent')
                ->whereIn('status', ['waiting', 'active'])
                ->orWhere(function ($q) {
                    $q->where('status', 'closed')
                        ->whereDate('updated_at', today());
                })
                ->orderByRaw("FIELD(status, 'waiting', 'active', 'closed')")
                ->orderBy('updated_at', 'desc')
                ->get();

            $unreadCount = ChatMessage::whereHas('session', function ($q) use ($user) {
                $q->where('assigned_to', $user->id)->where('status', 'active');
            })
                ->where('is_read', false)
                ->where('is_from_admin', false)
                ->count();

            return ['sessions' => $sessions, 'unread_count' => $unreadCount];
        });
    }

    public function markMessagesRead(int $sessionId): void
    {
        $session = ChatSession::with('messages.user')->findOrFail($sessionId);

        $session->messages()
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getSessionMessages(int $sessionId)
    {
        $session = ChatSession::with('messages.user')->findOrFail($sessionId);

        return $session->messages()->orderBy('created_at')->limit(500)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'user_name' => $m->user?->name,
                'created_at' => $m->created_at->toISOString(),
            ];
        });
    }

    public function getVisitorMessages(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);

        return $session->messages()->orderBy('created_at')->limit(500)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'created_at' => $m->created_at->toISOString(),
            ];
        });
    }

    /**
     * Get unread message count for a user, with caching
     */
    public function getUnreadCount(User $user): int
    {
        return Cache::remember('chat.unread.'.$user->id, 30, function () use ($user) {
            return ChatMessage::whereHas('session', function ($q) use ($user) {
                $q->where('assigned_to', $user->id)->where('status', 'active');
            })
                ->where('is_read', false)
                ->where('is_from_admin', false)
                ->count();
        });
    }
}
