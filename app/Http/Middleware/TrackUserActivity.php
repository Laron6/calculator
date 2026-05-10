<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\DeviceService;

class TrackUserActivity
{
    protected $deviceService;
    
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    
    public function handle($request, Closure $next)
    {
        if (auth()->check() && $request->hasSession()) {
            $sessionId = $request->session()->getId();
            $this->deviceService->updateActivity($sessionId);
        }
        
        return $next($request);
    }
}