// Инициализация графиков производительности
function initCharts(labels, bVec, decisions) {
    const ctx1 = document.getElementById('productivityChart');
    const ctx2 = document.getElementById('decisionsChart');
    const isMobile = window.innerWidth < 768;
    
    // Общие настройки для обоих графиков
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                labels: {
                    color: '#333',
                    font: { size: 12 }
                }
            }
        },
        scales: {
            y: {
                grid: { color: 'rgba(0,0,0,0.1)' },
                ticks: { color: '#333', font: { size: 11 } }
            },
            x: {
                grid: { color: 'rgba(0,0,0,0.1)' },
                ticks: { 
                    color: '#333', 
                    font: { size: 11 },
                    rotation: 0
                }
            }
        }
    };
    
    // Мобильные настройки (только если телефон)
    if (isMobile) {
        baseOptions.plugins.legend.labels.font.size = 9;
        baseOptions.scales.y.ticks.font.size = 8;
        baseOptions.scales.x.ticks.font.size = 7;
        baseOptions.scales.x.ticks.rotation = 45;
        baseOptions.scales.x.ticks.maxRotation = 45;
        baseOptions.scales.x.ticks.minRotation = 45;
    }
    
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Продуктивность группы без рабочего',
                    data: bVec,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102,126,234,0.1)',
                    borderWidth: 2,
                    pointRadius: isMobile ? 2 : 4,
                    pointBackgroundColor: '#667eea',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: baseOptions
        });
    }
    
    if (ctx2) {
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Продуктивность рабочего',
                    data: decisions,
                    borderColor: '#43e97b',
                    backgroundColor: 'rgba(67,233,123,0.1)',
                    borderWidth: 2,
                    pointRadius: isMobile ? 2 : 4,
                    pointBackgroundColor: '#43e97b',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: baseOptions
        });
    }
}