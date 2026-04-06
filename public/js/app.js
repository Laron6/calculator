// Функция для кнопок увеличения/уменьшения возраста и стажа
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

// Валидация формы редактирования рабочего
document.querySelector('form[action^="/worker/update/"]')?.addEventListener('submit', function(e) {
    let lastName = document.querySelector('input[name="last_name"]').value.trim();
    let firstName = document.querySelector('input[name="first_name"]').value.trim();
    let age = parseInt(document.querySelector('input[name="age"]').value);
    let experience = parseInt(document.querySelector('input[name="experience"]').value);
    let errors = [];
    
    let nameRegex = /^[а-яА-ЯёЁ-]+$/;
    if (!nameRegex.test(lastName)) {
        errors.push('Фамилия может содержать только русские буквы и дефис');
    }
    if (!nameRegex.test(firstName)) {
        errors.push('Имя может содержать только русские буквы и дефис');
    }
    
    if (age < 18) {
        errors.push('Возраст должен быть не менее 18 лет');
    }
    if (age > 100) {
        errors.push('Возраст не должен превышать 100 лет');
    }
    
    if (experience < 0) {
        errors.push('Стаж не может быть отрицательным');
    }
    if (experience > 80) {
        errors.push('Стаж не должен превышать 80 лет');
    }
    if (experience > (age - 18)) {
        errors.push('Стаж не может быть больше возраста минус 18 лет');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
        return false;
    }
});

// Обработка выбора файла для импорта
document.getElementById('fileInput')?.addEventListener('change', function(e) {
    var fileName = e.target.files[0]?.name || 'Файл не выбран';
    document.getElementById('fileName').innerText = fileName;
    document.getElementById('submitBtn').disabled = !e.target.files[0];
    document.getElementById('submitBtn').style.opacity = e.target.files[0] ? '1' : '0.5';
});

// Клик по кнопке выбора файла
document.querySelector('.file-upload-label')?.addEventListener('click', function() {
    document.getElementById('fileInput').click();
});