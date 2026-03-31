<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupProductivity extends Model
{
    protected $fillable = ['work_group_id', 'worker_id', 'value'];
    
    public function group()
    {
        return $this->belongsTo(WorkGroup::class);
    }
    
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}