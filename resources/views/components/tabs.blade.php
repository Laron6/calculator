<div class="tabs-wrapper">
    <div class="tabs">
        <a href="/?tab=workers" class="tab {{ request()->routeIs('home') && (request()->get('tab') == 'workers' || request()->get('tab') == null) ? 'active' : (request()->routeIs('workers.index') ? 'active' : '') }}">
            <i class="fas fa-users"></i> Редактор рабочих
        </a>
        <a href="/?tab=statistics" class="tab {{ request()->routeIs('home') && request()->get('tab') == 'statistics' ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Статистика группы
        </a>
        <a href="/charts" class="tab {{ request()->routeIs('charts') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Графики
        </a>
    </div>
</div>