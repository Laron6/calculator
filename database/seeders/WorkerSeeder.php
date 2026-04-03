<?php

namespace Database\Seeders;

use App\Models\Worker;
use App\Models\WorkGroup;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run()
    {
        $workers = [];
        for ($i = 1; $i <= 20; $i++) {
            $workers[] = [
                'last_name' => "Фамилия{$i}",
                'first_name' => "Имя{$i}",
                'patronymic' => "Отчество{$i}",
                'age' => rand(18, 60),
                'experience' => rand(0, 40),
                'gender' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        Worker::insert($workers);
        
        $group = WorkGroup::create(['name' => 'Тестовая группа']);
        $workers = Worker::take(5)->get();
        $group->workers()->attach($workers->pluck('id'));
    }
}