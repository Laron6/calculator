<div class="tabs-wrapper">
    <div class="tabs">
        <a href="?tab=workers" class="tab {{ $activeTab == 'workers' ? 'active' : '' }}">
            <i class="fas fa-users"></i> Редактор рабочих
        </a>
        <a href="?tab=statistics" class="tab {{ $activeTab == 'statistics' ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Статистика группы
        </a>
        <a href="/charts" class="tab">
            <i class="fas fa-chart-line"></i> Графики
        </a>
    </div>
</div>