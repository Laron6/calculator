<?php

namespace App\Models\Traits;

use App\Models\WorkGroup;
use App\Models\Worker;

trait GroupProductivityRelations
{
    public function group()
    {
        return $this->belongsTo(WorkGroup::class);
    }
    
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}