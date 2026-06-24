<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->can_chat || $user->hasRole('super_admin'));
    }

    public function rules(): array
    {
        $method = $this->route()->getActionMethod();

        return match ($method) {
            'send' => [
                'session_id' => 'required|exists:chat_sessions,id',
                'message' => 'required|string|max:2000',
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
