<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkGroup extends Model
{
    protected $fillable = ['name'];
    
    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'group_worker')->withTimestamps();
    }
    
    public function productivities()
    {
        return $this->hasMany(GroupProductivity::class);
    }
}