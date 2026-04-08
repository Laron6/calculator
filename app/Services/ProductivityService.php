<?php

namespace App\Services;

use App\Models\GroupProductivity;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Log;

class ProductivityService
{
    public function saveProductivities($groupId, array $productivities)
    {
        $group = WorkGroup::find($groupId);
        if (!$group) {
            return false;
        }
        
        $workerIds = $group->workers->pluck('id')->toArray();
        
        foreach ($productivities as $workerId => $value) {
            // Проверяем, что рабочий действительно входит в группу
            if (!in_array($workerId, $workerIds)) {
                continue;
            }
            
            if ($value !== null && $value !== '') {
                GroupProductivity::updateOrCreate(
                    ['work_group_id' => $groupId, 'worker_id' => $workerId],
                    ['value' => (float)$value]
                );
            }
        }
        return true;
    }
    
    public function getProductivities($groupId)
    {
        return GroupProductivity::where('work_group_id', $groupId)
            ->get()
            ->keyBy('worker_id');
    }
}