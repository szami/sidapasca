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

    .quick-action-card {
        border: 2px solid transparent;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
    }

    .quick-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .quick-action-card.indigo {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-color: #c7d2fe;
    }

    .quick-action-card.indigo:hover {
        border-color: #6366f1;
    }

    .quick-action-card.blue {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #bfdbfe;
    }

    .quick-action-card.blue:hover {
        border-color: #3b82f6;
    }

    .quick-action-card.green {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: #bbf7d0;
    }

    .quick-action-card.green:hover {
        border-color: #22c55e;
    }

    .quick-action-card.purple {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border-color: #e9d5ff;
    }

    .quick-action-card.purple:hover {
        border-color: #a855f7;
    }

    .quick-action-card.gray {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-color: #e5e7eb;
    }

    .quick-action-card.gray:hover {
        border-color: #6b7280;
    }

    .welcome-card {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 1.5rem;
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .stat-mini {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        padding: 1rem;
        backdrop-filter: blur(10px);
    }

    .recent-item {
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }

    .recent-item:hover {
        background: #f8fafc;
        border-left-color: #6366f1;
    }

    .schedule-card {
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        border-radius: 1rem;
        padding: 1.25rem;
        color: white;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .role-badge.superadmin {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }

    .role-badge.admin {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    }

    .role-badge.upkh {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .role-badge.tu {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
    }

    .role-badge.admin_prodi {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    }
</style>

<?php
// Get role-specific greeting
$greetings = [
    'superadmin' => 'Selamat datang, Super Administrator!',
    'admin' => 'Selamat datang, Administrator!',
    'upkh' => 'Selamat datang, Tim UPKH!',
    'tu' => 'Selamat datang, Tata Usaha!',
    'admin_prodi' => 'Selamat datang, Admin Program Studi!'
];
$greeting = $greetings[$role ?? 'admin'] ?? 'Selamat datang!';

$roleDescriptions = [
    'superadmin' => 'Akses penuh ke seluruh sistem',
    'admin' => 'Kelola data peserta dan import/export',
    'upkh' => 'Verifikasi berkas dan dokumen peserta',
    'tu' => 'Penjadwalan dan operasional ujian',
    'admin_prodi' => 'Monitoring data program studi Anda'
];
$roleDesc = $roleDescriptions[$role ?? 'admin'] ?? '';
?>

<section class="content px-3 py-4">
    <div class="container-fluid">
        <!-- Welcome Card -->
        <div class="welcome-card mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="role-badge <?= $role ?>">
                            <i class="fas fa-shield-alt"></i>
                            <?= $roleDisplayName ?>
                        </div>
                    </div>
                    <h1 class="mb-2" style="font-size: 1.75rem; font-weight: 700;"><?= $greeting ?></h1>
                    <p class="text-white-50 mb-0"><?= $roleDesc ?></p>
                    <p class="text-white-50 mb-0 mt-2">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php
                        $days_id = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $now = new DateTime();
                        echo $days_id[$now->format('w')] . ', ' . $now->format('d') . ' ' . $months_id[(int) $now->format('n')] . ' ' . $now->format('Y');
                        ?>
                    </p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-mini text-center">
                                <div class="h3 mb-1 font-weight-bold"><?= number_format($stats['total'] ?? 0) ?></div>
                                <div class="small text-white-50">Total Peserta</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini text-center">
                                <div class="h3 mb-1 font-weight-bold"><?= number_format($stats['lulus'] ?? 0) ?></div>
                                <div class="small text-white-50">Lulus Berkas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if (!empty($quickActions)): ?>
            <div class="mb-4">
                <h5 class="font-weight-bold text-gray-800 mb-3">
                    <i class="fas fa-bolt text-warning mr-2"></i> Aksi Cepat
                </h5>
                <div class="row g-3">
                    <?php foreach ($quickActions as $action): ?>
                        <div class="col-6 col-md-3">
                            <a href="<?= $action['url'] ?>" class="quick-action-card <?= $action['color'] ?>">
                                <div class="mb-2">
                                    <?php
                                    $icons = [
                                        'users' => '<i class="fas fa-users fa-2x text-indigo-600"></i>',
                                        'upload' => '<i class="fas fa-cloud-upload-alt fa-2x text-blue-600"></i>',
                                        'cog' => '<i class="fas fa-cog fa-2x text-gray-600"></i>',
                                        'chart' => '<i class="fas fa-chart-pie fa-2x text-green-600"></i>',
                                        'check' => '<i class="fas fa-check-circle fa-2x text-green-600"></i>',
                                        'mail' => '<i class="fas fa-envelope fa-2x text-purple-600"></i>',
                                        'calendar' => '<i class="fas fa-calendar-alt fa-2x text-blue-600"></i>',
                                        'clipboard' => '<i class="fas fa-clipboard-list fa-2x text-green-600"></i>',
                                        'printer' => '<i class="fas fa-print fa-2x text-purple-600"></i>',
                                        'document' => '<i class="fas fa-file-alt fa-2x text-blue-600"></i>',
                                    ];
                                    echo $icons[$action['icon']] ?? '<i class="fas fa-star fa-2x"></i>';
                                    ?>
                                </div>
                                <div class="font-weight-semibold text-gray-800" style="font-size: 0.875rem;">
                                    <?= $action['label'] ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- KPI Cards Row -->
        <div class="row mb-4">
            <!-- Total Participants -->
            <div class="col-lg-3 col-6 mb-3">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Total
                            Pendaftar</span>
                        <h2 class="mb-0 font-weight-bold text-dark"><?= number_format($stats['total'] ?? 0) ?></h2>
                        <small class="text-success mt-2">
                            <i class="fas fa-layer-group mr-1"></i> <?= $semesterName ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lulus Berkas -->
            <div class="col-lg-3 col-6 mb-3">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Lulus
                            Berkas</span>
                        <h2 class="mb-0 font-weight-bold text-success"><?= number_format($stats['lulus'] ?? 0) ?></h2>
                        <small class="text-secondary mt-2">Siap Ujian</small>
                    </div>
                </div>
            </div>

            <!-- Paid -->
            <div class="col-lg-3 col-6 mb-3">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-info">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">Sudah
                            Bayar</span>
                        <h2 class="mb-0 font-weight-bold text-info"><?= number_format($stats['paid'] ?? 0) ?></h2>
                        <small class="text-muted mt-2">Lunas Pembayaran</small>
                    </div>
                </div>
            </div>

            <!-- Unpaid/Pending -->
            <div class="col-lg-3 col-6 mb-3">
                <div class="kpi-card p-4">
                    <div class="kpi-icon-bg text-warning">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="d-flex flex-column position-relative">
                        <span class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; font-weight: 600;">
                            <?= ($role === 'tu') ? 'Terjadwal' : 'Belum Bayar' ?>
                        </span>
                        <h2 class="mb-0 font-weight-bold text-warning">
                            <?= ($role === 'tu') ? number_format($scheduledCount ?? 0) : number_format($stats['unpaid'] ?? 0) ?>
                        </h2>
                        <small class="text-<?= ($role === 'tu') ? 'success' : 'danger' ?> mt-2">
                            <?= ($role === 'tu') ? 'Peserta Terjadwal' : 'Perlu Ditagih' ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Charts/Table -->
            <div class="col-lg-8">
                <?php if ($role !== 'tu' && $role !== 'upkh'): ?>
                    <!-- S2 Magister Section -->
                    <div class="card card-premium mb-4">
                        <div class="card-header d-flex align-items-center text-white"
                            style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border-radius: 1rem 1rem 0 0;">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-graduation-cap mr-2"></i> Program Magister (S2)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 pl-4">Program Studi</th>
                                            <th class="border-0 text-center">Total</th>
                                            <th class="border-0 text-center">Lulus</th>
                                            <th class="border-0 text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($s2Stats)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data S2</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (array_slice($s2Stats, 0, 5) as $stat): ?>
                                                <tr>
                                                    <td class="pl-4 font-weight-medium"><?= $stat['nama_prodi'] ?? '-' ?></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-light"><?= $stat['total'] ?? 0 ?></span></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-success"><?= $stat['lulus'] ?? 0 ?></span></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-info"><?= $stat['paid'] ?? 0 ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- S3 Doktor Section -->
                    <div class="card card-premium">
                        <div class="card-header d-flex align-items-center text-white"
                            style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); border-radius: 1rem 1rem 0 0;">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-user-graduate mr-2"></i> Program Doktor (S3)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 pl-4">Program Studi</th>
                                            <th class="border-0 text-center">Total</th>
                                            <th class="border-0 text-center">Lulus</th>
                                            <th class="border-0 text-center">Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($s3Stats)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data S3</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (array_slice($s3Stats, 0, 5) as $stat): ?>
                                                <tr>
                                                    <td class="pl-4 font-weight-medium"><?= $stat['nama_prodi'] ?? '-' ?></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-light"><?= $stat['total'] ?? 0 ?></span></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-success"><?= $stat['lulus'] ?? 0 ?></span></td>
                                                    <td class="text-center"><span
                                                            class="badge badge-info"><?= $stat['paid'] ?? 0 ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === 'tu' && !empty($todaySchedule)): ?>
                    <!-- Today's Schedule (TU) -->
                    <div class="card card-premium">
                        <div class="card-header d-flex align-items-center text-white"
                            style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); border-radius: 1rem 1rem 0 0;">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-calendar-day mr-2"></i> Jadwal Ujian Hari Ini
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($todaySchedule as $schedule): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="schedule-card">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="font-weight-bold"><?= $schedule['sesi_ujian'] ?? 'Sesi' ?></div>
                                                    <div class="small opacity-75"><?= $schedule['ruang_ujian'] ?? 'Ruang' ?>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="h4 mb-0 font-weight-bold"><?= $schedule['peserta_count'] ?? 0 ?>
                                                    </div>
                                                    <div class="small opacity-75">peserta</div>
                                                </div>
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-white-25">
                                                <i class="fas fa-clock mr-1"></i> <?= $schedule['waktu_ujian'] ?? '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === 'upkh'): ?>
                    <!-- Verification Stats (UPKH) -->
                    <div class="card card-premium">
                        <div class="card-header d-flex align-items-center text-white"
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 1rem 1rem 0 0;">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-clipboard-check mr-2"></i> Status Verifikasi Dokumen
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="h1 font-weight-bold text-success"><?= number_format($verifiedCount ?? 0) ?>
                                    </div>
                                    <p class="text-muted mb-0">Terverifikasi</p>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="h1 font-weight-bold text-warning">
                                        <?= number_format(($stats['lulus'] ?? 0) - ($verifiedCount ?? 0)) ?></div>
                                    <p class="text-muted mb-0">Belum Verifikasi</p>
                                </div>
                            </div>
                            <div class="progress mt-4" style="height: 12px; border-radius: 6px;">
                                <?php
                                $total = max(($stats['lulus'] ?? 0), 1);
                                $percent = round(($verifiedCount ?? 0) / $total * 100);
                                ?>
                                <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                            </div>
                            <p class="text-center text-muted mt-2 mb-0"><?= $percent ?>% terverifikasi</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Recent & Info -->
            <div class="col-lg-4">
                <!-- Recent Participants -->
                <?php if (!empty($recentParticipants)): ?>
                    <div class="card card-premium">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0 font-weight-bold text-gray-800">
                                <i class="fas fa-clock text-primary mr-2"></i> Pendaftar Terbaru
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php foreach ($recentParticipants as $p): ?>
                                <div class="recent-item px-4 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="font-weight-semibold text-dark"><?= $p['nama_lengkap'] ?? '-' ?></div>
                                            <div class="small text-muted"><?= $p['nama_prodi'] ?? '-' ?></div>
                                        </div>
                                        <span
                                            class="badge badge-<?= ($p['status_berkas'] === 'lulus') ? 'success' : (($p['status_berkas'] === 'gagal') ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($p['status_berkas'] ?? 'pending') ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-white border-0 text-center">
                            <a href="/admin/participants" class="text-primary font-weight-semibold">
                                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- System Info -->
                <div class="card card-premium mt-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 font-weight-bold text-gray-800">
                            <i class="fas fa-info-circle text-info mr-2"></i> Informasi Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Semester Aktif</span>
                            <span class="font-weight-semibold"><?= $semesterName ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Role Anda</span>
                            <span class="font-weight-semibold"><?= $roleDisplayName ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Username</span>
                            <span class="font-weight-semibold"><?= $username ?? '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Versi</span>
                            <span class="font-weight-semibold">v1.1.0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Simple clock update
    setInterval(() => {
        const now = new Date();
        const clock = document.getElementById('clock');
        if (clock) {
            clock.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
    }, 1000);
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>