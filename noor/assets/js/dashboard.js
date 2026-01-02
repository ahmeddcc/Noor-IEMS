document.addEventListener('DOMContentLoaded', function () {
    // Check if chartData is defined globally
    if (typeof chartData === 'undefined') return;

    const canvas = document.getElementById('weeklyChart');
    if (canvas) {
        const ctx2d = canvas.getContext('2d');

        // Create GLOSSY GLASS GRADIENTS
        const cyanGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
        cyanGradient.addColorStop(0, 'rgba(6, 182, 212, 0.4)');
        cyanGradient.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        const greenGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
        greenGradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); /* Emerald */
        greenGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const redGradient = ctx2d.createLinearGradient(0, 0, 0, 400);
        redGradient.addColorStop(0, 'rgba(244, 63, 94, 0.4)'); /* Rose */
        redGradient.addColorStop(1, 'rgba(244, 63, 94, 0.0)');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'الوارد',
                        data: chartData.income,
                        borderColor: '#10b981', /* Emerald */
                        backgroundColor: greenGradient,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'المنصرف',
                        data: chartData.expense,
                        borderColor: '#f43f5e', /* Rose */
                        backgroundColor: redGradient,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'الصافي',
                        data: chartData.balance,
                        borderColor: '#06b6d4', /* Cyan Neon */
                        backgroundColor: cyanGradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#06b6d4',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#06b6d4',
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointShadowColor: 'rgba(6, 182, 212, 0.5)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                locale: 'ar-EG',
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Cairo' },
                            usePointStyle: true,
                            color: '#e2e8f0'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(56, 189, 248, 0.1)' },
                        ticks: {
                            color: '#94a3b8',
                            callback: function (value) { return value.toLocaleString('ar-EG') + ' ج.م'; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    }
});

// Force layout recalculation on resize for stat cards
(function () {
    let resizeTimeout;
    const statCards = document.querySelector('.dashboard-stats-compact');

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            if (statCards) {
                // Force reflow by reading a computed property
                statCards.style.display = 'none';
                statCards.offsetHeight; // Trigger reflow
                statCards.style.display = '';

                // Apply correct grid based on width
                if (window.innerWidth <= 600) {
                    statCards.style.gridTemplateColumns = '1fr';
                } else if (window.innerWidth <= 900) {
                    statCards.style.gridTemplateColumns = 'repeat(2, 1fr)';
                } else {
                    statCards.style.gridTemplateColumns = '';
                }
            }
        }, 100);
    });

    // Also run on load
    window.dispatchEvent(new Event('resize'));
})();
