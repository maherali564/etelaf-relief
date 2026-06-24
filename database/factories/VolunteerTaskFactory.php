<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerOpportunity;
use App\Models\VolunteerTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerTaskFactory extends Factory
{
    protected $model = VolunteerTask::class;

    public function definition(): array
    {
        return [
            'volunteer_opportunity_id' => VolunteerOpportunity::factory(),
            'volunteer_id' => Volunteer::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => 'assigned',
            'assigned_by' => User::factory(),
        ];
    }
}
