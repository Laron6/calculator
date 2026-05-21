<?php

namespace Database\Factories;

use App\Models\GroupProductivity;
use App\Models\User;
use App\Models\WorkGroup;
use App\Models\Worker;
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
            'work_group_id' => WorkGroup::factory(),
            'worker_id' => Worker::factory(),
            'volume' => $volume,
            'time' => $time,
            'value' => $productivity,
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}