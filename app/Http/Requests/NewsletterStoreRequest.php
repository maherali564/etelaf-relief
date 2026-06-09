<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsletterStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255|unique:newsletters,email',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('hp_website')) {
            abort(422, 'Spam detected');
        }
    }
}
