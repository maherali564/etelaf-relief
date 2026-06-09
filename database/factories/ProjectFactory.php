<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'goal_amount' => fake()->randomFloat(2, 1000, 100000),
            'raised_amount' => 0,
            'sort_order' => 0,
        ];
    }
}
