<?php

namespace Database\Factories;

use App\Models\Cryptocurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptocurrencyFactory extends Factory
{
    protected $model = Cryptocurrency::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' Coin',
            'symbol' => strtoupper(fake()->lexify('???')),
            'logo' => null,
            'min_amount' => 0.001,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
