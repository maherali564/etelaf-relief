<?php

use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Services\DonationService;

beforeEach(function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'bank_transfer',
        'is_active' => true,
        'config' => ['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123'],
    ]);
    PaymentMethod::factory()->create([
        'gateway_id' => $gateway->id,
        'is_active' => true,
    ]);
});

it('processes a donation', function () {
    $service = new DonationService;
    $donation = $service->processDonation([
        'donor_name' => 'John',
        'email' => 'john@example.com',
        'amount' => 50,
        'payment_method_id' => PaymentMethod::first()->id,
        'currency' => 'USD',
    ]);

    expect($donation)->toBeInstanceOf(Donation::class)
        ->and($donation->status)->toBe('pending')
        ->and($donation->idempotency_key)->not->toBeNull();
});

it('links donation to existing donor by email', function () {
    $donor = Donor::factory()->create(['email' => 'existing@example.com']);

    $service = new DonationService;
    $donation = $service->processDonation([
        'donor_name' => 'Existing',
        'email' => 'existing@example.com',
        'amount' => 25,
        'payment_method_id' => PaymentMethod::first()->id,
    ]);

    expect($donation->donor_id)->toBe($donor->id);
});

it('detects offline payment method', function () {
    $service = new DonationService;
    $donation = Donation::factory()->create([
        'payment_method_id' => PaymentMethod::first()->id,
    ]);

    expect($service->isOfflinePaymentMethod($donation))->toBeTrue();
});

it('detects online payment method', function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'stripe',
        'is_active' => true,
        'config' => ['secret_key' => 'sk_test', 'publishable_key' => 'pk_test'],
    ]);
    $method = PaymentMethod::factory()->create(['gateway_id' => $gateway->id]);

    $service = new DonationService;
    $donation = Donation::factory()->create(['payment_method_id' => $method->id]);

    expect($service->isOfflinePaymentMethod($donation))->toBeFalse();
});

it('loads donation page data with caching', function () {
    Project::factory()->create(['is_active' => true]);
    $service = new DonationService;
    $data = $service->loadDonationPageData();

    expect($data)->toHaveKeys(['paymentMethods', 'projects', 'stories', 'cryptocurrencies', 'donations']);
});

it('initiates payment with retry logic', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => PaymentMethod::first()->id,
        'status' => 'pending',
    ]);

    $service = new DonationService;
    $result = $service->initiatePayment($donation);

    expect($result)->toHaveKey('type', 'instructions');
});
