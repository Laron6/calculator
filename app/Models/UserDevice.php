<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'ip_address', 'user_agent',
        'device_name', 'platform', 'browser', 'last_activity', 'is_active'
    ];
    
    protected $casts = [
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}