<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('update_donation');
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:2000',
        ];
    }
}
