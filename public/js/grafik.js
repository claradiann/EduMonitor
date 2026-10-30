// Grafik EduMonitor (Chart.js 4). Satu tempat untuk gaya semua grafik supaya seragam.
window.Grafik = (function () {
    const W = {
        seri: '#4652a8',      // nilai anak / satu seri
        seriMuda: '#c6cbe8',  // nilai sementara
        kelas: '#c06a2c',     // rata-rata kelas (pembanding)
        tepat: '#4652a8',
        terlambat: '#c6372a',
        berjalan: '#dc9a5e',
        garis: '#eceef4',
        teks: '#5d6280',
        tinta: '#161a33',
    };
    const angka = (v) => (v === null || v === undefined) ? '-' : Number(v).toLocaleString('id-ID', { maximumFractionDigits: 1 });

    if (window.Chart) {
        Chart.defaults.font.family = 'Figtree, system-ui, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = W.teks;
        Chart.defaults.borderColor = W.garis;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.animation.duration = 700;
        Chart.defaults.animation.easing = 'easeOutQuart';
        Chart.defaults.plugins.legend.display = false;
        Object.assign(Chart.defaults.plugins.tooltip, {
            backgroundColor: '#11142e', padding: 10, cornerRadius: 8, boxPadding: 5,
            titleFont: { weight: '700', size: 13 }, bodyFont: { size: 12.5 }, usePointStyle: true,
        });
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) Chart.defaults.animation = false;
    }

    // Menulis angka di ujung batang (label langsung, tidak di setiap titik garis)
    const labelUjung = {
        id: 'labelUjung',
        afterDatasetsDraw(chart, _a, opsi) {
            if (!opsi || !opsi.aktif) return;
            const { ctx } = chart;
            const horizontal = chart.options.indexAxis === 'y';
            ctx.save();
            ctx.font = '700 12px Figtree, system-ui, sans-serif';
            ctx.fillStyle = W.tinta;
            chart.getDatasetMeta(0).data.forEach((bar, i) => {
                const v = chart.data.datasets[0].data[i];
                if (v === null || v === undefined) return;
                const teks = angka(v) + ((opsi.akhiran && opsi.akhiran[i]) || '');
                if (horizontal) { ctx.textAlign = 'left'; ctx.textBaseline = 'middle'; ctx.fillText(teks, bar.x + 6, bar.y); }
                else { ctx.textAlign = 'center'; ctx.textBaseline = 'bottom'; ctx.fillText(teks, bar.x, bar.y - 4); }
            });
            ctx.restore();
        },
    };

    const sumbu = (extra) => Object.assign({
        grid: { color: W.garis, drawTicks: false },
        border: { display: false },
        ticks: { padding: 8 },
    }, extra || {});

    // Perkembangan nilai: garis nilai anak + garis putus-putus rata-rata kelas
    function tren(canvas, data) {
        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.map((d) => d.label),
                datasets: [
                    {
                        label: 'Nilai', data: data.map((d) => d.nilai), borderColor: W.seri, backgroundColor: W.seri,
                        borderWidth: 2.5, pointRadius: 4.5, pointHoverRadius: 7, pointBorderColor: '#fff', pointBorderWidth: 2, tension: 0.3,
                        fill: { target: 'origin', above: 'rgba(70,82,168,.08)' }
                    },
                    {
                        label: 'Rata-rata kelas', data: data.map((d) => d.kelas), borderColor: W.kelas, backgroundColor: W.kelas,
                        borderWidth: 2, borderDash: [5, 4], pointRadius: 3, pointHoverRadius: 6, tension: 0.3
                    },
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: sumbu({ grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, autoSkipPadding: 12 } }),
                    y: sumbu({ suggestedMin: 50, max: 100, ticks: { stepSize: 10, padding: 8 } }),
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: (it) => data[it[0].dataIndex].label + ' · ' + data[it[0].dataIndex].tanggal,
                            label: (it) => ' ' + it.dataset.label + ': ' + angka(it.parsed.y),
                        }
                    },
                },
            },
        });
    }

    // Nilai akhir per mapel: batang mendatar dari nol, nilai sementara lebih muda
    function perMapel(canvas, data) {
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.map((d) => d.mapel),
                datasets: [{
                    data: data.map((d) => d.nilai),
                    backgroundColor: data.map((d) => d.sementara ? W.seriMuda : W.seri),
                    borderRadius: 4, borderSkipped: 'start', barThickness: 16
                }],
            },
            options: {
                indexAxis: 'y',
                layout: { padding: { right: 56 } },
                scales: {
                    x: sumbu({ min: 0, max: 100, ticks: { stepSize: 25 } }),
                    y: sumbu({ grid: { display: false }, ticks: { color: W.tinta, font: { weight: '600', size: 12.5 } } }),
                },
                plugins: {
                    labelUjung: { aktif: true, akhiran: data.map((d) => d.sementara ? ' (sementara)' : '') },
                    tooltip: { callbacks: { label: (it) => ' Nilai akhir: ' + angka(it.parsed.x) + (data[it.dataIndex].sementara ? ' (sementara, UTS/UAS belum lengkap)' : '') } },
                },
            },
            plugins: [labelUjung],
        });
    }

    // Guru: rata-rata kelas untuk tiap penilaian
    function rataPenilaian(canvas, data) {
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.map((d) => d.label),
                datasets: [{
                    data: data.map((d) => d.rata), backgroundColor: W.seri, hoverBackgroundColor: '#2f3878',
                    borderRadius: 4, borderSkipped: 'start', maxBarThickness: 34
                }],
            },
            options: {
                layout: { padding: { top: 20 } },
                scales: {
                    x: sumbu({ grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } }),
                    y: sumbu({ min: 0, max: 100, ticks: { stepSize: 25 } }),
                },
                plugins: {
                    labelUjung: { aktif: true },
                    tooltip: { callbacks: { label: (it) => ' Rata-rata kelas: ' + angka(it.parsed.y) } },
                },
            },
            plugins: [labelUjung],
        });
    }

    // Admin: ketepatan unggah nilai per guru (batang bertumpuk mendatar)
    function ketepatan(canvas, data) {
        const seri = [
            ['Tepat waktu', 'tepat', W.tepat],
            ['Terlambat', 'terlambat', W.terlambat],
            ['Masih dalam masa input', 'berjalan', W.berjalan],
        ];
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.map((d) => d.guru),
                datasets: seri.map(([label, kunci, warna]) => ({
                    label, data: data.map((d) => d[kunci]), backgroundColor: warna,
                    borderColor: '#fff', borderWidth: { right: 2 }, borderSkipped: false, barThickness: 18,
                })),
            },
            options: {
                indexAxis: 'y',
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: sumbu({ stacked: true, min: 0, ticks: { stepSize: 1, precision: 0 } }),
                    y: sumbu({ stacked: true, grid: { display: false }, ticks: { color: W.tinta, font: { weight: '600', size: 12.5 } } }),
                },
                plugins: { tooltip: { callbacks: { label: (it) => ' ' + it.dataset.label + ': ' + it.parsed.x + ' penilaian' } } },
            },
        });
    }

    return { tren, perMapel, rataPenilaian, ketepatan, warna: W, angka };
})();