@extends('layouts.app')

@section('title', 'Мои устройства')

@section('content')
<div class="auth-container">
    <div class="auth-card devices-card">
        <div class="devices-header">
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
        <div class="alert alert-success devices-alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger devices-alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif
        
        @php
            $currentSessionId = session()->getId();
            $otherDevicesCount = $devices->where('session_id', '!=', $currentSessionId)->count();
        @endphp
        
        @if($otherDevicesCount > 0)
        <div class="terminate-all-wrapper">
            <form action="{{ route('devices.terminate-others') }}" method="POST" onsubmit="return confirm('Вы уверены? Все остальные сессии будут завершены. Текущая сессия останется активной.');">
                @csrf
                <button type="submit" class="btn-warning terminate-all-btn">
                    <i class="fas fa-power-off"></i> 
                    <span>Завершить все остальные сессии ({{ $otherDevicesCount }})</span>
                </button>
            </form>
        </div>
        @endif
        
        @foreach($devices as $device)
        <div class="device-item">
            <div class="device-info">
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
                <button type="submit" class="btn-danger terminate-session-btn">
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
        <div class="auth-info devices no-devices">
            <i class="fas fa-info-circle"></i>
            <div class="auth-info-text">
                <strong>Нет активных сессий</strong><br>
                У вас нет активных сессий. Войдите в систему, чтобы увидеть свои устройства.
            </div>
        </div>
        @endif
        
        <div class="auth-info devices security-info">
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