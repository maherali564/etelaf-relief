<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WiseService
{
    protected array $config;

    protected string $baseUrl;

    /**
     * @param  array  $config  Must contain 'api_token', optionally 'webhook_secret', 'profile_id', and bank details
     */
    public function __construct(array $config)
    {
        if (empty($config['api_token'])) {
            throw new RuntimeException('Wise API token is not configured');
        }
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api.wise.com'
            : 'https://api.sandbox.transferwise.tech';
    }

    /**
     * Get the configured Wise profile ID
     */
    public function getProfileId(): string
    {
        return $this->config['profile_id'] ?? '';
    }

    /**
     * Get bank account details for payment instructions
     */
    public function getPaymentInfo(): array
    {
        return [
            'bank_name' => $this->config['bank_name'] ?? 'Wise',
            'account_name' => $this->config['account_name'] ?? '',
            'account_number' => $this->config['account_number'] ?? '',
            'iban' => $this->config['iban'] ?? '',
            'swift_code' => $this->config['swift_code'] ?? '',
            'routing_number' => $this->config['routing_number'] ?? '',
            'email' => $this->config['email'] ?? '',
        ];
    }

    /**
     * Generate payment instructions for a donation
     */
    public function process(Donation $donation): array
    {
        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'wise',
        ]);

        return [
            'type' => 'bank_transfer',
            'driver' => 'wise',
            'instructions' => $this->getPaymentInfo(),
            'message' => 'يرجى تحويل المبلغ عبر Wise أو التحويل البنكي أدناه',
        ];
    }

    /**
     * Verify Wise webhook signature using HMAC-SHA256
     *
     * @param  string  $payload  The raw request body
     * @param  string  $signature  The X-Wise-Signature header value
     * @return array|null Decoded event data on success, null on failure
     */
    public function verifyWebhook(string $payload, string $signature): ?array
    {
        $webhookSecret = $this->config['webhook_secret'] ?? '';

        if (empty($webhookSecret)) {
            Log::critical('Wise webhook secret is not configured');

            return null;
        }

        if (empty($signature)) {
            Log::warning('Wise webhook: empty signature header');

            return null;
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($computedSignature, $signature)) {
            Log::warning('Wise webhook: signature mismatch');

            return null;
        }

        return json_decode($payload, true);
    }
}
