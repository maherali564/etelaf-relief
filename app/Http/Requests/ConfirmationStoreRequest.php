<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'sender_name' => 'nullable|string|max:255',
            'sender_account' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'proof_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
}
