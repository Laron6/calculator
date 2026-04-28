@extends('layouts.app')

@section('title', 'Редактирование рабочего')

@section('content')
<div class="card edit-card">
    <h3><i class="fas fa-user-edit"></i> Редактирование рабочего</h3>

    @if(session('error'))
        <div class="alert alert-danger edit-alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success edit-alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form action="/worker/update/{{ $worker->id }}" method="POST">
        @csrf
        
        @if ($errors->any())
            <div class="alert alert-danger edit-error-alert">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="form-group">
            <label>Фамилия</label>
            <input type="text" name="last_name" value="{{ $worker->last_name }}" required>
        </div>
        
        <div class="form-group">
            <label>Имя</label>
            <input type="text" name="first_name" value="{{ $worker->first_name }}" required>
        </div>
        
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" name="patronymic" value="{{ $worker->patronymic }}">
        </div>
        
        <div class="form-group">
            <label>Возраст</label>
            <div class="number-input-wrapper">
                <input type="number" name="age" value="{{ $worker->age }}" class="number-input" required id="ageInput" min="18" max="100">
                <div class="number-controls">
                    <button type="button" class="number-btn" onclick="changeValue('ageInput', -1, 18, 100)">−</button>
                    <button type="button" class="number-btn" onclick="changeValue('ageInput', 1, 18, 100)">+</button>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Стаж</label>
            <div class="number-input-wrapper">
                <input type="number" name="experience" value="{{ $worker->experience }}" class="number-input" required id="expInput" min="0" max="80">
                <div class="number-controls">
                    <button type="button" class="number-btn" onclick="changeValue('expInput', -1, 0, 80)">−</button>
                    <button type="button" class="number-btn" onclick="changeValue('expInput', 1, 0, 80)">+</button>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Пол</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="gender" value="0" {{ $worker->gender == 0 ? 'checked' : '' }}>
                    <span><i class="fas fa-mars"></i> Мужской</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="1" {{ $worker->gender == 1 ? 'checked' : '' }}>
                    <span><i class="fas fa-venus"></i> Женский</span>
                </label>
            </div>
        </div>
        
        <div class="flex-between mt-4">
            <a href="/" class="btn btn-outline"><i class="fas fa-times"></i> Отмена</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
        </div>
    </form>
</div>
@endsection