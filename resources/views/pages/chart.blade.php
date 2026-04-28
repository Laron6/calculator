@extends('layouts.app')

@section('title', 'Графики производительности')

@section('content')
<div class="grid-3">
    <div class="card full-width">
        <div class="flex-between">
            <h3><i class="fas fa-chart-line"></i> График производительности труда</h3>
        </div>

        @if($groups->count() > 0)
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
        @endif

        @if($groups->count() == 0)
            <div class="alert alert-info chart-alert">
                <i class="fas fa-info-circle"></i> Группы не созданы. Создайте группу на вкладке "Редактор рабочих"
            </div>
        @elseif(!$selectedGroup)
            <div class="alert alert-info chart-alert">
                <i class="fas fa-info-circle"></i> Выберите группу для отображения графика
            </div>
        @elseif($selectedGroup && $selectedGroup->workers->count() > 0 && count($productivities) > 0)
            <div class="chart-container chart-container-styled">
                <canvas id="productivityChart"></canvas>
            </div>
        @elseif($selectedGroup && $selectedGroup->workers->count() > 0)
            <div class="alert alert-info chart-alert">
                <i class="fas fa-calculator"></i> Сначала выполните расчет производительности на вкладке "Статистика группы"
            </div>
        @elseif($selectedGroup)
            <div class="alert alert-info chart-alert">
                <i class="fas fa-users"></i> В группе нет рабочих
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/charts.js') }}"></script>
<script>
    window.chartLabels = {!! json_encode($labels) !!};
    window.chartProductivities = {!! json_encode($productivities) !!};
</script>
@endpush