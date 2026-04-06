<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

@include('components.theme-toggle')

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/theme.js') }}"></script>
</body>
</html>