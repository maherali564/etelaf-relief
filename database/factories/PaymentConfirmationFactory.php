<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\PaymentConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentConfirmationFactory extends Factory
{
    protected $model = PaymentConfirmation::class;

    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'type' => fake()->randomElement(['bank_transfer', 'crypto']),
            'reference_number' => fake()->bothify('REF-####-????'),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'sender_name' => fake()->name(),
            'sender_account' => fake()->bankAccountNumber(),
            'transfer_date' => fake()->date(),
            'notes' => fake()->sentence(),
            'proof_document' => null,
            'status' => 'pending',
            'admin_notes' => null,
            'confirmed_at' => null,
        ];
    }
}
