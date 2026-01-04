<?php ob_start(); ?>

<style>
    /* Premium Dashboard Styles */
    .dashboard-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 0 0 1.5rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px -5px rgba(79, 70, 229, 0.4);
    }

    .kpi-card {
        border: none;
        border-radius: 1rem;
        background: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .kpi-icon-bg {
        position: absolute;
        top: -10px;
        right: -10px;
        opacity: 0.1;
        font-size: 5rem;
        transform: rotate(15deg);
    }

    .card-premium {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
    }

    .card-premium .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }

    .table-premium th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
    }

    .table-premium td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        font-size: 1rem;
    }

    /* Custom Badges */
    .badge-soft-warning {
        background-color: #fff7ed;
        color: #c2410c;
        /* Orange-700 */
        border: 1px solid #ffedd5;
        font-size: 0.95rem;
    }

    .badge-soft-danger {
        background-color: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fee2e2;
        font-size: 0.95rem;
    }

    .badge-soft-success {
        background-color: #f0fdf4;
        color: #15803d;
        border: 1px solid #dcfce7;
        font-size: 0.95rem;
    }

    .badge-soft-primary {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
        font-size: 0.95rem;
    }

    .badge-soft-secondary {
        background-color: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
    }
</style>

<!-- Custom Header Section -->
<div class="content-header p-0">
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1 fw-bold" style="font-size: 2rem;">Selamat Datang, Admin!</h1>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php
                        $days_id = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $now = new DateTime();
                        echo $days_id[$now->format('w')] . ', ' . $now->format('d') . ' ' . $months_id[(int) $now->format('n')] . ' ' . $now->format('Y') . ' - <span id="clock">' . $now->format('H:i') . '</span> WITA';
                        ?>
                    </p>
                </div>
                <!-- <div class="col-md-4 text-right">
                         <button class="btn btn-light shadow-sm text-primary font-weight-bold">
                             <i class="fas fa-download mr-1"></i> Unduh Laporan
                         </button>
                    </div> -->
            </div>
        </div>
    </div>
</div>

<section class="content px-3 mt-n4">
    <div class="container-fluid">
        <!-- KPI Cards Row -->
        <div class="row mb-4" style="margin-top: -3rem;">
            <!-- Total Participants -->
            <div class="col-lg-3 col-6">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Total
                            Pendaftar</span>
                        <h2 class="mb-0 font-weight-bold text-dark"><?php echo number_format($stats['total']); ?></h2>
                        <small class="text-success mt-2">
                            <i class="fas fa-layer-group mr-1"></i> Smt: <?php echo $semesterName; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lulus Berkas -->
            <div class="col-lg-3 col-6">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Lulus
                            Berkas</span>
                        <h2 class="mb-0 font-weight-bold text-success"><?php echo number_format($stats['lulus']); ?>
                        </h2>
                        <small class="text-secondary mt-2">
                            Siap Ujian
                        </small>
                    </div>
                </div>
            </div>

            <!-- Paid -->
            <div class="col-lg-3 col-6">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-info">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Sudah
                            Bayar</span>
                        <h2 class="mb-0 font-weight-bold text-info"><?php echo number_format($stats['paid']); ?></h2>
                        <small class="text-muted mt-2">
                            Lunas Pembayaran
                        </small>
                    </div>
                </div>
            </div>

            <!-- Unpaid -->
            <div class="col-lg-3 col-6">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-warning">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Belum
                            Bayar</span>
                        <h2 class="mb-0 font-weight-bold text-warning"><?php echo number_format($stats['unpaid']); ?>
                        </h2>
                        <small class="text-danger mt-2">
                            <i class="fas fa-info-circle mr-1"></i> Perlu Ditagih
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- S2 Magister Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 d-flex align-items-center text-white"
                        style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); padding: 1.25rem 1.5rem;">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                            <i class="fas fa-graduation-cap mr-2"></i> Statistik Program Magister (S2)
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 2rem;">
                        <!-- Chart S2 -->
                        <div class="chart-container mb-5" style="position: relative; width: 100%;">
                            <canvas id="s2Chart"></canvas>
                        </div>

                        <!-- Search S2 -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-weight-bold text-gray-800 mb-0">Rincian Data Magister</h5>
                            <div class="input-group" style="width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"
                                        style="border-radius: 8px 0 0 8px;">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" id="s2Search" class="form-control border-left-0 bg-light"
                                    placeholder="Cari Program Studi S2..." style="border-radius: 0 8px 8px 0;">
                            </div>
                        </div>

                        <!-- Table S2 -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table" id="s2Table">
                                <thead class="bg-light text-uppercase text-secondary text-xs font-weight-bold">
                                    <tr>
                                        <th class="pl-4 py-3" style="width: 40%;">Program Studi</th>
                                        <th class="text-center py-3">Total</th>
                                        <th class="text-center py-3">Lulus Berkas</th>
                                        <th class="text-center py-3">Sudah Bayar</th>
                                        <th class="text-center py-3">Belum Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($s2Stats)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data pendaftar S2.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($s2Stats as $stat):
                                            $unpaidCalc = isset($stat['unpaid']) ? $stat['unpaid'] : 0;
                                            ?>
                                            <tr class="prodi-row-s2" style="transition: all 0.2s;">
                                                <td class="pl-4 py-3 font-weight-bold text-dark prodi-name">
                                                    <?php echo $stat['nama_prodi']; ?>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-light px-3 py-2 border"><?php echo $stat['total']; ?></span>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-success px-3 py-2 bg-success-light text-success-dark"><?php echo $stat['lulus']; ?></span>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-info px-3 py-2 bg-info-light text-info-dark"><?php echo $stat['paid']; ?></span>
                                                </td>
                                                <td class="text-center py-3">
                                                    <?php if ($unpaidCalc > 0): ?>
                                                        <span
                                                            class="badge badge-danger px-3 py-2 bg-danger-light text-danger-dark"><?php echo $unpaidCalc; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="fas fa-check"></i> Lunas</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- S3 Doktor Section -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 d-flex align-items-center text-white"
                        style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); padding: 1.25rem 1.5rem;">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                            <i class="fas fa-user-graduate mr-2"></i> Statistik Program Doktor (S3)
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 2rem;">
                        <!-- Chart S3 -->
                        <div class="chart-container mb-5" style="position: relative; width: 100%;">
                            <canvas id="s3Chart"></canvas>
                        </div>

                        <!-- Search S3 -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-weight-bold text-gray-800 mb-0">Rincian Data Doktor</h5>
                            <div class="input-group" style="width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"
                                        style="border-radius: 8px 0 0 8px;">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" id="s3Search" class="form-control border-left-0 bg-light"
                                    placeholder="Cari Program Studi S3..." style="border-radius: 0 8px 8px 0;">
                            </div>
                        </div>

                        <!-- Table S3 -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table" id="s3Table">
                                <thead class="bg-light text-uppercase text-secondary text-xs font-weight-bold">
                                    <tr>
                                        <th class="pl-4 py-3" style="width: 40%;">Program Studi</th>
                                        <th class="text-center py-3">Total</th>
                                        <th class="text-center py-3">Lulus Berkas</th>
                                        <th class="text-center py-3">Sudah Bayar</th>
                                        <th class="text-center py-3">Belum Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($s3Stats)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data pendaftar S3.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($s3Stats as $stat):
                                            $unpaidCalc = isset($stat['unpaid']) ? $stat['unpaid'] : 0;
                                            ?>
                                            <tr class="prodi-row-s3" style="transition: all 0.2s;">
                                                <td class="pl-4 py-3 font-weight-bold text-dark prodi-name">
                                                    <?php echo $stat['nama_prodi']; ?>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-light px-3 py-2 border"><?php echo $stat['total']; ?></span>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-success px-3 py-2 bg-success-light text-success-dark"><?php echo $stat['lulus']; ?></span>
                                                </td>
                                                <td class="text-center py-3"><span
                                                        class="badge badge-info px-3 py-2 bg-info-light text-info-dark"><?php echo $stat['paid']; ?></span>
                                                </td>
                                                <td class="text-center py-3">
                                                    <?php if ($unpaidCalc > 0): ?>
                                                        <span
                                                            class="badge badge-danger px-3 py-2 bg-danger-light text-danger-dark"><?php echo $unpaidCalc; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="fas fa-check"></i> Lunas</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>


<script>
    console.log('Dashboard script loaded');
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM Content Loaded - Initializing Chart');

        // Clock
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const clock = document.getElementById('clock');
            if (clock) clock.textContent = `${hours}:${minutes}`;
        }, 1000);

        // Search Function Helper
        const setupSearch = (inputId, rowClass, nameClass) => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('keyup', function () {
                    const value = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll(rowClass);
                    rows.forEach(row => {
                        const text = row.querySelector(nameClass).textContent.toLowerCase();
                        row.style.display = text.includes(value) ? '' : 'none';
                    });
                });
            }
        };

        // Initialize Search for S2 and S3
        setupSearch('s2Search', '.prodi-row-s2', '.prodi-name');
        setupSearch('s3Search', '.prodi-row-s3', '.prodi-name');

        // Chart Configuration Function
        const initProdiChart = (elementId, dataStats, title) => {
            const ctxElement = document.getElementById(elementId);
            if (!ctxElement) return;

            if (dataStats && dataStats.length > 0) {
                // Sort by total for better visualization, showing ALL data
                const sortedData = [...dataStats].sort((a, b) => parseInt(b.total) - parseInt(a.total));

                const labels = sortedData.map(item => item.nama_prodi || 'Unassigned');
                const dataTotal = sortedData.map(item => parseInt(item.total));
                const chartContainer = ctxElement.parentElement;

                // Premium Color Palette
                const colors = [
                    '#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6', '#f97316',
                    '#a855f7', '#06b6d4', '#22c55e', '#eab308', '#f43f5e'
                ];

                // Adaptive Chart Logic
                const dataCount = sortedData.length;
                const isHeavy = dataCount > 12;

                // Dynamic Height
                if (isHeavy) {
                    const newHeight = Math.max(400, dataCount * 30);
                    chartContainer.style.height = `${newHeight}px`;
                } else {
                    chartContainer.style.height = '400px';
                }

                const chartType = isHeavy ? 'bar' : 'doughnut';
                const legendPos = isHeavy ? 'top' : 'right';
                const backgroundColors = isHeavy ? '#4f46e5' : colors.slice(0, dataCount);

                new Chart(ctxElement.getContext('2d'), {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Pendaftar',
                            data: dataTotal,
                            backgroundColor: backgroundColors,
                            borderRadius: isHeavy ? 4 : 0,
                            hoverOffset: isHeavy ? 0 : 15,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: isHeavy ? 'y' : 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: isHeavy ? 0 : '60%',
                        plugins: {
                            legend: {
                                display: true,
                                position: legendPos,
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { family: "'Inter', sans-serif", size: 11 },
                                    generateLabels: isHeavy ? Chart.defaults.plugins.legend.labels.generateLabels : (chart) => {
                                        const datasets = chart.data.datasets;
                                        return chart.data.labels.map((label, i) => ({
                                            text: `${label} (${datasets[0].data[i]})`,
                                            fillStyle: datasets[0].backgroundColor[i],
                                            strokeStyle: datasets[0].backgroundColor[i],
                                            pointStyle: 'circle',
                                            hidden: !chart.getDataVisibility(i),
                                            index: i
                                        }));
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                padding: 14,
                                cornerRadius: 8,
                                titleFont: { size: 13, family: "'Inter', sans-serif" },
                                bodyFont: { size: 13, family: "'Inter', sans-serif" },
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1) + '%';
                                        return `${context.label}: ${value} (${percentage})`;
                                    }
                                }
                            }
                        },
                        scales: isHeavy ? {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: { grid: { display: false }, ticks: { autoSkip: false } }
                        } : { x: { display: false }, y: { display: false } }, // Hide scales for Doughnut
                        layout: { padding: { top: 20, bottom: 20 } },
                        interaction: { mode: 'index', intersect: false },
                    }
                });
            } else {
                ctxElement.parentElement.innerHTML =
                    '<div class="d-flex justify-content-center align-items-center h-100 text-muted">Belum ada data ' + title + ' untuk ditampilkan.</div>';
            }
        };

        // Initialize Charts
        const s2Data = <?php echo json_encode($s2Stats ?? []); ?>;
        const s3Data = <?php echo json_encode($s3Stats ?? []); ?>;

        initProdiChart('s2Chart', s2Data, 'Magister (S2)');
        initProdiChart('s3Chart', s3Data, 'Doktor (S3)');
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>