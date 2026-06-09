<?php

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\ConfirmationService;
use App\Services\DonationReviewService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    Event::fake();
    Log::spy();

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

it('DonationReviewService approves donation', function () {
    $donation = Donation::factory()->create(['status' => 'under_review']);
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $service->approve($donation, $admin->id);

    expect($donation->fresh()->status)->toBe('completed');
});

it('DonationReviewService rejects donation', function () {
    $donation = Donation::factory()->create(['status' => 'under_review']);
    $admin = User::factory()->create();

    $service = new DonationReviewService;
    $service->reject($donation, 'Invalid proof', $admin->id);

    expect($donation->fresh()->status)->toBe('failed')
        ->and($donation->fresh()->rejection_reason)->toBe('Invalid proof');
});

it('CertificateService throws 404 for non-completed donation', function () {
    $donation = Donation::factory()->create(['status' => 'pending']);

    $service = new CertificateService;
    expect(fn () => $service->downloadCertificate($donation))
        ->toThrow(NotFoundHttpException::class);
});

it('ConfirmationService validates gateway driver', function () {
    $donation = Donation::factory()->create([
        'payment_method_id' => PaymentMethod::first()->id,
    ]);

    $service = new ConfirmationService;
    $driver = $service->validateGateway($donation);

    expect($driver)->toBe('bank_transfer');
});

it('ConfirmationService rejects invalid gateway', function () {
    $donation = Donation::factory()->create(['payment_method_id' => null]);

    $service = new ConfirmationService;
    $driver = $service->validateGateway($donation);

    expect($driver)->toBeNull();
});
