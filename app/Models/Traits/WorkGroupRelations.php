<?php

namespace App\Models\Traits;

use App\Models\Worker;
use App\Models\GroupProductivity;

trait WorkGroupRelations
{
    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'group_worker')->withTimestamps();
    }
    
    public function productivities()
    {
        return $this->hasMany(GroupProductivity::class);
    }
}