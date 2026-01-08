<?php ob_start(); ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Manajemen Ujian</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="/admin">Home</a></li>
            <li class="breadcrumb-item active">Manajemen Ujian</li>
        </ol>
    </div>
</div>

<?php
$db = \App\Utils\Database::connection();
$semesters = $db->query("SELECT * FROM semesters ORDER BY id DESC")->fetchAll();
$selectedSemesterId = \Leaf\Http\Request::get('semester_id') ?: (\App\Models\Semester::getActive()['id'] ?? null);
?>

<!-- Semester Filter Card -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-5">
                <form method="GET" action="" id="semesterFilterForm" class="mb-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <select name="semester_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>" <?= $selectedSemesterId == $sem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sem['nama']) ?> (<?= htmlspecialchars($sem['kode']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="col-md-7 text-md-right mt-2 mt-md-0">
                <span class="text-muted"><i class="fas fa-info-circle mr-1"></i> Pilih semester untuk memfilter seluruh
                    data dan statistik ujian.</span>
            </div>
        </div>
    </div>
</div>

<!-- Scheduling Section -->
<?php if (\App\Utils\RoleHelper::canManageSchedule()): ?>
    <h5 class="mb-3"><i class="fas fa-calendar-alt mr-1 text-info"></i> Penjadwalan & Export</h5>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-info shadow">
                <div class="inner">
                    <h4>Scheduler</h4>
                    <p>Jadwalkan Peserta Ujian</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <a href="/admin/scheduler" class="small-box-footer">Buka Scheduler <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-success shadow">
                <div class="inner">
                    <h4>Kehadiran</h4>
                    <p>Entry Kehadiran Ujian</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <a href="/admin/attendance" class="small-box-footer">Kelola Kehadiran <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-warning shadow">
                <div class="inner">
                    <h4>Export CAT</h4>
                    <p>Export Jadwal (Standar)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <a href="/admin/scheduler/export-cat?semester_id=<?= $selectedSemesterId ?>" class="small-box-footer">Export
                    Excel <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-danger shadow">
                <div class="inner">
                    <h4>Export IT</h4>
                    <p>Export Detail (TTL & PWD)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="/admin/participants/export?semester_id=<?= $selectedSemesterId ?>" class="small-box-footer">Export
                    Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Print Section -->
<?php if (\App\Utils\RoleHelper::canPrintSchedule()): ?>
    <h5 class="mt-4 mb-3"><i class="fas fa-print mr-1 text-primary"></i> Cetak Dokumen</h5>
    <div class="row">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-primary shadow">
                <div class="inner">
                    <h4>Jadwal</h4>
                    <p>Cetak Jadwal per Ruang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-print"></i>
                </div>
                <a href="/admin/cat-schedule" class="small-box-footer">Cetak Jadwal <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-gradient-purple shadow">
                <div class="inner">
                    <h4>Daftar Hadir</h4>
                    <p>Cetak Form Absensi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <a href="/admin/attendance-list" class="small-box-footer">Cetak Daftar Hadir <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Quick Stats -->
<?php
$semesterId = $selectedSemesterId;

// Use DocumentVerification model for consistent statistics (same as /admin/verification/physical)
$verificationStats = \App\Models\DocumentVerification::getStatistics($semesterId);

// Total Eligible = peserta lulus berkas yang sudah punya nomor peserta
$totalEligible = $verificationStats['total_eligible'] ?? 0;

// Total Verified = peserta dengan status verifikasi fisik 'lengkap' (dari document_verifications)
$totalVerified = $verificationStats['lengkap'] ?? 0;

// Total Scheduled (punya ruang ujian) - dari semua peserta eligible
$totalScheduled = $db->query("SELECT COUNT(*) as total FROM participants WHERE semester_id = ? AND nomor_peserta IS NOT NULL AND nomor_peserta != '' AND ruang_ujian IS NOT NULL AND ruang_ujian != ''")->bind($semesterId)->fetchAssoc()['total'] ?? 0;

// Total Pending (eligible tapi belum punya ruang)
$totalPending = $totalEligible - $totalScheduled;

// Other stats
$totalRooms = $db->query("SELECT COUNT(*) as total FROM exam_rooms")->fetchAssoc()['total'] ?? 0;
$totalSessions = $db->query("SELECT COUNT(*) as total FROM exam_sessions WHERE semester_id = ?")->bind($semesterId)->fetchAssoc()['total'] ?? 0;

// Completion Percentage
$percent = $totalEligible > 0 ? round(($totalScheduled / $totalEligible) * 100) : 0;
$progressColor = $percent >= 80 ? 'bg-success' : ($percent >= 50 ? 'bg-info' : ($percent >= 25 ? 'bg-warning' : 'bg-danger'));
?>

<h5 class="mt-4 mb-3"><i class="fas fa-chart-line mr-1 text-success"></i> Statistik & Monitoring</h5>

<!-- Main Progress Card -->
<div class="card shadow-sm mb-4" style="border-left: 4px solid #17a2b8;">
    <div class="card-body py-4">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-2"
                    style="width: 70px; height: 70px;">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h2 class="text-primary font-weight-bold mb-0"><?= $totalEligible ?></h2>
                <p class="text-muted text-uppercase mb-0" style="font-size: 11px; letter-spacing: 1px;">Peserta
                    Eligible</p>
            </div>
            <div class="col-md-6 px-lg-5 py-3 py-md-0 border-left border-right">
                <div class="progress-group">
                    <span class="font-weight-bold text-dark">Status Penjadwalan</span>
                    <span class="float-right"><b class="text-success">
                            <?= $totalScheduled ?></b> / <?= $totalEligible ?>
                        Peserta</span>
                    <div class="progress mt-2 shadow-sm" style="height: 20px; border-radius: 10px;">
                        <div class="progress-bar <?= $progressColor ?> progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: <?= $percent ?>%; border-radius: 10px;">
                            <span class="font-weight-bold"><?= $percent ?>%</span>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <?php if ($percent >= 100): ?>
                            <i class="fas fa-check-circle text-success"></i> Penjadwalan selesai!
                        <?php elseif ($percent >= 50): ?>
                            <i class="fas fa-hourglass-half text-info"></i> Penjadwalan sedang berlangsung...
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle text-warning"></i> Masih banyak peserta belum terjadwal
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-white mb-2"
                    style="width: 70px; height: 70px;">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <h2 class="text-warning font-weight-bold mb-0"><?= $totalPending ?></h2>
                <p class="text-muted text-uppercase mb-0" style="font-size: 11px; letter-spacing: 1px;">Belum Terjadwal
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="info-box shadow-sm bg-gradient-light">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-door-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-dark">Ruang Ujian</span>
                <span class="info-box-number text-dark" style="font-size: 1.8rem;"><?= $totalRooms ?></span>
                <small class="text-muted">Tersedia</small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="info-box shadow-sm bg-gradient-light">
            <span class="info-box-icon bg-olive elevation-1"><i class="fas fa-stopwatch"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-dark">Sesi Ujian</span>
                <span class="info-box-number text-dark" style="font-size: 1.8rem;"><?= $totalSessions ?></span>
                <small class="text-muted">Aktif Semester Ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="info-box shadow-sm bg-gradient-light">
            <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-dark">Tingkat Penyelesaian</span>
                <span class="info-box-number text-dark" style="font-size: 1.8rem;"><?= $percent ?>%</span>
                <div class="progress progress-xs mt-1">
                    <div class="progress-bar <?= $progressColor ?>" style="width: <?= $percent ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Manajemen Ujian';
include __DIR__ . '/../../layouts/admin.php';
?>