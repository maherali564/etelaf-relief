<?php

namespace Database\Factories;

use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterFactory extends Factory
{
    protected $model = Newsletter::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'is_subscribed' => true,
            'subscribed_at' => now(),
            'verified_at' => now(),
        ];
    }
}
