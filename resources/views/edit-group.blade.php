<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование группы</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="glass-card" style="padding: 30px;">
        <h3 style="margin-bottom: 24px; color: white;"><i class="fas fa-edit"></i> Редактирование группы</h3>
        <form action="/group/update/{{ $group->id }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Название группы</label>
                <input type="text" name="name" value="{{ $group->name }}" required style="color: white; background: rgba(255,255,255,0.08);">
            </div>
            <div class="flex-between mt-4">
                <a href="/" class="btn btn-outline"><i class="fas fa-times"></i> Отмена</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>