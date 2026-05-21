<?php

namespace Database\Factories;

use App\Models\WorkGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkGroupFactory extends Factory
{
    protected $model = WorkGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['бригада', 'отдел', 'команда']),
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}