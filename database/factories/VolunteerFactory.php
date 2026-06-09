<?php

namespace Database\Factories;

use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerFactory extends Factory
{
    protected $model = Volunteer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'skills' => fake()->sentence(),
            'availability' => fake()->randomElement(['دوام كامل', 'جزئي', 'عطل نهاية الأسبوع']),
            'message' => fake()->paragraph(),
            'status' => 'pending',
            'locale' => 'ar',
        ];
    }
}
