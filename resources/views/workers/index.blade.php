@extends('layouts.app')

@section('title', 'Редактор рабочих')

@section('content')
<div class="grid-3">
    @include('components.worker-card')
    
    <div class="card">
        <h3><i class="fas fa-list"></i> Все рабочие <span class="badge">{{ $workers->count() }}</span></h3>
        <div class="worker-list">
            @foreach($workers as $worker)
            <div class="worker-item">
                <span class="worker-name">{{ $worker->last_name }} {{ $worker->first_name }}</span>
                <div class="worker-actions">
                    <a href="/worker/edit/{{ $worker->id }}" class="btn-link" title="Редактировать"><i class="fas fa-pen"></i></a>
                    <form action="/worker/delete/{{ $worker->id }}" method="POST" class="inline-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-link" onclick="return confirm('Удалить рабочего?')" title="Удалить">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="flex-between workers-form-container">
            <h3><i class="fas fa-layer-group"></i> Рабочие группы</h3>
            <form action="/group/create" method="POST" class="workers-create-form">
                @csrf
                <input type="text" name="name" placeholder="Название группы" class="workers-create-input">
                <button type="submit" class="btn btn-primary workers-create-btn">
                    <i class="fas fa-plus"></i> Создать
                </button>
            </form>
        </div>
        <div class="groups-list workers-groups-list">
            @forelse($groups as $group)
            <div class="group-card {{ $selectedGroupId == $group->id ? 'selected' : '' }}">
                <a href="?group_id={{ $group->id }}" class="group-link">
                    <i class="fas fa-folder"></i> {{ $group->name }}
                    <span class="group-count">({{ $group->workers->count() }})</span>
                </a>
                <div class="group-actions">
                    <a href="/group/edit/{{ $group->id }}" class="btn-link" title="Редактировать"><i class="fas fa-pen"></i></a>
                    <form action="/group/delete/{{ $group->id }}" method="POST" class="inline-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-link" onclick="return confirm('Удалить группу?')" title="Удалить">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="alert alert-info workers-empty-alert">
                <i class="fas fa-info-circle"></i> Группы не созданы. Создайте первую группу
            </div>
            @endforelse
        </div>
    </div>
</div>

@if($selectedGroup)
<div class="card mt-4 group-management">
    <h3><i class="fas fa-people-arrows"></i> Управление группой: {{ $selectedGroup->name }}</h3>
    <div class="grid-2">
        <div>
            <h4><i class="fas fa-user-plus"></i> Доступные рабочие</h4>
            <div class="worker-list compact">
                @foreach($workers as $worker)
                    @if(!$selectedGroup->workers->contains($worker))
                    <div class="worker-item">
                        <span class="worker-name">{{ $worker->last_name }} {{ $worker->first_name }}</span>
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
                    <span class="worker-name">{{ $worker->last_name }} {{ $worker->first_name }}</span>
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
@endsection