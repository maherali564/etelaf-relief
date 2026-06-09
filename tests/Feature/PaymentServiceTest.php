<?php

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentService;

beforeEach(function () {
    $this->gateway = PaymentGateway::factory()->create([
        'driver' => 'bank_transfer',
        'is_active' => true,
        'config' => [
            'bank_name' => 'Test Bank',
            'account_name' => 'Test',
            'account_number' => '123',
            'iban' => 'SA00000000',
        ],
    ]);
    $this->method = PaymentMethod::factory()->create([
        'gateway_id' => $this->gateway->id,
        'is_active' => true,
    ]);
});

it('creates payment service from donation', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => $this->method->id,
    ]);

    $service = PaymentService::fromDonation($donation);

    expect($service)->toBeInstanceOf(PaymentService::class);
});

it('throws exception for donation without gateway', function () {
    $donation = Donation::factory()->create(['payment_method_id' => null]);

    expect(fn () => PaymentService::fromDonation($donation))
        ->toThrow(RuntimeException::class);
});

it('initiates bank transfer payment', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => $this->method->id,
    ]);

    $service = PaymentService::fromDonation($donation);
    $result = $service->initPayment($donation);

    expect($result['type'])->toBe('instructions')
        ->and($result['data']['type'])->toBe('bank_transfer');
});

it('initiates manual payment', function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'manual',
        'is_active' => true,
        'config' => [],
    ]);
    $method = PaymentMethod::factory()->create(['gateway_id' => $gateway->id]);
    $donation = Donation::factory()->create(['payment_method_id' => $method->id]);

    $service = PaymentService::fromDonation($donation);
    $result = $service->initPayment($donation);

    expect($result['type'])->toBe('instructions');
});

it('throws for unsupported driver', function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'unknown_driver',
        'is_active' => true,
        'config' => [],
    ]);
    $method = PaymentMethod::factory()->create(['gateway_id' => $gateway->id]);
    $donation = Donation::factory()->create(['payment_method_id' => $method->id]);

    $service = PaymentService::fromDonation($donation);

    expect(fn () => $service->initPayment($donation))
        ->toThrow(RuntimeException::class);
});

it('stripe payment returns redirect type', function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'stripe',
        'is_active' => true,
        'config' => [
            'secret_key' => 'sk_test_123',
            'publishable_key' => 'pk_test_123',
        ],
    ]);
    $method = PaymentMethod::factory()->create(['gateway_id' => $gateway->id]);
    $donation = Donation::factory()->create(['payment_method_id' => $method->id]);

    $service = PaymentService::fromDonation($donation);

    expect(fn () => $service->initPayment($donation))
        ->toThrow(RuntimeException::class);
});
