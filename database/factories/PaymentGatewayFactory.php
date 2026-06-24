<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' Gateway',
            'slug' => fake()->slug(),
            'driver' => 'bank_transfer',
            'type' => 'offline',
            'config' => [],
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
