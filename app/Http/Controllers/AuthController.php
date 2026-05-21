<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDevice;
use App\Services\DeviceService;
use App\Auth\LoginRequest;
use App\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;

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
        $key = 'login_attempts_' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Слишком много попыток входа. Попробуйте через ' . $seconds . ' секунд.',
            ])->onlyInput('email');
        }
        
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->remember)) {
            RateLimiter::clear($key);
            
            $request->session()->regenerate();
            
            $this->deviceService->registerDevice(
                auth()->user(),
                $request->session()->getId(),
                $request
            );
            
            return redirect()->intended('/');
        }
        
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
        $key = 'register_attempts_' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Слишком много попыток регистрации. Попробуйте через ' . $seconds . ' секунд.',
            ])->onlyInput('email');
        }
        
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
        
        RateLimiter::clear($key);
        
        return redirect('/');
    }
    
    public function logout(Request $request)
    {
        // Получаем реальный session_id из БД
        $dbSession = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->first();
        
        if ($dbSession) {
            $this->deviceService->terminateSession($dbSession->id);
        } else {
            $sessionId = $request->session()->getId();
            $this->deviceService->terminateSession($sessionId);
        }
        
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
        $device = UserDevice::where('id', $deviceId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        
        $currentDbSession = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->first();
        
        $isCurrent = false;
        if ($currentDbSession && $device->session_id === $currentDbSession->id) {
            $isCurrent = true;
        }
        
        if ($isCurrent) {
            return redirect()->route('devices')->with('error', 'Нельзя завершить текущую сессию');
        }
        
        $this->deviceService->terminateSession($device->session_id);
        
        return redirect()->route('devices')->with('success', 'Сессия завершена');
    }
    
    public function terminateOtherDevices()
    {
        $currentDbSession = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->first();
        
        if (!$currentDbSession) {
            return redirect()->route('devices')->with('error', 'Не удалось определить текущую сессию');
        }
        
        $currentSessionId = $currentDbSession->id;
        
        $otherSessions = UserDevice::where('user_id', auth()->id())
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