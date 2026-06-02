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
                <a href="?tab=statistics&group_id={{ $group->id }}&from={{ request('from', $from ?? '') }}&to={{ request('to', $to ?? '') }}" 
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
        
        <form action="{{ route('productivity.save', $selectedGroup->id) }}" method="POST">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger statistics-error-alert">
                    <ul class="statistics-error-list">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-times-circle"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(!$showNoDataWarning)
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="worker-col">Рабочий</th>
                            <th class="volume-col">Объём продукции (шт)</th>
                            <th class="time-col">Затраченное время (ч)</th>
                            <th class="date-col">Дата записи</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedGroup->workers as $worker)
                        @php
                            $recordDate = $recordDates[$worker->id] ?? null;
                        @endphp
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
                            <td class="date-col">
                                @if($recordDate)
                                    {{ \Carbon\Carbon::parse($recordDate)->format('d.m.Y') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                <input type="hidden" name="record_dates[{{ $worker->id }}]" value="{{ $recordDate ?? date('Y-m-d') }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            
            <div class="calc-buttons">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить данные</button>
                <a href="{{ route('productivity.calculate', $selectedGroup->id) }}?tab=statistics&group_id={{ $selectedGroup->id }}&from={{ request('from', $from ?? '') }}&to={{ request('to', $to ?? '') }}" class="btn btn-success"><i class="fas fa-calculator"></i> Рассчитать производительность</a>
            </div>
        </form>
        
        <div class="filter-export-bar">
            <div class="filter-title">
                <i class="fas fa-chart-line"></i> Фильтрация по дате
            </div>
            <form method="GET" action="{{ route('home') }}" class="filter-form">
                <input type="hidden" name="tab" value="statistics">
                <input type="hidden" name="group_id" value="{{ $selectedGroupId }}">
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Дата от</label>
                    <input type="date" name="from" value="{{ request('from', $from ?? '') }}">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Дата до</label>
                    <input type="date" name="to" value="{{ request('to', $to ?? '') }}">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Применить фильтр
                </button>
                
                <a href="{{ route('export.statistics', ['groupId' => $selectedGroupId, 'from' => request('from', $from ?? ''), 'to' => request('to', $to ?? '')]) }}" 
                   class="btn btn-success">
                    <i class="fas fa-download"></i> Выгрузить CSV за период
                </a>
                
                <a href="{{ route('telegram.report', ['groupId' => $selectedGroupId, 'from' => request('from', $from ?? ''), 'to' => request('to', $to ?? '')]) }}" 
                   class="btn btn-telegram">
                    <i class="fab fa-telegram"></i> Отправить в Telegram
                </a>
            </form>
            <div class="filter-hint">
                <i class="fas fa-info-circle"></i> По умолчанию показаны данные за последние 3 месяца
            </div>
        </div>
        
        @if($showNoDataWarning)
        <div class="alert alert-warning statistics-warning-alert">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Внимание!</strong> За выбранный период данные не найдены.
        </div>
        @endif
        
    @endif

    @if($showResults && count($calculatedResults) > 0 && !$showNoDataWarning)
    @php
        $validResults = array_filter($calculatedResults, function($res) {
            return $res['productivity'] > 0;
        });
        $totalProductivity = array_sum(array_column($calculatedResults, 'productivity'));
        $averageProductivity = count($validResults) > 0 ? round(array_sum(array_column($validResults, 'productivity')) / count($validResults), 2) : 0;
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
                <div class="metric-label">Средняя производительность (по активным)</div>
            </div>
        </div>
        
        @if(count($validResults) < count($calculatedResults))
        <div class="alert alert-info statistics-note-alert">
            <i class="fas fa-info-circle"></i> 
            Работники с нулевой производительностью исключены из расчёта средней.
        </div>
        @endif
    </div>
    @endif
</div>
@endsection