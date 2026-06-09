<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class DonationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'donor_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'is_anonymous' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|string|in:monthly,quarterly,yearly',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'project_id' => 'nullable|exists:projects,id',
            'post_id' => 'nullable|exists:posts,id',
            'story_id' => 'nullable|exists:stories,id',
            'cryptocurrency_id' => 'nullable|exists:cryptocurrencies,id',
            'crypto_network_id' => 'nullable|exists:crypto_networks,id',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($this->filled('payment_method_id')) {
            $method = PaymentMethod::with('gateway')->find($this->payment_method_id);
            if ($method && $method->gateway?->driver === 'crypto') {
                $rules['cryptocurrency_id'] = 'required|exists:cryptocurrencies,id';
                $rules['crypto_network_id'] = 'required|exists:crypto_networks,id';
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('hp_website')) {
            abort(422, 'Spam detected');
        }
    }
}
