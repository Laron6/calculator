<div class="header">
    <div class="header-content">
        <h1><i class="fas fa-chart-line"></i> Объективная оценка производительности</h1>
        <p>Метод Гаусса — точный расчет индивидуальной производительности для групп рабочих</p>
        
        @auth
        <div class="user-menu" style="margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <div class="welcome-message" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); padding: 6px 18px; border-radius: 40px; border: 1px solid rgba(255,255,255,0.2);">
                <i class="fas fa-user-circle" style="font-size: 14px;"></i>
                <span style="font-weight: 500; font-size: 14px;">{{ auth()->user()->name }}</span>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('devices') }}" class="btn-outline" style="padding: 6px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                    <i class="fas fa-laptop"></i> Устройства
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding: 6px 18px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1px solid rgba(255,255,255,0.2); border-radius: 40px; color: white; transition: all 0.3s ease;">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>
            </div>
        </div>
        @else
        <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;">
            <a href="{{ route('login') }}" class="btn-primary">Войти</a>
            <a href="{{ route('register') }}" class="btn-primary">Регистрация</a>
        </div>
        @endauth
    </div>
</div>