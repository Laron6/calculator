// Переключение между темной и светлой темой
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    const icon = document.querySelector('.theme-toggle i');
    if (newTheme === 'dark') {
        icon.className = 'fas fa-moon';
    } else {
        icon.className = 'fas fa-sun';
    }
}

// Загрузка сохраненной темы при старте
const savedTheme = localStorage.getItem('theme') || 'dark';
document.body.setAttribute('data-theme', savedTheme);
const icon = document.querySelector('.theme-toggle i');
if (savedTheme === 'light') {
    icon.className = 'fas fa-sun';
} else {
    icon.className = 'fas fa-moon';
}