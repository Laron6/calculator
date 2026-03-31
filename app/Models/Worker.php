<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    protected $fillable = ['last_name', 'first_name', 'patronymic', 'age', 'experience', 'gender'];
    
    public function getFullNameAttribute()
    {
        return trim("{$this->last_name} {$this->first_name} {$this->patronymic}");
    }
    
    public function groups()
    {
        return $this->belongsToMany(WorkGroup::class, 'group_worker');
    }
}