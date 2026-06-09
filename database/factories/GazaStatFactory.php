<?php

namespace Database\Factories;

use App\Models\GazaStat;
use Illuminate\Database\Eloquent\Factories\Factory;

class GazaStatFactory extends Factory
{
    protected $model = GazaStat::class;

    public function definition(): array
    {
        return [
            'label' => ['en' => 'Stat '.fake()->word(), 'ar' => 'إحصائية '.fake()->word()],
            'value' => (string) fake()->numberBetween(100, 100000),
            'prefix' => null,
            'icon' => null,
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
