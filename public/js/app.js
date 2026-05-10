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

// Обработка выбора файла для импорта
document.getElementById('fileInput')?.addEventListener('change', function(e) {
    var fileName = e.target.files[0]?.name || 'Файл не выбран';
    document.getElementById('fileName').innerText = fileName;
    document.getElementById('submitBtn').disabled = !e.target.files[0];
    document.getElementById('submitBtn').style.opacity = e.target.files[0] ? '1' : '0.5';
});