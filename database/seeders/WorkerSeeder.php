<?php

namespace Database\Seeders;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем первого пользователя или создаём демо-пользователя для сидера
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'System User',
                'email' => 'system@example.com',
                'password' => bcrypt('password'),
            ]);
        }
        
        // Создаём 20 рабочих через фабрику с user_id
        $workers = Worker::factory()->count(20)->create([
            'user_id' => $user->id
        ]);
        
        // Создаём основную группу с user_id
        $group = WorkGroup::create([
            'name' => 'Основная группа',
            'user_id' => $user->id
        ]);
        
        // Добавляем 9 рабочих в группу
        $group->workers()->attach($workers->take(9)->pluck('id'));
        
        // Создаём дополнительные группы для примера
        $this->createAdditionalGroups($workers, $user);
    }
    
    private function createAdditionalGroups($workers, $user): void
    {
        $groups = [
            ['name' => 'Проектная бригада', 'count' => 5],
            ['name' => 'Вечерняя смена', 'count' => 4],
            ['name' => 'Резервная группа', 'count' => 2],
        ];
        
        foreach ($groups as $groupData) {
            $group = WorkGroup::create([
                'name' => $groupData['name'],
                'user_id' => $user->id
            ]);
            $group->workers()->attach($workers->random($groupData['count'])->pluck('id'));
        }
    }
}