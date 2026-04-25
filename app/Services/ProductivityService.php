<?php

namespace App\Services;

use App\Models\GroupProductivity;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Log;

class ProductivityService
{
    public function saveProductivities($groupId, array $volumes, array $times)
    {
        $group = WorkGroup::find($groupId);
        if (!$group) {
            Log::error('Группа не найдена', ['group_id' => $groupId]);
            return false;
        }
        
        $workerIds = $group->workers->pluck('id')->toArray();
        $savedCount = 0;
        
        foreach ($workerIds as $workerId) {
            $volume = $volumes[$workerId] ?? null;
            $time = $times[$workerId] ?? null;
            
            // Пропускаем если оба поля пустые
            if (($volume === null || $volume === '') && ($time === null || $time === '')) {
                continue;
            }
            
            // Преобразуем в числа
            $volume = $volume !== null && $volume !== '' ? (float)$volume : 0;
            $time = $time !== null && $time !== '' ? (float)$time : 0;
            
            // Рассчитываем производительность по формуле ПТ = V / T
            $productivity = 0;
            if ($time > 0 && $volume > 0) {
                $productivity = $volume / $time;
            }
            
            GroupProductivity::updateOrCreate(
                ['work_group_id' => $groupId, 'worker_id' => $workerId],
                [
                    'volume' => $volume,
                    'time' => $time,
                    'value' => round($productivity, 2)
                ]
            );
            
            $savedCount++;
        }
        
        Log::info('Данные производительности сохранены', [
            'group_id' => $groupId,
            'saved_count' => $savedCount
        ]);
        
        return true;
    }
    
    /**
     * Получение данных производительности для группы
     * 
     * @param int $groupId
     * @return \Illuminate\Support\Collection
     */
    public function getProductivities($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->get()
            ->keyBy('worker_id');
    }
    
    /**
     * Получение только объёмов продукции для группы
     */
    public function getVolumes($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->pluck('volume', 'worker_id')
            ->toArray();
    }
    
    /**
     * Получение только времени для группы
     */
    public function getTimes($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->pluck('time', 'worker_id')
            ->toArray();
    }
    
    /**
     * Получение производительности для группы
     */
    public function getProductivityValues($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->pluck('value', 'worker_id')
            ->toArray();
    }
}