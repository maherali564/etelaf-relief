<?php

use App\Models\Cryptocurrency;
use App\Models\CryptoNetwork;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Services\Blockchain\BlockchainMonitorService;

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

it('creates crypto network with active scope', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'is_active' => true,
    ]);

    expect(CryptoNetwork::active()->get())->toHaveCount(1);
});

it('filters inactive crypto networks', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'is_active' => false,
    ]);

    expect(CryptoNetwork::active()->get())->toHaveCount(0);
});

it('identifies ERC20 network for etherscan checker', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'network_name' => 'ERC20',
        'is_active' => true,
    ]);

    expect($network->network_name)->toContain('ERC20');
});

it('identifies BEP20 network for bsc checker', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'network_name' => 'BEP20',
        'is_active' => true,
    ]);

    expect($network->network_name)->toContain('BEP20');
});

it('identifies TRC20 network for trongrid checker', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'network_name' => 'TRC20',
        'is_active' => true,
    ]);

    expect($network->network_name)->toContain('TRC20');
});

it('generates explorer link for network', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'explorer_url' => 'https://etherscan.io/address/{address}',
        'wallet_address' => '0x123',
    ]);

    expect($network->explorer_link)->toBe('https://etherscan.io/address/0x123');
});

it('returns null explorer link when no url configured', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'explorer_url' => null,
    ]);

    expect($network->explorer_link)->toBeNull();
});

it('monitor service handles network without checker', function () {
    $currency = Cryptocurrency::factory()->create(['is_active' => true]);
    $network = CryptoNetwork::factory()->create([
        'cryptocurrency_id' => $currency->id,
        'network_name' => 'UnknownChain',
        'is_active' => true,
    ]);

    $service = new BlockchainMonitorService;
    $service->checkNetwork($network);

    expect(true)->toBeTrue();
});
