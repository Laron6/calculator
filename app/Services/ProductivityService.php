<?php

namespace App\Services;

use App\Models\GroupProductivity;
use App\Models\WorkGroup;
use App\Models\Worker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductivityService
{
    public function saveProductivities($groupId, array $volumes, array $times)
    {
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
            
        if (!$group) {
            Log::error('Группа не найдена или не принадлежит пользователю', ['group_id' => $groupId]);
            return false;
        }
        
        $workerIds = $group->workers->pluck('id')->toArray();
        $savedCount = 0;
        
        foreach ($workerIds as $workerId) {
            // Проверяем, что рабочий принадлежит текущему пользователю
            $worker = Worker::where('id', $workerId)
                ->where('user_id', Auth::id())
                ->exists();
                
            if (!$worker) {
                continue;
            }
            
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
                    'value' => round($productivity, 2),
                    'user_id' => Auth::id()
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
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy('worker_id');
    }
    
    /**
     * Получение только объёмов продукции для группы
     */
    public function getVolumes($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id())
            ->pluck('volume', 'worker_id')
            ->toArray();
    }
    
    /**
     * Получение только времени для группы
     */
    public function getTimes($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id())
            ->pluck('time', 'worker_id')
            ->toArray();
    }
    
    /**
     * Получение производительности для группы
     */
    public function getProductivityValues($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id())
            ->pluck('value', 'worker_id')
            ->toArray();
    }
}