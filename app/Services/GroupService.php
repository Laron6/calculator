<?php

namespace App\Services;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;

class GroupService
{
    public function getAll()
    {
        return WorkGroup::with('workers')->orderBy('name')->get();
    }
    
    public function create(array $data)
    {
        return WorkGroup::create($data);
    }
    
    public function update(WorkGroup $group, array $data)
    {
        return $group->update($data);
    }
    
    public function delete(WorkGroup $group)
    {
        $group->workers()->detach();
        GroupProductivity::where('work_group_id', $group->id)->delete();
        return $group->delete();
    }
    
    public function addWorker(WorkGroup $group, $workerId)
    {
        if (!$group->workers()->where('worker_id', $workerId)->exists()) {
            return $group->workers()->attach($workerId);
        }
        return false;
    }
    
    public function removeWorker(WorkGroup $group, $workerId)
    {
        $group->workers()->detach($workerId);
        GroupProductivity::where('work_group_id', $group->id)
            ->where('worker_id', $workerId)
            ->delete();
        return true;
    }
}