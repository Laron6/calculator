<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DeviceService;
use App\Auth\LoginRequest;
use App\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    protected $deviceService;
    
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function login(LoginRequest $request)
    {
        // Защита от брутфорса: максимум 5 попыток с одного IP за 1 минуту
        $key = 'login_attempts_' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Слишком много попыток входа. Попробуйте через ' . $seconds . ' секунд.',
            ])->onlyInput('email');
        }
        
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->remember)) {
            // Очищаем счётчик попыток при успешном входе
            RateLimiter::clear($key);
            
            $request->session()->regenerate();
            
            $this->deviceService->registerDevice(
                auth()->user(),
                $request->session()->getId(),
                $request
            );
            
            return redirect()->intended('/');
        }
        
        // Увеличиваем счётчик неудачных попыток
        RateLimiter::hit($key, 60);
        
        return back()->withErrors([
            'email' => 'Неверный email или пароль',
        ])->onlyInput('email');
    }
    
    public function showRegister()
    {
        return view('auth.register');
    }
    
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        Auth::login($user);
        $request->session()->regenerate();
        
        $this->deviceService->registerDevice(
            $user,
            $request->session()->getId(),
            $request
        );
        
        return redirect('/');
    }
    
    public function logout(Request $request)
    {
        $sessionId = $request->session()->getId();
        $this->deviceService->terminateSession($sessionId);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
    
    public function devices()
    {
        $devices = $this->deviceService->getUserDevices(auth()->id());
        return view('auth.devices', compact('devices'));
    }
    
    public function terminateDevice($deviceId)
    {
        $device = \App\Models\UserDevice::where('id', $deviceId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        $currentSessionId = session()->getId();
        if ($device->session_id === $currentSessionId) {
            return redirect()->route('devices')->with('error', 'Нельзя завершить текущую сессию');
        }
        
        $device->update(['is_active' => false]);
        
        return redirect()->route('devices')->with('success', 'Сессия завершена');
    }
    
    public function terminateOtherDevices()
    {
        $currentSessionId = session()->getId();
        
        $otherSessions = \App\Models\UserDevice::where('user_id', auth()->id())
            ->where('session_id', '!=', $currentSessionId)
            ->where('is_active', true)
            ->exists();
        
        if (!$otherSessions) {
            return redirect()->route('devices')->with('error', 'Нет других активных сессий');
        }
        
        $this->deviceService->terminateOtherSessions(auth()->id(), $currentSessionId);
        
        return redirect()->route('devices')->with('success', 'Все остальные сессии завершены');
    }
}