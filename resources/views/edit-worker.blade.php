<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование рабочего</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="glass-card" style="padding: 30px;">
        <h3 style="margin-bottom: 24px;"><i class="fas fa-user-edit"></i> Редактирование рабочего</h3>
        <form action="/worker/update/{{ $worker->id }}" method="POST">
            @csrf
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
                <input type="number" name="age" value="{{ $worker->age }}" required>
            </div>
            <div class="form-group">
                <label>Стаж</label>
                <input type="number" name="experience" value="{{ $worker->experience }}" required>
            </div>
            <div class="form-group">
                <label>Пол</label>
                <select name="gender">
                    <option value="0" {{ $worker->gender == 0 ? 'selected' : '' }}>Мужской</option>
                    <option value="1" {{ $worker->gender == 1 ? 'selected' : '' }}>Женский</option>
                </select>
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