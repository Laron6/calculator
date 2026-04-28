<div class="card">
    <h3><i class="fas fa-user-plus"></i> Добавить рабочего</h3>
    <form action="/worker/add" method="POST">
        @csrf
        
        @if ($errors->any())
            <div class="alert alert-danger worker-card-alert">
                <ul class="worker-error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="form-group">
            <label>Фамилия</label>
            <input type="text" name="last_name" placeholder="Иванов" required>
        </div>
        <div class="form-group">
            <label>Имя</label>
            <input type="text" name="first_name" placeholder="Иван" required>
        </div>
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" name="patronymic" placeholder="Иванович">
        </div>
        <div class="form-group">
            <label>Возраст</label>
            <div class="number-input-wrapper">
                <input type="number" name="age" placeholder="лет" class="number-input" required id="ageInput" min="18" max="100" value="18">
                <div class="number-controls">
                    <button type="button" class="number-btn" onclick="changeValue('ageInput', -1, 18, 100)">−</button>
                    <button type="button" class="number-btn" onclick="changeValue('ageInput', 1, 18, 100)">+</button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Стаж</label>
            <div class="number-input-wrapper">
                <input type="number" name="experience" placeholder="лет" class="number-input" required id="expInput" min="0" max="80" value="0">
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
                    <input type="radio" name="gender" value="0" checked>
                    <span><i class="fas fa-mars"></i> Мужской</span>
                </label>
                <label class="radio-label">
                    <input type="radio" name="gender" value="1">
                    <span><i class="fas fa-venus"></i> Женский</span>
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-full">
            <i class="fas fa-plus"></i> Добавить рабочего
        </button>
    </form>

    <hr>

    <div class="flex-between">
        <h3 class="import-title"><i class="fas fa-file-import"></i> Импорт/Экспорт</h3>
    </div>
    <form action="/workers/import" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        <div class="import-file-wrapper">
            <label for="fileInput" class="file-upload-label">
                <i class="fas fa-folder-open"></i> Выберите файл (.lst)
            </label>
            <input type="file" id="fileInput" name="file" accept=".lst,.txt">
            <span id="fileName" class="file-name">Файл не выбран</span>
        </div>
        <button type="submit" class="btn btn-primary w-full import-submit-btn" id="submitBtn" disabled>
            <i class="fas fa-upload"></i> Загрузить список
        </button>
    </form>
    <a href="/workers/export" class="btn btn-success w-full export-link">
        <i class="fas fa-download"></i> Сохранить список (.lst)
    </a>
</div>