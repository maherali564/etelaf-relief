<?php

use App\Models\PaymentGateway;

beforeEach(function () {
    $g = PaymentGateway::factory()->create([
        'driver' => 'wise',
        'is_active' => true,
        'config' => [
            'api_token' => 'wise_test_token',
            'webhook_secret' => 'whsec_test_wise',
            'profile_id' => '12345',
        ],
    ]);
    dump('Created gateway ID: ' . $g->id);
    dump('Config after create: ' . json_encode($g->config));
});

it('checks config in db', function () {
    $all = PaymentGateway::where('driver', 'wise')->get();
    dump('Total wise gateways: ' . $all->count());
    foreach ($all as $g) {
        dump("  ID: {$g->id}, Config: " . json_encode($g->config));
    }
    $response = $this->postJson('/webhook/wise', ['data' => 'test']);
    dump('Status: ' . $response->getStatusCode());
    dump('Body: ' . $response->getContent());
});
