@extends('layouts.app')

@section('title', 'Расчёт производительности')

@section('content')
<div class="card full-width">
    <div class="flex-between">
        <h3><i class="fas fa-chart-simple"></i> Расчёт производительности труда</h3>
    </div>

    @if($groups->count() == 0)
        <div class="alert alert-info statistics-alert">
            <i class="fas fa-info-circle"></i> Группы не созданы. Создайте группу на вкладке "Редактор рабочих"
        </div>
    @else
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
    @endif

    @if(!$selectedGroup && $groups->count() > 0)
        <div class="alert alert-info statistics-alert">
            <i class="fas fa-info-circle"></i> Выберите группу для расчёта производительности
        </div>
    @elseif($selectedGroup && $selectedGroup->workers->count() == 0)
        <div class="alert alert-info statistics-alert">
            <i class="fas fa-info-circle"></i> В группе нет рабочих. Добавьте рабочих в группу на вкладке "Редактор рабочих"
        </div>
    @elseif($selectedGroup && $selectedGroup->workers->count() > 0)
        <div class="alert alert-info statistics-info-alert">
            <i class="fas fa-info-circle"></i> 
            Производительность труда рассчитывается по формуле: <strong>ПТ = V / T</strong>, где:<br>
            • <strong>V</strong> — объём выпущенной продукции (шт)<br>
            • <strong>T</strong> — затраченное время (часы)
        </div>
        
        <form action="/group/{{ $selectedGroup->id }}/productivity" method="POST">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger statistics-error-alert">
                    <ul class="statistics-error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="worker-col">Рабочий</th>
                            <th class="volume-col">Объём продукции (шт)</th>
                            <th class="time-col">Затраченное время (ч)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedGroup->workers as $worker)
                        <tr>
                            <td>
                                <strong>{{ $worker->last_name }} {{ $worker->first_name }}</strong><br>
                                <small>{{ $worker->patronymic }}</small>
                            </td>
                            <td>
                                <input type="number" step="1" name="volumes[{{ $worker->id }}]" 
                                       value="{{ $volumes[$worker->id] ?? '' }}" 
                                       placeholder="Объём, шт" class="input-small input-volume-time">
                            </td>
                            <td>
                                <input type="number" step="0.5" name="times[{{ $worker->id }}]" 
                                       value="{{ $times[$worker->id] ?? '' }}" 
                                       placeholder="Время, ч" class="input-small input-volume-time">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="calc-buttons">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить данные</button>
                <a href="/group/{{ $selectedGroup->id }}/calculate?tab=statistics&group_id={{ $selectedGroup->id }}" class="btn btn-success"><i class="fas fa-calculator"></i> Рассчитать производительность</a>
            </div>
        </form>
    @endif

    @if($showResults && count($calculatedResults) > 0)
    @php
        $totalProductivity = array_sum(array_column($calculatedResults, 'productivity'));
        $averageProductivity = count($calculatedResults) > 0 ? round($totalProductivity / count($calculatedResults), 2) : 0;
    @endphp
    <div class="mt-4 results-section">
        <h3><i class="fas fa-chart-line"></i> Результаты расчёта</h3>
        
        <div class="table-wrapper">
            <table class="table results-table">
                <thead>
                    <tr>
                        <th>Рабочий</th>
                        <th>Производительность труда (шт/ч)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calculatedResults as $res)
                    <tr>
                        <td><strong>{{ $res['worker']->last_name }} {{ $res['worker']->first_name }}</strong></td>
                        <td class="highlight">{{ number_format($res['productivity'], 2) }} шт/ч</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value">{{ number_format($totalProductivity, 2) }} шт/ч</div>
                <div class="metric-label">Общая производительность группы</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">{{ number_format($averageProductivity, 2) }} шт/ч</div>
                <div class="metric-label">Средняя производительность</div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection