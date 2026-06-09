<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalService
{
    protected array $config;

    protected string $baseUrl;

    public function __construct(array $config)
    {
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new RuntimeException('PayPal client ID and secret must be configured');
        }
        $this->config = $config;
        $this->baseUrl = ($config['mode'] ?? 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken(): string
    {
        $response = Http::timeout(10)->connectTimeout(5)->withBasicAuth(
            $this->config['client_id'] ?? '',
            $this->config['client_secret'] ?? ''
        )->asForm()->post("{$this->baseUrl}/v1/oauth2/token", [
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            Log::error('PayPal getAccessToken failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Failed to get PayPal access token');
        }

        return $response->json('access_token');
    }

    /**
     * Create a PayPal order for a donation
     *
     * @param  Donation  $donation  The donation to create an order for
     * @return string The PayPal approval URL to redirect the user to
     *
     * @throws RuntimeException If order creation fails
     */
    public function createOrder(Donation $donation): string
    {
        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'paypal',
        ]);

        $token = $this->getAccessToken();
        $idempotencyKey = $donation->idempotency_key ?? IdempotencyHelper::generateKey('paypal');

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)
            ->withHeader('PayPal-Request-Id', $idempotencyKey)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $donation->id,
                    'description' => 'تبرع - '.$donation->donor_name,
                    'amount' => [
                        'currency_code' => $donation->currency,
                        'value' => number_format($donation->amount, 2, '.', ''),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'return_url' => route('payment.success', ['locale' => $donation->locale, 'donation' => $donation->id]),
                            'cancel_url' => route('payment.cancel', ['locale' => $donation->locale, 'donation' => $donation->id]),
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('PayPal createOrder failed', [
                'donation_id' => $donation->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('فشل إنشاء طلب PayPal');
        }

        $data = $response->json();

        if (empty($data['id'])) {
            Log::error('PayPal createOrder: no order ID returned', ['donation_id' => $donation->id, 'response' => $data]);
            throw new RuntimeException('PayPal لم يُرجع رقم طلب');
        }

        $donation->update(['transaction_id' => $data['id']]);

        foreach ($data['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'payer-action') {
                return $link['href'];
            }
        }

        Log::error('PayPal createOrder: no payer-action link found', ['donation_id' => $donation->id, 'links' => $data['links'] ?? []]);
        throw new RuntimeException('لم يتم العثور على رابط الدفع PayPal');
    }

    /**
     * Capture an approved PayPal order
     *
     * @param  string  $orderId  The PayPal order ID
     * @return array The capture response data
     *
     * @throws RuntimeException If capture fails
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)->post(
            "{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture"
        );

        if (! $response->successful()) {
            Log::error('PayPal captureOrder failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('فشل تأكيد الدفع PayPal');
        }

        return $response->json();
    }

    /**
     * Verify PayPal webhook signature using PayPal's verification API
     *
     * @param  string  $payload  The raw request body
     * @param  array  $headers  Request headers (must include PAYPAL-AUTH-ALGO, etc.)
     * @return bool True if the webhook is verified
     */
    public function verifyWebhook(string $payload, array $headers): bool
    {
        $webhookId = $this->config['webhook_id'] ?? '';

        if (empty($webhookId)) {
            Log::critical('PayPal webhook ID is not configured');

            return false;
        }

        try {
            $token = $this->getAccessToken();
        } catch (\Exception $e) {
            Log::error('PayPal verifyWebhook: failed to get access token', ['error' => $e->getMessage()]);

            return false;
        }

        $verificationSignature = ($headers['PAYPAL-AUTH-ALGO'] ?? '')
            .'|'.($headers['PAYPAL-CERT-URL'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-ID'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-SIG'] ?? '')
            .'|'.($headers['PAYPAL-TRANSMISSION-TIME'] ?? '');

        $response = Http::timeout(10)->connectTimeout(5)->withToken($token)->post(
            "{$this->baseUrl}/v1/notifications/verify-webhook-signature",
            [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($payload, true),
            ]
        );

        $result = $response->successful() ? $response->json() : [];
        $verified = ($result['verification_status'] ?? '') === 'SUCCESS';

        if (! $verified) {
            Log::warning('PayPal webhook signature verification failed');
        }

        return $verified;
    }
}
