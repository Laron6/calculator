<?php

namespace App\Services;

use App\Models\GroupProductivity;
use App\Models\WorkGroup;
use App\Models\Worker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductivityService
{
    public function saveProductivities($groupId, array $volumes, array $times, array $recordDates = [])
    {
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
            
        if (!$group) {
            Log::error('Группа не найдена', ['group_id' => $groupId]);
            return ['success' => false, 'saved_count' => 0, 'errors' => ['Группа не найдена']];
        }
        
        $workerIds = $group->workers->pluck('id')->toArray();
        $savedCount = 0;
        $errors = [];
        
        foreach ($workerIds as $workerId) {
            $volume = $volumes[$workerId] ?? null;
            $time = $times[$workerId] ?? null;
            
            // Пропускаем если оба поля пустые
            if (($volume === null || $volume === '') && ($time === null || $time === '')) {
                continue;
            }
            
            // Получаем дату записи для конкретного рабочего
            $recordDate = $recordDates[$workerId] ?? now()->format('Y-m-d');
            
            // Валидация даты
            if (!strtotime($recordDate)) {
                $errors[] = "Неверный формат даты для рабочего ID {$workerId}";
                continue;
            }
            
            $volume = $volume !== null && $volume !== '' ? (float)$volume : 0;
            $time = $time !== null && $time !== '' ? (float)$time : 0;
            
            $productivity = 0;
            if ($time > 0 && $volume > 0) {
                $productivity = $volume / $time;
            }
            
            GroupProductivity::updateOrCreate(
                [
                    'work_group_id' => $groupId, 
                    'worker_id' => $workerId,
                    'record_date' => $recordDate
                ],
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
            'saved_count' => $savedCount,
            'errors' => $errors
        ]);
        
        return ['success' => true, 'saved_count' => $savedCount, 'errors' => $errors];
    }
    
    public function getProductivities($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy('worker_id');
    }
    
    public function getVolumes($groupId, $from = null, $to = null)
    {
        $query = GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id());
        
        if ($from) {
            $query->whereDate('record_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('record_date', '<=', $to);
        }
        
        return $query->pluck('volume', 'worker_id')->toArray();
    }
    
    public function getTimes($groupId, $from = null, $to = null)
    {
        $query = GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id());
        
        if ($from) {
            $query->whereDate('record_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('record_date', '<=', $to);
        }
        
        return $query->pluck('time', 'worker_id')->toArray();
    }
    
    public function getRecordDates($groupId, $from = null, $to = null)
    {
        $query = GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id());
        
        if ($from) {
            $query->whereDate('record_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('record_date', '<=', $to);
        }
        
        return $query->pluck('record_date', 'worker_id')->toArray();
    }
    
    public function getProductivityValues($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id())
            ->pluck('value', 'worker_id')
            ->toArray();
    }
    
    public function hasDataForPeriod($groupId, $from = null, $to = null)
    {
        $query = GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id());
        
        if ($from) {
            $query->whereDate('record_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('record_date', '<=', $to);
        }
        
        return $query->exists();
    }
}