@extends('layouts.admin')

@section('title', 'Manajemen Ujian')

@section('content')
<div class="container-fluid">
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

    <!-- Scheduling Section -->
    <h5 class="mt-4 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Penjadwalan</h5>
    <div class="row">
        <?php if (\App\Utils\RoleHelper::canManageSchedule()): ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>Scheduler</h3>
                        <p>Jadwalkan Peserta Ujian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <a href="/admin/scheduler" class="small-box-footer">Buka Scheduler <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>Kehadiran</h3>
                        <p>Entry Kehadiran Ujian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <a href="/admin/attendance" class="small-box-footer">Kelola Kehadiran <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Export CAT</h3>
                        <p>Export Jadwal untuk CAT</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <a href="/admin/scheduler/export-cat" class="small-box-footer">Export Excel <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Print Section -->
    <?php if (\App\Utils\RoleHelper::canPrintSchedule()): ?>
        <h5 class="mt-4 mb-2"><i class="fas fa-print mr-1"></i> Cetak Dokumen</h5>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>Jadwal</h3>
                        <p>Cetak Jadwal per Ruang</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <a href="/admin/cat-schedule" class="small-box-footer">Cetak Jadwal <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>Daftar Hadir</h3>
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

    <!-- Quick Stats (Optional) -->
    <?php
    $db = \App\Utils\Database::connection();
    $activeSem = \App\Models\Semester::getActive();
    $semesterId = $activeSem['id'] ?? 0;

    $totalScheduled = $db->query("SELECT COUNT(*) as total FROM participants WHERE semester_id = ? AND ruang_ujian IS NOT NULL AND ruang_ujian != ''")->bind($semesterId)->fetchAssoc()['total'] ?? 0;
    $totalRooms = $db->query("SELECT COUNT(*) as total FROM exam_rooms")->fetchAssoc()['total'] ?? 0;
    $totalSessions = $db->query("SELECT COUNT(*) as total FROM exam_sessions WHERE semester_id = ?")->bind($semesterId)->fetchAssoc()['total'] ?? 0;
    ?>
    <h5 class="mt-4 mb-2"><i class="fas fa-chart-bar mr-1"></i> Statistik Cepat</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Peserta Terjadwal</span>
                    <span class="info-box-number"><?= $totalScheduled ?></span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-door-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ruang Ujian</span>
                    <span class="info-box-number"><?= $totalRooms ?></span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sesi Ujian</span>
                    <span class="info-box-number"><?= $totalSessions ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection