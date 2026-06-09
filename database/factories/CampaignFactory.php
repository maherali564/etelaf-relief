<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'title' => ['en' => fake()->sentence(3), 'ar' => 'حملة تجريبية'],
            'description' => ['en' => fake()->paragraph(), 'ar' => 'وصف الحملة'],
            'goal_amount' => fake()->randomFloat(2, 1000, 100000),
            'raised_amount' => 0,
            'slug' => fake()->unique()->slug(),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'is_active' => true,
        ];
    }
}
