function initNilaiMapelChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nilai Rata-rata',
                data: data,
                backgroundColor: '#1e293b',
                borderRadius: 8,
                barThickness: 16,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        font: {
                            family: 'Outfit',
                            weight: '500'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Outfit',
                            weight: '700'
                        }
                    }
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById('chartNilaiMapel');
    if (canvas && typeof chartNilaiMapelData !== 'undefined') {
        initNilaiMapelChart('chartNilaiMapel', chartNilaiMapelData.labels, chartNilaiMapelData.data);
    }
});