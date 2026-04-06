<?php

namespace Database\Factories;

use App\Models\GroupProductivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupProductivityFactory extends Factory
{
    protected $model = GroupProductivity::class;

    public function definition(): array
    {
        return [
            'value' => $this->faker->randomFloat(2, 0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}