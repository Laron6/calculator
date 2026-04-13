<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceService
{
    private function getRealIp(Request $request)
    {
        $ip = $request->ip();
        
        $trustedHeaders = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
        ];
        
        foreach ($trustedHeaders as $header) {
            if ($request->server->has($header)) {
                $ips = explode(',', $request->server->get($header));
                $ip = trim($ips[0]);
                break;
            }
        }
        
        return $ip;
    }
    
    public function registerDevice($user, $sessionId, Request $request)
    {
        UserDevice::where('session_id', $sessionId)->delete();
        
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
        UserDevice::where('session_id', $sessionId)->update([
            'is_active' => false
        ]);
    }
    
    public function terminateOtherSessions($userId, $currentSessionId)
    {
        UserDevice::where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->update(['is_active' => false]);
    }
    
    public function getUserDevices($userId)
    {
        return UserDevice::where('user_id', $userId)
            ->where('is_active', true)  // <-- показываем только активные
            ->orderBy('last_activity', 'desc')
            ->get();
    }
}