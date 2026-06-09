<?php

namespace App\Services\Payment;

use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    protected array $config;

    /**
     * @param  array  $config  Must contain 'secret_key', 'publishable_key', and optionally 'webhook_secret'
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        if (empty($config['secret_key'])) {
            throw new RuntimeException('Stripe secret key is not configured');
        }
        Stripe::setApiKey($config['secret_key']);
    }

    /**
     * Create a Stripe Checkout Session for a donation
     *
     * @param  Donation  $donation  The donation to create a session for
     * @return string The checkout session URL to redirect the user to
     *
     * @throws RuntimeException If session creation fails or config is missing
     */
    public function createCheckoutSession(Donation $donation): string
    {
        if (empty($this->config['publishable_key'])) {
            throw new RuntimeException('Stripe publishable key is not configured');
        }

        Log::info('Payment initiated', [
            'donation_id' => $donation->id,
            'amount' => $donation->amount,
            'currency' => $donation->currency,
            'gateway' => 'stripe',
        ]);

        $idempotencyKey = $donation->idempotency_key ?? IdempotencyHelper::generateKey('stripe');

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($donation->currency),
                    'product_data' => ['name' => 'تبرع - '.$donation->donor_name],
                    'unit_amount' => (int) ($donation->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('payment.success', ['locale' => $donation->locale, 'donation' => $donation->id]),
            'cancel_url' => route('payment.cancel', ['locale' => $donation->locale, 'donation' => $donation->id]),
            'metadata' => ['donation_id' => $donation->id],
        ];

        if ($donation->is_recurring && $donation->recurring_interval) {
            $intervalMap = [
                'monthly' => 'month',
                'quarterly' => 'month',
                'yearly' => 'year',
            ];
            $intervalCountMap = [
                'monthly' => 1,
                'quarterly' => 3,
                'yearly' => 1,
            ];
            $interval = $intervalMap[$donation->recurring_interval] ?? 'month';
            $intervalCount = $intervalCountMap[$donation->recurring_interval] ?? 1;

            $sessionParams['mode'] = 'subscription';
            $sessionParams['line_items'][0]['price_data']['recurring'] = [
                'interval' => $interval,
                'interval_count' => $intervalCount,
            ];
            unset($sessionParams['payment_method_types']);
            $sessionParams['subscription_data'] = [
                'metadata' => ['donation_id' => $donation->id],
            ];
        } else {
            $sessionParams['mode'] = 'payment';
        }

        try {
            $session = Session::create($sessionParams, ['idempotency_key' => $idempotencyKey]);

            $updateData = ['transaction_id' => $session->id];
            if ($donation->is_recurring && isset($session->subscription)) {
                $updateData['stripe_subscription_id'] = $session->subscription;
            }
            $donation->update($updateData);

            return $session->url;
        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('فشل إنشاء جلسة الدفع في Stripe: '.$e->getMessage());
        }
    }

    /**
     * Verify Stripe webhook signature
     *
     * @param  string  $payload  The raw request body
     * @param  string  $sigHeader  The Stripe-Signature header value
     * @return array The parsed event data
     *
     * @throws RuntimeException If verification fails
     */
    public function verifyWebhook(string $payload, string $sigHeader): array
    {
        $endpointSecret = $this->config['webhook_secret'] ?? '';
        if (empty($endpointSecret)) {
            Log::critical('Stripe webhook secret is not configured');
            throw new RuntimeException('Stripe webhook secret is not configured');
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            return $event->toArray();
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            throw new RuntimeException('Stripe webhook: invalid payload');
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: signature verification failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Stripe webhook: signature verification failed');
        }
    }
}
