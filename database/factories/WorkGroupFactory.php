<?php

namespace Database\Factories;

use App\Models\WorkGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkGroupFactory extends Factory
{
    protected $model = WorkGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['бригада', 'отдел', 'команда']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}