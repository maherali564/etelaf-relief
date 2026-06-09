<?php

namespace Database\Factories;

use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoTransactionFactory extends Factory
{
    protected $model = CryptoTransaction::class;

    public function definition(): array
    {
        return [
            'crypto_network_id' => CryptoNetwork::factory(),
            'txid' => '0x'.fake()->sha256(),
            'from_address' => '0x'.fake()->sha256(),
            'to_address' => '0x'.fake()->sha256(),
            'amount' => fake()->randomFloat(8, 0.01, 10),
            'currency' => fake()->randomElement(['USDT', 'USDC', 'BTC', 'ETH']),
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
