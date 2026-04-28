<?php

namespace Database\Factories;

use App\Models\GroupProductivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupProductivityFactory extends Factory
{
    protected $model = GroupProductivity::class;

    public function definition(): array
    {
        $volume = $this->faker->numberBetween(1, 5000);
        $time = $this->faker->numberBetween(1, 100);
        $productivity = round($volume / $time, 2);
        
        return [
            'volume' => $volume,
            'time' => $time,
            'value' => $productivity,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}