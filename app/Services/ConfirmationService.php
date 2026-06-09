<?php

namespace App\Services;

use App\Models\CryptoNetwork;
use App\Models\Cryptocurrency;
use App\Models\Donation;
use App\Models\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfirmationService
{
    /**
     * Load data for the payment confirmation page
     *
     * @param  Donation  $donation  The donation to confirm
     * @return array Page data including payment method, gateway, and crypto info
     */
    public function loadConfirmationPage(Donation $donation): array
    {
        $paymentMethod = $donation->paymentMethod;
        $gateway = $paymentMethod?->gateway;

        $cryptocurrencies = null;
        $selectedNetwork = null;
        if ($gateway && $gateway->driver === 'crypto') {
            $cryptocurrencies = Cryptocurrency::with('networks')->active()->get();
            $selectedNetwork = $donation->crypto_network_id
                ? CryptoNetwork::with('cryptocurrency')->find($donation->crypto_network_id)
                : null;
        }

        return [
            'paymentMethod' => $paymentMethod,
            'gateway' => $gateway,
            'config' => $gateway?->config ?? [],
            'instructions' => $paymentMethod?->instructions ?? '',
            'driver' => $gateway?->driver ?? '',
            'cryptocurrencies' => $cryptocurrencies,
            'selectedNetwork' => $selectedNetwork,
        ];
    }

    /**
     * Validate that the donation's gateway supports confirmation flow
     *
     * @param  Donation  $donation  The donation to validate
     * @return string|null The gateway driver name, or null if not supported
     */
    public function validateGateway(Donation $donation): ?string
    {
        $gateway = $donation->paymentMethod?->gateway;
        if (! $gateway || ! in_array($gateway->driver, ['bank_transfer', 'wise', 'crypto'])) {
            return null;
        }

        return $gateway->driver;
    }

    /**
     * Validate that the donation's gateway supports storing a confirmation
     *
     * @param  Donation  $donation  The donation to validate
     * @return string|null The gateway driver name, or null if not supported
     */
    public function validateStoreGateway(Donation $donation): ?string
    {
        $gateway = $donation->paymentMethod?->gateway;
        if (! $gateway || ! in_array($gateway->driver, ['bank_transfer', 'wise'])) {
            return null;
        }

        return $gateway->driver;
    }

    /**
     * Submit a payment confirmation (bank transfer proof)
     *
     * @param  Donation  $donation  The donation being confirmed
     * @param  array  $validated  Validated form data
     * @param  Request|null  $request  Optional request for file upload
     */
    public function submitConfirmation(Donation $donation, array $validated, ?Request $request = null): void
    {
        $data = [
            'donation_id' => $donation->id,
            'type' => 'bank_transfer',
            'reference_number' => $validated['reference_number'] ?? null,
            'amount' => $validated['amount'] ?? $donation->amount,
            'currency' => $validated['currency'] ?? $donation->currency,
            'sender_name' => $validated['sender_name'] ?? $donation->donor_name,
            'sender_account' => $validated['sender_account'] ?? null,
            'transfer_date' => $validated['transfer_date'] ?? now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ];

        if ($request && $request->hasFile('proof_document')) {
            $request->validate(['proof_document' => 'file|mimes:jpg,jpeg,png,pdf|max:10240']);
            $data['proof_document'] = $request->file('proof_document')->store('confirmations', 'public');
        }

        PaymentConfirmation::create($data);

        $donation->update([
            'status' => 'under_review',
            'confirmation_details' => [
                'reference_number' => $data['reference_number'],
                'type' => $data['type'],
                'submitted_at' => now()->toDateTimeString(),
            ],
        ]);

        Log::info('Bank transfer confirmation submitted', [
            'donation_id' => $donation->id,
            'reference' => $data['reference_number'],
        ]);
    }
}
