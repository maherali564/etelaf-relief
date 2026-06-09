<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'donor_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'payment_method_id' => PaymentMethod::factory(),
            'status' => 'pending',
            'locale' => 'en',
            'donated_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'reviewed_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed', 'rejection_reason' => 'Payment declined']);
    }
}
