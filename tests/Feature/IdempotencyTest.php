<?php

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Services\DonationService;
use App\Services\Payment\IdempotencyHelper;

it('generates unique idempotency keys', function () {
    $key1 = IdempotencyHelper::generateKey('stripe');
    $key2 = IdempotencyHelper::generateKey('stripe');

    expect($key1)->not->toBe($key2)
        ->and($key1)->toStartWith('stripe_');
});

it('generates key with different prefix', function () {
    $key = IdempotencyHelper::generateKey('paypal');

    expect($key)->toStartWith('paypal_');
});

it('generates key without prefix (starts with underscore separator)', function () {
    $key = IdempotencyHelper::generateKey();

    expect($key)->toStartWith('_')
        ->and(strlen($key))->toBe(33);
});

it('generates keys of expected length', function () {
    $key = IdempotencyHelper::generateKey('test');

    expect(strlen($key))->toBe(37); // 'test_' (5) + 32 random chars
});

it('handles empty key gracefully', function () {
    $result = IdempotencyHelper::checkAndMark('', Donation::class);

    expect($result)->toBeFalse();
});

it('handles empty model class gracefully', function () {
    $result = IdempotencyHelper::checkAndMark('some_key', '');

    expect($result)->toBeFalse();
});

it('generates donation idempotency_key via service', function () {
    $donation = (new DonationService)->processDonation([
        'donor_name' => 'Test',
        'email' => 'test@example.com',
        'amount' => 100,
        'payment_method_id' => PaymentMethod::factory()->create([
            'gateway_id' => PaymentGateway::factory()->create([
                'driver' => 'bank_transfer',
                'is_active' => true,
                'config' => ['bank_name' => 'Test'],
            ])->id,
        ])->id,
    ]);

    expect($donation->idempotency_key)->not->toBeNull();
});
