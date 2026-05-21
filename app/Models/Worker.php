<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\WorkerAttributes;
use App\Models\Traits\WorkerScopes;
use App\Models\Traits\WorkerRelations;

class Worker extends Model
{
    use HasFactory, WorkerAttributes, WorkerScopes, WorkerRelations;
    
    protected $fillable = ['last_name', 'first_name', 'patronymic', 'age', 'experience', 'gender', 'user_id'];
    
    protected $casts = [
        'age' => 'integer',
        'experience' => 'integer',
        'gender' => 'integer'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}