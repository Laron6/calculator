@extends('layouts.app')

@section('title', 'Графики производительности')

@section('content')
<div class="grid-3">
    <div class="card full-width">
        <div class="flex-between">
            <h3><i class="fas fa-chart-line"></i> Графики</h3>
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
            <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                <i class="fas fa-info-circle"></i> Группы не созданы. Создайте группу на вкладке "Редактор рабочих"
            </div>
        @elseif(!$selectedGroup)
            <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                <i class="fas fa-info-circle"></i> Выберите группу для отображения графиков
            </div>
        @elseif($selectedGroup && $selectedGroup->workers->count() > 0 && count($bVec) > 0)
            <div class="chart-container" style="background: rgba(255,255,255,0.05); border-radius: 24px; padding: 24px; margin-top: 24px;">
                <canvas id="productivityChart"></canvas>
            </div>
            <div class="chart-container" style="background: rgba(255,255,255,0.05); border-radius: 24px; padding: 24px; margin-top: 24px;">
                <canvas id="decisionsChart"></canvas>
            </div>
            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="{{ asset('js/charts.js') }}"></script>
            <script>
                const labels = {!! json_encode($labels) !!};
                const bVec = {!! json_encode($bVec) !!};
                const decisions = {!! json_encode($decisions) !!};
                initCharts(labels, bVec, decisions);
            </script>
            @endpush
        @elseif($selectedGroup && $selectedGroup->workers->count() > 0)
            <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                <i class="fas fa-calculator"></i> Сначала выполните расчет производительности на вкладке "Статистика группы"
            </div>
        @elseif($selectedGroup)
            <div class="alert alert-info" style="text-align: center; margin: 40px 0;">
                <i class="fas fa-users"></i> В группе нет рабочих
            </div>
        @endif
    </div>
</div>
@endsection