<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Log;

class TrackUserActivity
{
    protected $deviceService;
    
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        if (auth()->check() && 
            $request->hasSession() && 
            $request->method() === 'GET' && 
            $response->getStatusCode() < 400) {
            try {
                $sessionId = $request->session()->getId();
                $this->deviceService->updateActivity($sessionId);
            } catch (\Exception $e) {
                // Логируем, но не прерываем выполнение
                Log::warning('Failed to update activity: ' . $e->getMessage());
            }
        }
        
        return $response;
    }
}