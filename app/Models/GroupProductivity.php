<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\GroupProductivityAttributes;
use App\Models\Traits\GroupProductivityScopes;
use App\Models\Traits\GroupProductivityRelations;

class GroupProductivity extends Model
{
    use HasFactory, GroupProductivityAttributes, GroupProductivityScopes, GroupProductivityRelations;
    
    protected $fillable = ['work_group_id', 'worker_id', 'value'];
    
    protected $casts = [
        'value' => 'float'
    ];
}