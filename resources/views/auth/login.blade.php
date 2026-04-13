@extends('layouts.app')

@section('title', 'Вход в систему')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div style="text-align: center;">
            <div class="auth-logo login">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="auth-title login">
                <i class="fas fa-sign-in-alt"></i> 
                Добро пожаловать
            </h3>
            <p class="auth-subtitle">
                Войдите в систему для доступа к калькулятору производительности
            </p>
        </div>
        
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="ivan@example.com" required>
                @error('email')
                    <small style="color: #f5576c; font-size: 11px; display: block; margin-top: 5px;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" name="password" placeholder="••••••••" required>
                @error('password')
                    <small style="color: #f5576c; font-size: 11px; display: block; margin-top: 5px;">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember">
                    <span><i class="fas fa-clock"></i> Запомнить меня</span>
                </label>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket"></i>
                <span>Войти</span>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </button>
        </form>
        
        <div class="auth-divider" style="text-align: center;">
            <p class="auth-text" style="margin-bottom: 15px;">
                Нет аккаунта?
            </p>
            <a href="{{ route('register') }}" class="btn-register-auth">
                <i class="fas fa-user-plus"></i>
                <span>Зарегистрируйтесь</span>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </a>
        </div>
        
        <div class="auth-info login">
            <i class="fas fa-calculator"></i>
            <div class="auth-info-text">
                <strong>Оценка производительности</strong><br>
                После входа вам станут доступны: расчёт методом Гаусса, управление рабочими группами, 
                визуализация графиков и экспорт данных.
            </div>
        </div>
    </div>
</div>
@endsection