<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\UserAuthTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, UserAuthTrait;
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }
    
    public function activeDevices()
    {
        return $this->devices()->where('is_active', true);
    }
}