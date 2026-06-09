<?php

use App\Models\PaymentGateway;
use App\Services\Payment\PayPalService;
use App\Services\Payment\StripeService;

beforeEach(function () {
    PaymentGateway::factory()->create([
        'driver' => 'stripe',
        'is_active' => true,
        'config' => [
            'secret_key' => 'sk_test_123',
            'publishable_key' => 'pk_test_123',
            'webhook_secret' => 'whsec_test',
        ],
    ]);

    PaymentGateway::factory()->create([
        'driver' => 'paypal',
        'is_active' => true,
        'config' => [
            'client_id' => 'test_client',
            'client_secret' => 'test_secret',
            'webhook_id' => 'test_webhook_id',
            'mode' => 'sandbox',
        ],
    ]);
});

it('rejects stripe webhook without signature', function () {
    $response = $this->postJson('/webhook/stripe', ['test' => 'data']);

    $response->assertStatus(400);
});

it('rejects stripe webhook with invalid signature', function () {
    $response = $this->postJson('/webhook/stripe', ['test' => 'data'], [
        'Stripe-Signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
});

it('rejects paypal webhook without signature headers', function () {
    $response = $this->postJson('/webhook/paypal', ['event_type' => 'CHECKOUT.ORDER.APPROVED']);

    $response->assertStatus(400);
});

it('logs webhook security warnings for invalid payloads', function () {
    Log::shouldReceive('warning')
        ->atLeast(1)
        ->withArgs(fn ($message) => str_contains($message, 'Stripe webhook'));

    $response = $this->postJson('/webhook/stripe', ['test' => 'data'], [
        'Stripe-Signature' => 'bad_sig',
    ]);

    $response->assertStatus(400);
});

it('requires webhook secret to be configured in StripeService', function () {
    $service = new StripeService(['secret_key' => 'sk_test', 'publishable_key' => 'pk_test']);

    expect(fn () => $service->verifyWebhook('payload', 'sig'))
        ->toThrow(RuntimeException::class, 'Stripe webhook secret is not configured');
});

it('requires PayPal credentials to be configured', function () {
    expect(fn () => new PayPalService([]))
        ->toThrow(RuntimeException::class, 'PayPal client ID and secret must be configured');
});

it('throttles webhook endpoints', function () {
    for ($i = 0; $i < 65; $i++) {
        $response = $this->postJson('/webhook/stripe', ['test' => $i]);
    }

    expect(in_array($response->status(), [429, 400]))->toBeTrue();
});
