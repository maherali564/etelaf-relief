<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $method = $this->route()?->getActionMethod() ?? 'send';

        return match ($method) {
            'send' => [
                'session_id' => 'required|exists:chat_sessions,id',
                'token' => 'required|string|size:64',
                'message' => 'required|string|max:2000',
            ],
            'close' => [
                'session_id' => 'required|exists:chat_sessions,id',
                'token' => 'required|string|size:64',
            ],
            'typing' => [
                'session_id' => 'required|exists:chat_sessions,id',
                'is_typing' => 'required|boolean',
            ],
            default => [
                'session_id' => 'required|exists:chat_sessions,id',
            ],
        };
    }
}
