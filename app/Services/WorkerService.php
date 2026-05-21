<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Illuminate\Support\Facades\Auth;

class WorkerService
{
    public function getAll()
    {
        return Worker::where('user_id', Auth::id())->orderBy('last_name')->get();
    }
    
    public function create(array $data)
    {
        $data['user_id'] = Auth::id();
        return Worker::create($data);
    }
    
    public function update(Worker $worker, array $data)
    {
        if ($worker->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на редактирование этого рабочего');
        }
        return $worker->update($data);
    }
    
    public function delete(Worker $worker)
    {
        if ($worker->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав на удаление этого рабочего');
        }
        
        $worker->groups()->detach();
        GroupProductivity::where('worker_id', $worker->id)->delete();
        return $worker->delete();
    }
}