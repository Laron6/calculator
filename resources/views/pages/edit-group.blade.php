@extends('layouts.app')

@section('title', 'Редактирование группы')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3><i class="fas fa-edit"></i> Редактирование группы</h3>

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form action="/group/update/{{ $group->id }}" method="POST">
        @csrf
        
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="form-group">
            <label>Название группы</label>
            <input type="text" name="name" value="{{ $group->name }}" required>
        </div>
        
        <div class="flex-between mt-4">
            <a href="/" class="btn btn-outline"><i class="fas fa-times"></i> Отмена</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
        </div>
    </form>
</div>
@endsection