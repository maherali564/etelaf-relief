<?php

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;

beforeEach(function () {
    PaymentGateway::factory()->create([
        'driver' => 'bank_transfer',
        'is_active' => true,
        'config' => ['bank_name' => 'Test'],
    ]);
    PaymentMethod::factory()->create(['is_active' => true]);
});

it('creates a donation with pending status', function () {
    $donation = Donation::factory()->create(['status' => 'pending']);

    expect($donation)->toBeInstanceOf(Donation::class)
        ->and($donation->status)->toBe('pending');
});

it('marks donation as completed', function () {
    $donation = Donation::factory()->create(['status' => 'pending']);

    $donation->markCompleted();

    expect($donation->fresh()->status)->toBe('completed')
        ->and($donation->fresh()->reviewed_at)->not->toBeNull();
});

it('marks donation as failed with reason', function () {
    $donation = Donation::factory()->create(['status' => 'pending']);

    $donation->markFailed('Insufficient funds');

    expect($donation->fresh()->status)->toBe('failed')
        ->and($donation->fresh()->rejection_reason)->toBe('Insufficient funds');
});

it('scopes completed donations', function () {
    Donation::factory()->count(3)->create(['status' => 'completed']);
    Donation::factory()->create(['status' => 'pending']);

    $completed = Donation::completed()->get();

    expect($completed)->toHaveCount(3);
});
