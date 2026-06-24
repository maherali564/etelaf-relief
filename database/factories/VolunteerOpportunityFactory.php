<?php

namespace Database\Factories;

use App\Models\VolunteerOpportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerOpportunityFactory extends Factory
{
    protected $model = VolunteerOpportunity::class;

    public function definition(): array
    {
        return [
            'title' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'description' => ['en' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'requirements' => fake()->paragraph(),
            'location' => fake()->city(),
            'slots' => fake()->numberBetween(1, 50),
            'hours_required' => fake()->numberBetween(5, 40),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+3 months'),
            'status' => 'active',
            'is_active' => true,
        ];
    }
}
