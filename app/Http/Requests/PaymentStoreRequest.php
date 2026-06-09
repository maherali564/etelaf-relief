<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $donation = $this->route('donation');
        $token = session('payment_token_'.$donation?->id);
        $submittedToken = $this->input('token');

        if (! $token || ! $submittedToken || ! hash_equals($token, $submittedToken)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string|size:64',
        ];
    }
}
