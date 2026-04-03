<div class="card">
    <h3><i class="fas fa-user-plus"></i> Добавить рабочего</h3>
    <form action="/worker/add" method="POST">
        @csrf
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
            <div style="display: flex; gap: 12px; margin-top: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                    <input type="radio" name="gender" value="0" style="width: 18px; height: 18px; margin: 0;" checked>
                    <span><i class="fas fa-mars"></i> Мужской</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 40px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                    <input type="radio" name="gender" value="1" style="width: 18px; height: 18px; margin: 0;">
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
        <h3 style="font-size: 16px;"><i class="fas fa-file-import"></i> Импорт/Экспорт</h3>
    </div>
    <form action="/workers/import" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="fileInput" class="file-upload-label">
                <i class="fas fa-folder-open"></i> Выберите файл (.lst)
            </label>
            <input type="file" id="fileInput" name="file" accept=".lst,.txt" style="display: none;">
            <span id="fileName" class="file-name">Файл не выбран</span>
        </div>
        <button type="submit" class="btn btn-primary w-full" id="submitBtn" disabled style="opacity: 0.5;">
            <i class="fas fa-upload"></i> Загрузить список
        </button>
    </form>
    <a href="/workers/export" class="btn btn-success w-full" style="margin-top: 12px; text-align: center;">
        <i class="fas fa-download"></i> Сохранить список (.lst)
    </a>
</div>