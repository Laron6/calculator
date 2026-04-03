<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Графики производительности</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="glass-card">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Графики производительности</h1>
            <p>Визуализация индивидуальной производительности рабочих</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="tabs-wrapper">
            <div class="tabs">
                <a href="/" class="tab"><i class="fas fa-users"></i> Редактор рабочих</a>
                <a href="/?tab=statistics" class="tab"><i class="fas fa-chart-bar"></i> Статистика группы</a>
                <a href="/charts" class="tab active"><i class="fas fa-chart-line"></i> Графики</a>
            </div>
        </div>

        <div class="tab-content active" style="padding: 40px;">
            <div class="card">
                <div class="group-selector">
                    <div class="group-buttons">
                        @foreach($groups as $group)
                        <a href="/charts?group_id={{ $group->id }}" 
                           class="group-btn {{ $selectedGroupId == $group->id ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            {{ $group->name }}
                            <span class="group-badge">{{ $group->workers->count() }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                @if($selectedGroup && $selectedGroup->workers->count() > 0 && count($bVec) > 0)
                <div style="background: rgba(255,255,255,0.05); border-radius: 24px; padding: 24px; margin-top: 24px;">
                    <canvas id="productivityChart"></canvas>
                </div>
                <div style="background: rgba(255,255,255,0.05); border-radius: 24px; padding: 24px; margin-top: 24px;">
                    <canvas id="decisionsChart"></canvas>
                </div>
                <script>
                    const labels = {!! json_encode($labels) !!};
                    const bVec = {!! json_encode($bVec) !!};
                    const decisions = {!! json_encode($decisions) !!};
                    
                    new Chart(document.getElementById('productivityChart'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Производительность при отсутствии рабочего',
                                data: bVec,
                                borderColor: '#667eea',
                                backgroundColor: 'rgba(102,126,234,0.1)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#667eea',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { labels: { color: 'white' } } },
                            scales: {
                                y: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'white' } },
                                x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'white', rotation: 45 } }
                            }
                        }
                    });
                    
                    new Chart(document.getElementById('decisionsChart'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Индивидуальная производительность',
                                data: decisions,
                                borderColor: '#43e97b',
                                backgroundColor: 'rgba(67,233,123,0.1)',
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#43e97b',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { labels: { color: 'white' } } },
                            scales: {
                                y: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'white' } },
                                x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: 'white', rotation: 45 } }
                            }
                        }
                    });
                </script>
                @elseif($selectedGroup && $selectedGroup->workers->count() > 0)
                <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                    <i class="fas fa-calculator"></i> Сначала выполните расчет производительности на вкладке "Статистика группы"
                </div>
                @elseif($selectedGroup)
                <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                    <i class="fas fa-users"></i> В группе нет рабочих
                </div>
                @else
                <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                    <i class="fas fa-info-circle"></i> Выберите группу для отображения графиков
                </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <p>© 2026. Все права защищены.</p>
                <p class="footer-small">Система объективной оценки производительности труда</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>