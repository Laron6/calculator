<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование группы</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="glass-card" style="padding: 30px;">
        <h3 style="margin-bottom: 24px;"><i class="fas fa-edit"></i> Редактирование группы</h3>

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
</div>

@include('components.theme-toggle')

<script src="{{ asset('js/theme.js') }}"></script>
</body>
</html>