<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use App\Models\CryptoNetwork;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoNetworkFactory extends Factory
{
    protected $model = CryptoNetwork::class;

    public function definition(): array
    {
        return [
            'cryptocurrency_id' => Cryptocurrency::factory(),
            'network_name' => fake()->randomElement(['ERC20', 'BEP20', 'TRC20', 'Bitcoin']),
            'wallet_address' => '0x'.fake()->sha256(),
            'qr_code' => null,
            'explorer_url' => null,
            'is_active' => true,
        ];
    }
}
