<?php

namespace Database\Seeders;

use App\Models\Worker;
use App\Models\WorkGroup;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём 20 рабочих через фабрику
        $workers = Worker::factory()->count(20)->create();
        
        // Создаём основную группу
        $group = WorkGroup::create(['name' => 'Основная группа']);
        
        // Добавляем 9 рабочих в группу
        $group->workers()->attach($workers->take(9)->pluck('id'));
        
        // Создаём дополнительные группы для примера
        $this->createAdditionalGroups($workers);
    }
    
    private function createAdditionalGroups($workers): void
    {
        $groups = [
            ['name' => 'Проектная бригада', 'count' => 5],
            ['name' => 'Вечерняя смена', 'count' => 4],
            ['name' => 'Резервная группа', 'count' => 2],
        ];
        
        foreach ($groups as $groupData) {
            $group = WorkGroup::create(['name' => $groupData['name']]);
            $group->workers()->attach($workers->random($groupData['count'])->pluck('id'));
        }
    }
}