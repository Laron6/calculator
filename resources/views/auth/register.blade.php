@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div style="text-align: center;">
            <div class="auth-logo register">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="auth-title register">
                <i class="fas fa-user-plus"></i> 
                Регистрация
            </h3>
            <p class="auth-subtitle">
                Создайте аккаунт для работы с системой
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
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label><i class="fas fa-user"></i> Имя</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Иван Иванов" required>
                @error('name')
                    <small style="color: #f5576c; font-size: 11px; display: block; margin-top: 5px;">{{ $message }}</small>
                @enderror
            </div>
            
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
                <div class="password-hint">
                    <i class="fas fa-info-circle"></i> 
                    Пароль должен содержать:
                    <ul class="password-requirements" style="margin-top: 5px; padding-left: 20px;">
                        <li>• минимум 8 символов</li>
                        <li>• заглавные и строчные буквы (A-Z, a-z)</li>
                        <li>• хотя бы одну цифру (0-9)</li>
                        <li>• спецсимволы (@$!%*#?&)</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-check-circle"></i> Подтверждение пароля</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" required>
                @error('password_confirmation')
                    <small style="color: #f5576c; font-size: 11px; display: block; margin-top: 5px;">{{ $message }}</small>
                @enderror
            </div>
            
            <button type="submit" class="btn-submit-register">
                <i class="fas fa-user-check"></i>
                <span>Зарегистрироваться</span>
                <i class="fas fa-arrow-right arrow-icon"></i>
            </button>
        </form>
        
        <div class="auth-divider" style="text-align: center;">
            <p class="auth-text">
                Уже есть аккаунт? 
                <a href="{{ route('login') }}" class="auth-link register">Войдите</a>
            </p>
        </div>
        
        <div class="auth-info register">
            <i class="fas fa-chart-simple"></i>
            <div class="auth-info-text">
                <strong>Что даёт регистрация?</strong><br>
                Доступ к расчёту производительности методом Гаусса, управление рабочими группами, 
                создание графиков и многое другое.
            </div>
        </div>
    </div>
</div>
@endsection