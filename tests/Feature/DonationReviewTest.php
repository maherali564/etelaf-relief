<?php

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\DonationReviewService;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('activity_log')) {
        Schema::create('activity_log', function ($table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

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

it('approves donation under review', function () {
    $donation = Donation::factory()->create(['status' => 'under_review']);
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $result = $service->approve($donation, $admin->id);

    expect($donation->fresh()->status)->toBe('completed')
        ->and($donation->fresh()->reviewed_by)->toBe($admin->id);
});

it('rejects donation with reason', function () {
    $donation = Donation::factory()->create(['status' => 'under_review']);
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $result = $service->reject($donation, 'Documentation incomplete', $admin->id);

    expect($donation->fresh()->status)->toBe('failed')
        ->and($donation->fresh()->rejection_reason)->toBe('Documentation incomplete');
});

it('rejects donation without reason', function () {
    $donation = Donation::factory()->create(['status' => 'under_review']);
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $service->reject($donation, null, $admin->id);

    expect($donation->fresh()->status)->toBe('failed');
});

it('approves already completed donation (idempotent)', function () {
    $donation = Donation::factory()->completed()->create();
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $service->approve($donation, $admin->id);

    expect($donation->fresh()->status)->toBe('completed');
});

it('approves failed donation (re-approve)', function () {
    $donation = Donation::factory()->failed()->create();
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $service->approve($donation, $admin->id);

    expect($donation->fresh()->status)->toBe('completed');
});
