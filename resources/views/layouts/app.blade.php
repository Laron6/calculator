<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Объективная оценка производительности</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="container">
    <div class="glass-card">
        @include('components.header')
        
        @include('components.alerts')
        
        @include('components.tabs')
        
        <div class="tab-content {{ $activeTab == 'workers' ? 'active' : '' }}">
            @include('workers.index')
        </div>

        <div class="tab-content {{ $activeTab == 'statistics' ? 'active' : '' }}">
            @include('statistics.index')
        </div>

        @include('components.footer')
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
    
    document.getElementById('fileInput')?.addEventListener('change', function(e) {
        var fileName = e.target.files[0]?.name || 'Файл не выбран';
        document.getElementById('fileName').innerText = fileName;
        document.getElementById('submitBtn').disabled = !e.target.files[0];
        document.getElementById('submitBtn').style.opacity = e.target.files[0] ? '1' : '0.5';
    });
    
    document.querySelector('.file-upload-label')?.addEventListener('click', function() {
        document.getElementById('fileInput').click();
    });
</script>
</body>
</html>