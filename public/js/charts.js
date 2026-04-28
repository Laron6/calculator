// Инициализация графика производительности
let productivityChartInstance = null;

function initChart(labels, productivities) {
    const ctx = document.getElementById('productivityChart');
    if (!ctx) return;
    
    if (productivityChartInstance) {
        productivityChartInstance.destroy();
    }
    
    const isMobile = window.innerWidth < 768;
    
    productivityChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Производительность труда (шт/ч)',
                data: productivities,
                borderColor: '#43e97b',
                backgroundColor: 'rgba(67,233,123,0.1)',
                borderWidth: 2,
                pointRadius: isMobile ? 3 : 5,
                pointBackgroundColor: '#43e97b',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#333',
                        font: { size: isMobile ? 10 : 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(2) + ' шт/ч';
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Производительность (шт/ч)',
                        color: '#333'
                    },
                    grid: { color: 'rgba(0,0,0,0.1)' },
                    ticks: { 
                        color: '#333', 
                        font: { size: isMobile ? 9 : 11 },
                        callback: function(value) {
                            return value.toFixed(0);
                        }
                    },
                    beginAtZero: true
                },
                x: {
                    title: {
                        display: true,
                        text: 'Рабочие',
                        color: '#333'
                    },
                    grid: { color: 'rgba(0,0,0,0.1)' },
                    ticks: { 
                        color: '#333', 
                        font: { size: isMobile ? 8 : 11 },
                        rotation: isMobile ? 45 : 0,
                        maxRotation: 45,
                        minRotation: isMobile ? 45 : 0
                    }
                }
            }
        }
    });
}

// Автоматическая инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('productivityChart');
    
    if (canvas) {
        // Данные передаются через глобальные переменные из шаблона
        if (window.chartLabels && window.chartProductivities) {
            initChart(window.chartLabels, window.chartProductivities);
        }
    }
});