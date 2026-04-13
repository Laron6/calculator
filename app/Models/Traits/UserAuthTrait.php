<?php

namespace App\Models\Traits;

trait UserAuthTrait
{
    public function isAdmin(): bool
    {
        return $this->email === 'admin@example.com';
    }
    
    public function getActiveDevicesCountAttribute(): int
    {
        return $this->activeDevices()->count();
    }
    
    public function hasOtherActiveSessions(string $currentSessionId): bool
    {
        return $this->activeDevices()
            ->where('session_id', '!=', $currentSessionId)
            ->exists();
    }
    
    public function terminateOtherSessions(string $currentSessionId): void
    {
        $this->activeDevices()
            ->where('session_id', '!=', $currentSessionId)
            ->update(['is_active' => false]);
    }
    
    public function getLastActiveDeviceAttribute()
    {
        return $this->devices()
            ->orderBy('last_activity', 'desc')
            ->first();
    }
    
    public function isSessionActive(string $sessionId): bool
    {
        return $this->devices()
            ->where('session_id', $sessionId)
            ->where('is_active', true)
            ->exists();
    }
}