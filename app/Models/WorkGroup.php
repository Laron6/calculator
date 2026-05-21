<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\WorkGroupAttributes;
use App\Models\Traits\WorkGroupScopes;
use App\Models\Traits\WorkGroupRelations;

class WorkGroup extends Model
{
    use HasFactory, WorkGroupAttributes, WorkGroupScopes, WorkGroupRelations;
    
    protected $fillable = ['name', 'user_id'];
    
    protected $casts = [
        'id' => 'integer'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}