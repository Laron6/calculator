<div class="header">
    <div class="header-content">
        <h1><i class="fas fa-chart-line"></i> Расчёт производительности труда</h1>
        <p>ПТ = V / T — производительность труда = объём продукции / затраченное время</p>
        
        @auth
        <div class="user-menu">
            <div class="welcome-message">
                <i class="fas fa-user-circle"></i>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <div class="action-buttons">
                <a href="{{ route('devices') }}" class="btn-outline">
                    <i class="fas fa-laptop"></i> Устройства
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-outline">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="auth-buttons">
            <a href="{{ route('login') }}" class="btn-primary">Войти</a>
            <a href="{{ route('register') }}" class="btn-primary">Регистрация</a>
        </div>
        @endauth
    </div>
</div>