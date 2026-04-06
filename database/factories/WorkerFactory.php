<?php

namespace Database\Factories;

use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkerFactory extends Factory
{
    protected $model = Worker::class;

    public function definition(): array
    {
        $firstNames = ['Иван', 'Пётр', 'Алексей', 'Дмитрий', 'Андрей', 'Сергей', 'Владимир', 'Николай', 'Михаил', 'Александр'];
        $lastNames = ['Иванов', 'Петров', 'Сидоров', 'Алексеев', 'Дмитриев', 'Андреев', 'Сергеев', 'Владимиров', 'Николаев', 'Михайлов'];
        $patronymics = ['Иванович', 'Петрович', 'Алексеевич', 'Дмитриевич', 'Андреевич', 'Сергеевич', 'Владимирович', 'Николаевич', 'Михайлович', 'Александрович'];
        
        $age = rand(18, 65);
        $maxExperience = $age - 18;
        
        return [
            'last_name' => $lastNames[array_rand($lastNames)],
            'first_name' => $firstNames[array_rand($firstNames)],
            'patronymic' => $patronymics[array_rand($patronymics)],
            'age' => $age,
            'experience' => rand(0, $maxExperience),
            'gender' => rand(0, 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}