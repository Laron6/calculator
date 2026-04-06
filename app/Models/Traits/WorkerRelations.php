<?php

namespace App\Models\Traits;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;

trait WorkerRelations
{
    public function groups()
    {
        return $this->belongsToMany(WorkGroup::class, 'group_worker')->withTimestamps();
    }
    
    public function productivities()
    {
        return $this->hasMany(GroupProductivity::class);
    }
}