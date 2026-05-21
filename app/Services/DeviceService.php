<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    private const MAX_ACTIVE_DEVICES = 5;
    
    private function getRealIp(Request $request)
    {
        return $request->ip();
    }
    
    public function registerDevice($user, $sessionId, Request $request)
    {
        UserDevice::where('session_id', $sessionId)->delete();
        
        $activeCount = UserDevice::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
        
        if ($activeCount >= self::MAX_ACTIVE_DEVICES) {
            $oldest = UserDevice::where('user_id', $user->id)
                ->where('is_active', true)
                ->orderBy('last_activity', 'asc')
                ->first();
            
            if ($oldest) {
                $this->terminateSession($oldest->session_id);
            }
        }
        
        $realIp = $this->getRealIp($request);
        
        return UserDevice::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => $realIp,
            'user_agent' => $request->userAgent(),
            'device_name' => $this->getDeviceName($request->userAgent()),
            'platform' => $this->getPlatform($request->userAgent()),
            'browser' => $this->getBrowser($request->userAgent()),
            'last_activity' => now(),
            'is_active' => true,
        ]);
    }
    
    private function getDeviceName($userAgent)
    {
        if (str_contains($userAgent, 'iPhone')) return 'iPhone';
        if (str_contains($userAgent, 'iPad')) return 'iPad';
        if (str_contains($userAgent, 'Android')) return 'Android Phone';
        if (str_contains($userAgent, 'Windows')) return 'Windows PC';
        if (str_contains($userAgent, 'Macintosh')) return 'Mac';
        if (str_contains($userAgent, 'Linux')) return 'Linux PC';
        return 'Неизвестное устройство';
    }
    
    private function getPlatform($userAgent)
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
        return 'Unknown';
    }
    
    private function getBrowser($userAgent)
    {
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) return 'Safari';
        if (str_contains($userAgent, 'Edg')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        return 'Unknown';
    }
    
    public function updateActivity($sessionId)
    {
        UserDevice::where('session_id', $sessionId)->update([
            'last_activity' => now()
        ]);
    }
    
    public function terminateSession($sessionId)
    {
        // Удаляем реальную Laravel-сессию
        DB::table('sessions')->where('id', $sessionId)->delete();
        
        // Деактивируем запись в user_devices
        UserDevice::where('session_id', $sessionId)->update([
            'is_active' => false
        ]);
    }
    
    public function terminateOtherSessions($userId, $currentSessionId)
    {
        // Получаем ТОЛЬКО чужие сессии
        $otherSessions = UserDevice::where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->where('is_active', true)
            ->get();
        
        foreach ($otherSessions as $session) {
            // Удаляем реальные Laravel-сессии
            DB::table('sessions')->where('id', $session->session_id)->delete();
            
            // Деактивируем запись в user_devices
            $session->is_active = false;
            $session->save();
        }
        
        // Текущее устройство НЕ ТРОГАЕМ
    }
    
    public function getUserDevices($userId)
    {
        return UserDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();
    }  
}