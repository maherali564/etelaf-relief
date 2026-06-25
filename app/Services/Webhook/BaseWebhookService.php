<?php

namespace App\Services\Webhook;

use App\Models\Donation;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

abstract class BaseWebhookService
{
    protected PaymentGateway $gateway;
    protected string $provider;

    public function __construct(PaymentGateway $gateway, string $provider)
    {
        $this->gateway = $gateway;
        $this->provider = $provider;
    }

    protected function logWebhook(string $type, ?string $payload = null): void
    {
        Log::info("{$this->provider} webhook received", ['type' => $type]);
    }

    protected function findDonationByTransactionId(string $transactionId): ?Donation
    {
        $donation = Donation::where('transaction_id', $transactionId)->first();
        if (! $donation) {
            Log::warning("{$this->provider} webhook: donation not found", ['transaction_id' => $transactionId]);
        }
        return $donation;
    }

    protected function isDonationPending(Donation $donation): bool
    {
        if ($donation->status !== 'pending') {
            Log::info("{$this->provider} webhook: donation already processed", [
                'donation_id' => $donation->id, 'status' => $donation->status,
            ]);
            return false;
        }
        return true;
    }

    protected function verifyAmount(float $webhookAmount, float $storedAmount): bool
    {
        if ($webhookAmount > 0 && abs($webhookAmount - $storedAmount) > 0.01) {
            Log::warning("{$this->provider} webhook: amount mismatch", [
                'webhook_amount' => $webhookAmount, 'stored_amount' => $storedAmount,
            ]);
            return false;
        }
        return true;
    }

    protected function completeDonation(Donation $donation, array $extraData = []): void
    {
        $donation->update(array_merge(['status' => 'completed'], $extraData));
        Log::info("Donation completed via {$this->provider}", ['donation_id' => $donation->id]);
    }

    // ponytail: header() is case-insensitive in Laravel, but webhook handlers receive
    // raw array not Request, so this handles the mismatch
    protected function extractHeader(array $headers, string $key): string
    {
        $value = $headers[$key] ?? $headers[strtolower($key)] ?? $headers[strtoupper($key)] ?? '';
        return is_array($value) ? ($value[0] ?? '') : $value;
    }

    abstract public function handle(string $payload, array $headers): array;
}
