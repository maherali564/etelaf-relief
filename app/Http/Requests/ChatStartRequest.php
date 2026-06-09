<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'required|email|max:255',
            'visitor_url' => 'nullable|string|max:500',
        ];
    }
}
