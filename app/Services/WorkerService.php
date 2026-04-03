<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;

class WorkerService
{
    public function getAll()
    {
        return Worker::orderBy('last_name')->get();
    }
    
    public function create(array $data)
    {
        return Worker::create($data);
    }
    
    public function update(Worker $worker, array $data)
    {
        return $worker->update($data);
    }
    
    public function delete(Worker $worker)
    {
        $worker->groups()->detach();
        GroupProductivity::where('worker_id', $worker->id)->delete();
        return $worker->delete();
    }
}