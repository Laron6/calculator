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
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedGroup->workers as $worker)
                    <tr>
                        <td>{{ $worker->last_name }} {{ $worker->first_name }} {{ $worker->patronymic }}</td>
                        <td>
                            <input type="number" step="0.1" name="productivities[{{ $worker->id }}]" 
                                   value="{{ $productivityValues[$worker->id] ?? '' }}" 
                                   placeholder="0" class="input-small">
                        </td>
                    </tr>
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
        
        @if($selectedGroup && $selectedGroup->workers->count() != 9)
            <div class="alert alert-warning" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Внимание! В оригинальном алгоритме Delphi расчет производился для группы из 9 рабочих. 
                Сейчас в группе {{ $selectedGroup->workers->count() }} рабочих. 
                Результаты могут отличаться от ожидаемых.
            </div>
        @endif
        
        @if($selectedGroup && $selectedGroup->workers->count() < 4)
            <div class="alert alert-warning" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Для расчета итоговой производительности (L) и коэффициента (R) необходимо минимум 4 рабочих в группе.
                Сейчас в группе {{ $selectedGroup->workers->count() }} рабочих.
            </div>
        @endif
        
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($selectedGroup && $selectedGroup->workers->count() >= 4)
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
        @else
        <div class="metrics-grid">
            <div class="metric-card" style="opacity: 0.5;">
                <div class="metric-value">—</div>
                <div class="metric-label">Итоговая производительность (L)</div>
            </div>
            <div class="metric-card" style="opacity: 0.5;">
                <div class="metric-value">—</div>
                <div class="metric-label">Коэффициент (R)</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($showAlternative && count($alternativeResults) > 0)
    <div class="alternative-results">
        <h3><i class="fas fa-chart-line"></i> Результаты альтернативного расчета</h3>
        
        @if($selectedGroup && $selectedGroup->workers->count() != 9)
            <div class="alert alert-warning" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Внимание! В оригинальном алгоритме Delphi альтернативный расчет производился для группы из 9 рабочих. 
                Сейчас в группе {{ $selectedGroup->workers->count() }} рабочих. 
                Результаты могут отличаться от ожидаемых.
            </div>
        @endif
        
        @if($selectedGroup && $selectedGroup->workers->count() < 4)
            <div class="alert alert-warning" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Для расчета итоговой производительности (L) и коэффициента (R) необходимо минимум 4 рабочих в группе.
                Сейчас в группе {{ $selectedGroup->workers->count() }} рабочих.
            </div>
        @endif
        
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($selectedGroup && $selectedGroup->workers->count() >= 4)
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
        @else
        <div class="metrics-grid">
            <div class="metric-card" style="opacity: 0.5;">
                <div class="metric-value">—</div>
                <div class="metric-label">Итоговая производительность (L)</div>
            </div>
            <div class="metric-card" style="opacity: 0.5;">
                <div class="metric-value">—</div>
                <div class="metric-label">Коэффициент (R)</div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>