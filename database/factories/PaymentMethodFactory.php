<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word().' Payment',
            'gateway_id' => PaymentGateway::factory(),
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
