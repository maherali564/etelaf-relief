<?php

use App\Models\Cryptocurrency;
use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;

beforeEach(function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'crypto',
        'is_active' => true,
        'config' => [],
    ]);
    $this->method = PaymentMethod::factory()->create([
        'gateway_id' => $gateway->id,
        'is_active' => true,
    ]);
});

it('creates a crypto transaction via factory', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'is_active' => true,
    ]);
    $tx = CryptoTransaction::factory()->create([
        'crypto_network_id' => $network->id,
    ]);

    expect($tx)->toBeInstanceOf(CryptoTransaction::class)
        ->and($tx->status)->toBe('pending')
        ->and(strlen($tx->txid))->toBeGreaterThanOrEqual(32);
});

it('creates a completed transaction', function () {
    $tx = CryptoTransaction::factory()->completed()->create();

    expect($tx->status)->toBe('completed');
});

it('creates a failed transaction', function () {
    $tx = CryptoTransaction::factory()->failed()->create();

    expect($tx->status)->toBe('failed');
});

it('belongs to a network', function () {
    $tx = CryptoTransaction::factory()->create();

    expect($tx->network)->toBeInstanceOf(CryptoNetwork::class);
});

it('creates a crypto donation with matching gateway', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => $this->method->id,
    ]);

    expect($donation->paymentMethod->gateway->driver)->toBe('crypto');
});

it('stores txid as unique identifier', function () {
    $tx = CryptoTransaction::factory()->create();

    expect($tx->txid)->not->toBeEmpty();
});

it('tracks matched donation', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => $this->method->id,
    ]);
    $tx = CryptoTransaction::factory()->create([
        'matched_donation_id' => $donation->id,
    ]);

    expect($tx->donation->id)->toBe($donation->id);
});

it('stores raw data as JSON', function () {
    $raw = ['block' => 123456, 'confirmations' => 10];
    $tx = CryptoTransaction::factory()->create(['raw_data' => $raw]);

    expect($tx->raw_data)->toBe($raw);
});
