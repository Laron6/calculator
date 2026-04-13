@extends('layouts.app')

@section('title', 'Мои устройства')

@section('content')
<div class="auth-container">
    <div class="auth-card" style="max-width: 600px;">
        <div style="text-align: center;">
            <div class="auth-logo devices">
                <i class="fas fa-laptop"></i>
            </div>
            <h3 class="auth-title devices">
                <i class="fas fa-laptop"></i> Мои устройства
            </h3>
            <p class="auth-subtitle">
                Управление активными сессиями
            </p>
        </div>
        
        @if(session('success'))
        <div class="alert alert-success" style="margin: 15px 0; padding: 12px; border-radius: 12px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger" style="margin: 15px 0; padding: 12px; border-radius: 12px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif
        
        @php
            $currentSessionId = session()->getId();
            $otherDevicesCount = $devices->where('session_id', '!=', $currentSessionId)->count();
        @endphp
        
        @if($otherDevicesCount > 0)
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <form action="{{ route('devices.terminate-others') }}" method="POST" onsubmit="return confirm('Вы уверены? Все остальные сессии будут завершены. Текущая сессия останется активной.');">
                @csrf
                <button type="submit" class="btn-warning" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 28px;">
                    <i class="fas fa-power-off"></i> 
                    <span>Завершить все остальные сессии ({{ $otherDevicesCount }})</span>
                </button>
            </form>
        </div>
        @endif
        
        @foreach($devices as $device)
        <div class="device-item">
            <div>
                <div class="device-name">
                    @php
                        $deviceIcon = 'fa-laptop';
                        if (stripos($device->platform, 'Windows') !== false) $deviceIcon = 'fa-windows';
                        elseif (stripos($device->platform, 'Mac') !== false) $deviceIcon = 'fa-apple';
                        elseif (stripos($device->platform, 'Android') !== false) $deviceIcon = 'fa-android';
                        elseif (stripos($device->platform, 'iOS') !== false) $deviceIcon = 'fa-mobile-alt';
                        elseif (stripos($device->platform, 'Linux') !== false) $deviceIcon = 'fa-linux';
                    @endphp
                    <i class="fab {{ $deviceIcon }}"></i> 
                    {{ $device->device_name ?: 'Неизвестное устройство' }}
                </div>
                <div class="device-details">
                    {{ $device->browser ?: 'Неизвестный браузер' }} на {{ $device->platform ?: 'Неизвестная ОС' }}
                </div>
                <div class="device-meta">
                    <i class="fas fa-map-marker-alt"></i> IP: {{ $device->ip_address ?: 'неизвестно' }} • 
                    <i class="fas fa-clock"></i> Последняя активность: {{ $device->last_activity ? $device->last_activity->diffForHumans() : 'неизвестно' }}
                </div>
            </div>
            @if($device->session_id !== $currentSessionId)
            <form action="{{ route('devices.terminate', $device->id) }}" method="POST" onsubmit="return confirm('Завершить эту сессию? Пользователь будет вынужден войти заново.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" style="padding: 6px 16px; font-size: 12px; white-space: nowrap;">
                    <i class="fas fa-times-circle"></i> Завершить
                </button>
            </form>
            @else
            <span class="device-badge">
                <i class="fas fa-check-circle"></i> Текущее устройство
            </span>
            @endif
        </div>
        @endforeach
        
        @if($devices->count() == 0)
        <div class="auth-info devices" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i>
            <div class="auth-info-text">
                <strong>Нет активных сессий</strong><br>
                У вас нет активных сессий. Войдите в систему, чтобы увидеть свои устройства.
            </div>
        </div>
        @endif
        
        <div class="auth-info devices" style="margin-top: 20px;">
            <i class="fas fa-shield-alt"></i>
            <div class="auth-info-text">
                <strong>Безопасность</strong><br>
                Здесь отображаются все устройства, на которых выполнен вход в ваш аккаунт. 
                Вы можете завершить любую сессию, кроме текущей.
            </div>
        </div>
    </div>
</div>
@endsection