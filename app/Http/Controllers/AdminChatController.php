<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminChatRequest;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function assign(AdminChatRequest $request)
    {
        $this->chatService->assignSession($request->session_id, Auth::user());

        return response()->json(['status' => 'assigned']);
    }

    public function send(AdminChatRequest $request)
    {
        $message = $this->chatService->sendAdminMessage(
            $request->session_id,
            Auth::user(),
            $request->message
        );

        return response()->json(['id' => $message->id]);
    }

    public function close(AdminChatRequest $request)
    {
        $this->chatService->closeAdminSession($request->session_id, Auth::user());

        return response()->json(['status' => 'closed']);
    }

    public function typing(AdminChatRequest $request)
    {
        $this->chatService->broadcastTyping(
            $request->session_id,
            $request->boolean('is_typing'),
            true
        );

        return response()->noContent();
    }

    public function sessions()
    {
        return response()->json(
            $this->chatService->getSessions(Auth::user())
        );
    }

    public function sessionMessages($sessionId)
    {
        $this->chatService->markMessagesRead($sessionId);

        return response()->json(
            $this->chatService->getSessionMessages($sessionId)
        );
    }

    public function unreadCount()
    {
        if (! Auth::check()) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => $this->chatService->getUnreadCount(Auth::user()),
        ]);
    }
}
