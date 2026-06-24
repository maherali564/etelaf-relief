<?php

use App\Models\PaymentGateway;
use App\Services\Payment\WiseService;

beforeEach(function () {
    PaymentGateway::factory()->create([
        'driver' => 'wise',
        'is_active' => true,
        'config' => [
            'api_token' => 'wise_test_token',
            'webhook_secret' => 'whsec_test_wise',
            'profile_id' => '12345',
        ],
    ]);
});

it('rejects wise webhook without signature', function () {
    $response = $this->postJson('/webhook/wise', ['data' => 'test']);

    $response->assertStatus(400);
});

it('rejects wise webhook with invalid HMAC signature', function () {
    $response = $this->postJson('/webhook/wise', ['data' => 'test'], [
        'X-Wise-Signature' => 'invalid_hmac',
    ]);

    $response->assertStatus(400);
});

it('requires Wise webhook secret to be configured', function () {
    $service = new WiseService(['api_token' => 'test']);

    $result = $service->verifyWebhook('payload', 'sig');

    expect($result)->toBeNull();
});

it('verifies wise webhook HMAC correctly', function () {
    $secret = 'whsec_test_wise';
    $payload = '{"event":"transfer_completed"}';
    $signature = hash_hmac('sha256', $payload, $secret);

    $service = new WiseService([
        'api_token' => 'test',
        'webhook_secret' => $secret,
    ]);

    $result = $service->verifyWebhook($payload, $signature);

    expect($result)->toHaveKey('event', 'transfer_completed');
});

it('fails wise HMAC with wrong signature', function () {
    $service = new WiseService([
        'api_token' => 'test',
        'webhook_secret' => 'whsec_test_wise',
    ]);

    $result = $service->verifyWebhook('{"event":"test"}', 'wrong_sig');

    expect($result)->toBeNull();
});

it('handles empty wise signature', function () {
    $service = new WiseService([
        'api_token' => 'test',
        'webhook_secret' => 'whsec_test_wise',
    ]);

    $result = $service->verifyWebhook('{}', '');

    expect($result)->toBeNull();
});

it('provides wise payment info', function () {
    $service = new WiseService([
        'api_token' => 'test',
        'bank_name' => 'Test Bank',
        'account_name' => 'Test Account',
    ]);

    $info = $service->getPaymentInfo();

    expect($info['bank_name'])->toBe('Test Bank')
        ->and($info['account_name'])->toBe('Test Account');
});
