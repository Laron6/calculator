<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование рабочего</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="glass-card" style="padding: 30px;">
        <h3 style="margin-bottom: 24px; color: white;"><i class="fas fa-user-edit"></i> Редактирование рабочего</h3>

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

        <form action="/worker/update/{{ $worker->id }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Фамилия</label>
                <input type="text" name="last_name" value="{{ $worker->last_name }}" required style="color: white;">
            </div>
            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="first_name" value="{{ $worker->first_name }}" required style="color: white;">
            </div>
            <div class="form-group">
                <label>Отчество</label>
                <input type="text" name="patronymic" value="{{ $worker->patronymic }}" style="color: white;">
            </div>
            <div class="form-group">
                <label>Возраст</label>
                <div class="number-input-wrapper">
                    <input type="number" name="age" value="{{ $worker->age }}" class="number-input" required id="ageInput" min="18" max="100" style="color: white;">
                    <div class="number-controls">
                        <button type="button" class="number-btn" onclick="changeValue('ageInput', -1, 18, 100)">−</button>
                        <button type="button" class="number-btn" onclick="changeValue('ageInput', 1, 18, 100)">+</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Стаж</label>
                <div class="number-input-wrapper">
                    <input type="number" name="experience" value="{{ $worker->experience }}" class="number-input" required id="expInput" min="0" max="80" style="color: white;">
                    <div class="number-controls">
                        <button type="button" class="number-btn" onclick="changeValue('expInput', -1, 0, 80)">−</button>
                        <button type="button" class="number-btn" onclick="changeValue('expInput', 1, 0, 80)">+</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Пол</label>
                <div style="display: flex; gap: 12px; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                        <input type="radio" name="gender" value="0" style="width: 18px; height: 18px; margin: 0;" {{ $worker->gender == 0 ? 'checked' : '' }}>
                        <span style="color: white;"><i class="fas fa-mars"></i> Мужской</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                        <input type="radio" name="gender" value="1" style="width: 18px; height: 18px; margin: 0;" {{ $worker->gender == 1 ? 'checked' : '' }}>
                        <span style="color: white;"><i class="fas fa-venus"></i> Женский</span>
                    </label>
                </div>
            </div>
            <div class="flex-between mt-4">
                <a href="/" class="btn btn-outline"><i class="fas fa-times"></i> Отмена</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    function changeValue(inputId, delta, minVal, maxVal) {
        let input = document.getElementById(inputId);
        let currentValue = parseInt(input.value);
        
        if (isNaN(currentValue) || currentValue === '') {
            currentValue = minVal;
            input.value = minVal;
        }
        
        let newValue = currentValue + delta;
        
        if (newValue >= minVal && newValue <= maxVal) {
            input.value = newValue;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
</script>

</body>
</html>