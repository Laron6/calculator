<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Объективная оценка производительности</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="glass-card">
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-chart-line"></i> Объективная оценка производительности</h1>
                <p>Метод Гаусса — точный расчет индивидуальной производительности для групп рабочих</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

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

        <div class="tab-content {{ $activeTab == 'workers' ? 'active' : '' }}">
            <div class="grid-3">
                <div class="card">
                    <h3><i class="fas fa-user-plus"></i> Добавить рабочего</h3>
                    <form action="/worker/add" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Фамилия</label>
                            <input type="text" name="last_name" placeholder="Иванов" required>
                        </div>
                        <div class="form-group">
                            <label>Имя</label>
                            <input type="text" name="first_name" placeholder="Иван" required>
                        </div>
                        <div class="form-group">
                            <label>Отчество</label>
                            <input type="text" name="patronymic" placeholder="Иванович">
                        </div>
                        <div class="form-group">
                            <label>Возраст</label>
                            <div class="number-input-wrapper">
                                <input type="number" name="age" placeholder="лет" class="number-input" required id="ageInput" min="18" max="100" value="18">
                                <div class="number-controls">
                                    <button type="button" class="number-btn" onclick="changeValue('ageInput', -1, 18, 100)">−</button>
                                    <button type="button" class="number-btn" onclick="changeValue('ageInput', 1, 18, 100)">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Стаж</label>
                            <div class="number-input-wrapper">
                                <input type="number" name="experience" placeholder="лет" class="number-input" required id="expInput" min="0" max="80" value="0">
                                <div class="number-controls">
                                    <button type="button" class="number-btn" onclick="changeValue('expInput', -1, 0, 80)">−</button>
                                    <button type="button" class="number-btn" onclick="changeValue('expInput', 1, 0, 80)">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Пол</label>
                            <div style="display: flex; gap: 12px; margin-top: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                                    <input type="radio" name="gender" value="0" style="width: 18px; height: 18px; margin: 0;" checked>
                                    <span><i class="fas fa-mars"></i> Мужской</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                                    <input type="radio" name="gender" value="1" style="width: 18px; height: 18px; margin: 0;">
                                    <span><i class="fas fa-venus"></i> Женский</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-plus"></i> Добавить рабочего
                        </button>
                    </form>

                    <hr>

                    <div class="flex-between">
                        <h3 style="font-size: 16px;"><i class="fas fa-file-import"></i> Импорт/Экспорт</h3>
                    </div>
                    <form action="/workers/import" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div style="margin-bottom: 16px;">
                            <label for="fileInput" class="file-upload-label">
                                <i class="fas fa-folder-open"></i> Выберите файл (.lst)
                            </label>
                            <input type="file" id="fileInput" name="file" accept=".lst,.txt" style="display: none;">
                            <span id="fileName" class="file-name">Файл не выбран</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-full" id="submitBtn" disabled style="opacity: 0.5;">
                            <i class="fas fa-upload"></i> Загрузить список
                        </button>
                    </form>
                    <a href="/workers/export" class="btn btn-success w-full" style="margin-top: 12px; text-align: center;">
                        <i class="fas fa-download"></i> Сохранить список (.lst)
                    </a>
                </div>

                <div class="card">
                    <h3><i class="fas fa-list"></i> Все рабочие <span class="badge">{{ $workers->count() }}</span></h3>
                    <div class="worker-list">
                        @foreach($workers as $worker)
                        <div class="worker-item">
                            <span class="worker-name">{{ $worker->last_name }} {{ $worker->first_name }}</span>
                            <div class="worker-actions">
                                <a href="/worker/edit/{{ $worker->id }}" class="btn-link" title="Редактировать"><i class="fas fa-pen"></i></a>
                                <a href="/worker/delete/{{ $worker->id }}" class="btn-link" onclick="return confirm('Удалить рабочего?')" title="Удалить"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="flex-between" style="flex-direction: column; align-items: stretch; gap: 16px;">
                        <h3><i class="fas fa-layer-group"></i> Рабочие группы</h3>
                        <form action="/group/create" method="POST" style="display: flex; gap: 12px; width: 100%;">
                            @csrf
                            <input type="text" name="name" placeholder="Название группы" style="flex: 1; margin: 0;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; white-space: nowrap;">
                                <i class="fas fa-plus"></i> Создать
                            </button>
                        </form>
                    </div>
                    <div class="groups-list" style="margin-top: 20px;">
                        @foreach($groups as $group)
                        <div class="group-card {{ $selectedGroupId == $group->id ? 'selected' : '' }}">
                            <a href="?tab=workers&group_id={{ $group->id }}" class="group-link">
                                <i class="fas fa-folder"></i> {{ $group->name }}
                                <span class="group-count">({{ $group->workers->count() }})</span>
                            </a>
                            <div class="group-actions">
                                <a href="/group/edit/{{ $group->id }}" class="btn-link" title="Редактировать"><i class="fas fa-pen"></i></a>
                                <a href="/group/delete/{{ $group->id }}" class="btn-link" onclick="return confirm('Удалить группу?')" title="Удалить"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($selectedGroup)
            <div class="card mt-4">
                <h3><i class="fas fa-people-arrows"></i> Управление группой: {{ $selectedGroup->name }}</h3>
                <div class="grid-2">
                    <div>
                        <h4><i class="fas fa-user-plus"></i> Доступные рабочие</h4>
                        <div class="worker-list compact">
                            @foreach($workers as $worker)
                                @if(!$selectedGroup->workers->contains($worker))
                                <div class="worker-item">
                                    <span class="worker-name" style="color: white;">{{ $worker->last_name }} {{ $worker->first_name }}</span>
                                    <form action="/group/{{ $selectedGroup->id }}/add-worker" method="POST" class="inline-form">
                                        @csrf
                                        <input type="hidden" name="worker_id" value="{{ $worker->id }}">
                                        <button type="submit" class="btn btn-primary btn-sm" title="Добавить"><i class="fas fa-plus"></i></button>
                                    </form>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4><i class="fas fa-users"></i> Состав группы</h4>
                        <div class="worker-list compact">
                            @foreach($selectedGroup->workers as $worker)
                            <div class="worker-item">
                                <span class="worker-name" style="color: white;">{{ $worker->last_name }} {{ $worker->first_name }}</span>
                                <form action="/group/{{ $selectedGroup->id }}/remove-worker" method="POST" class="inline-form">
                                    @csrf
                                    <input type="hidden" name="worker_id" value="{{ $worker->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить"><i class="fas fa-minus"></i></button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="tab-content {{ $activeTab == 'statistics' ? 'active' : '' }}">
            <div class="card">
                <div class="flex-between">
                    <h3><i class="fas fa-chart-simple"></i> Статистика группы</h3>
                </div>

                <div class="group-selector">
                    <div class="group-buttons">
                        @foreach($groups as $group)
                        <a href="?tab=statistics&group_id={{ $group->id }}" 
                           class="group-btn {{ $selectedGroupId == $group->id ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            {{ $group->name }}
                            <span class="group-badge">{{ $group->workers->count() }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                @if(!$selectedGroup)
                    <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                        <i class="fas fa-info-circle"></i> Выберите группу для просмотра статистики
                    </div>
                @elseif($selectedGroup->workers->count() == 0)
                    <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                        <i class="fas fa-info-circle"></i> В группе нет рабочих. Добавьте рабочих в группу на вкладке "Редактор рабочих"
                    </div>
                @else
                <form action="/group/{{ $selectedGroup->id }}/productivity" method="POST">
                    @csrf
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ф.И.О отсутствующего рабочего</th>
                                    <th>Производительность</th>
                            </thead>
                            <tbody>
                                @foreach($selectedGroup->workers as $worker)
                                <tr>
                                    <td>{{ $worker->last_name }} {{ $worker->first_name }} {{ $worker->patronymic }}\\
                                    <td>
                                        <input type="number" step="0.1" name="productivities[{{ $worker->id }}]" 
                                               value="{{ $productivityValues[$worker->id] ?? '' }}" 
                                               placeholder="0" class="input-small">
                                    </td>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="calc-buttons">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
                        <a href="/group/{{ $selectedGroup->id }}/calculate?tab=statistics&group_id={{ $selectedGroup->id }}" class="btn btn-success"><i class="fas fa-calculator"></i> Рассчитать (Гаусс)</a>
                        <a href="/group/{{ $selectedGroup->id }}/calculate-alternative?tab=statistics&group_id={{ $selectedGroup->id }}" class="btn btn-secondary"><i class="fas fa-exchange-alt"></i> Альтернативный расчет</a>
                    </div>
                </form>
                @endif

                @if($showResults && count($calculatedResults) > 0)
                <div class="mt-4">
                    <h3><i class="fas fa-chart-line"></i> Результаты расчета (метод Гаусса)</h3>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr><th>Рабочий</th><th>Индивидуальная производительность</th>
                            </thead>
                            <tbody>
                                @foreach($calculatedResults as $res)
                                <tr>
                                    <td>{{ $res['worker']->last_name }} {{ $res['worker']->first_name }}</td>
                                    <td class="highlight">{{ $res['productivity'] }}</td>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value">{{ $calculatedMetrics['L'] }}</div>
                            <div class="metric-label">Итоговая производительность (L)</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">{{ $calculatedMetrics['R'] }}</div>
                            <div class="metric-label">Коэффициент (R)</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($showAlternative && count($alternativeResults) > 0)
                <div class="alternative-results">
                    <h3><i class="fas fa-chart-line"></i> Результаты альтернативного расчета</h3>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr><th>Рабочий</th><th>Индивидуальная производительность</th>
                            </thead>
                            <tbody>
                                @foreach($selectedGroup->workers as $i => $worker)
                                <tr>
                                    <td>{{ $worker->last_name }} {{ $worker->first_name }}</td>
                                    <td class="highlight">{{ round($alternativeResults[$i] ?? 0, 2) }}</td>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-value">{{ $calculatedMetrics['L_alt'] ?? 0 }}</div>
                            <div class="metric-label">Итоговая производительность (L)</div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-value">{{ $calculatedMetrics['R_alt'] ?? 0 }}</div>
                            <div class="metric-label">Коэффициент (R)</div>
                        </div>
                    </div>
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

<script>
    function changeValue(inputId, delta, minVal, maxVal) {
        let input = document.getElementById(inputId);
        let currentValue = parseInt(input.value);
        
        if (isNaN(currentValue) || currentValue === '') {
            currentValue = minVal;
            input.value = minVal;
        }
        
        let newValue = currentValue + delta;
        
        if (newValue >= minVal && newValue <= maxVal) {
            input.value = newValue;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    
    document.getElementById('fileInput').addEventListener('change', function(e) {
        var fileName = e.target.files[0]?.name || 'Файл не выбран';
        document.getElementById('fileName').innerText = fileName;
        document.getElementById('submitBtn').disabled = !e.target.files[0];
        document.getElementById('submitBtn').style.opacity = e.target.files[0] ? '1' : '0.5';
    });
    
    document.querySelector('.file-upload-label').addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });
</script>

</body>
</html>