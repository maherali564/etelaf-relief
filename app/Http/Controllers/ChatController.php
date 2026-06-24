<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatActionRequest;
use App\Http\Requests\ChatStartRequest;
use App\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    public function start(ChatStartRequest $request)
    {
        $result = $this->chatService->startSession(
            $request->visitor_name,
            $request->visitor_email,
            $request->ip(),
            $request->visitor_url
        );

        return response()->json($result);
    }

    public function send(ChatActionRequest $request)
    {
        $message = $this->chatService->sendMessage(
            $request->session_id,
            $request->token,
            $request->message
        );

        return response()->json(['id' => $message->id]);
    }

    public function typing(ChatActionRequest $request)
    {
        $this->chatService->broadcastTyping($request->session_id, false);

        return response()->noContent();
    }

    public function close(ChatActionRequest $request)
    {
        $this->chatService->closeSession($request->session_id);

        return response()->json(['status' => 'closed']);
    }

    public function messages($sessionId)
    {
        return response()->json(
            $this->chatService->getVisitorMessages($sessionId)
        );
    }
}
