<?php

namespace App\Services;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Illuminate\Support\Facades\Auth;

class GroupService
{
    public function getAll()
    {
        return WorkGroup::where('user_id', Auth::id())
            ->with('workers')
            ->orderBy('name')
            ->get();
    }
    
    public function create(array $data)
    {
        $data['user_id'] = Auth::id();
        return WorkGroup::create($data);
    }
    
    public function update(WorkGroup $group, array $data)
    {
        if ($group->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на редактирование этой группы');
        }
        return $group->update($data);
    }
    
    public function delete(WorkGroup $group)
    {
        if ($group->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на удаление этой группы');
        }
        
        $group->workers()->detach();
        GroupProductivity::where('work_group_id', $group->id)->delete();
        return $group->delete();
    }
    
    public function addWorker(WorkGroup $group, $workerId)
    {
        if ($group->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на изменение этой группы');
        }
        
        $worker = \App\Models\Worker::where('id', $workerId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        if (!$group->workers()->where('worker_id', $workerId)->exists()) {
            return $group->workers()->attach($workerId);
        }
        return false;
    }
    
    public function removeWorker(WorkGroup $group, $workerId)
    {
        if ($group->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на изменение этой группы');
        }
        
        $group->workers()->detach($workerId);
        GroupProductivity::where('work_group_id', $group->id)
            ->where('worker_id', $workerId)
            ->delete();
        return true;
    }
}