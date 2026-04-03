<?php

namespace App\Services;

use App\Models\GroupProductivity;

class ProductivityService
{
    public function saveProductivities($groupId, array $productivities)
    {
        foreach ($productivities as $workerId => $value) {
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