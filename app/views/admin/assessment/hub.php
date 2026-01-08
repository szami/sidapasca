@extends('layouts.admin')

@section('title', 'Penilaian & Kelulusan')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Penilaian & Kelulusan</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                <li class="breadcrumb-item active">Penilaian & Kelulusan</li>
            </ol>
        </div>
    </div>

    <!-- Assessment Section -->
    <?php
    $isSuperadmin = \App\Utils\RoleHelper::isSuperadmin();
    $isAdmin = \App\Utils\RoleHelper::isAdmin();
    $canManageAssessmentBidang = \App\Utils\RoleHelper::canManageAssessmentBidang();
    ?>

    <?php if ($isSuperadmin || $isAdmin || $canManageAssessmentBidang): ?>
        <h5 class="mt-4 mb-2"><i class="fas fa-pen-fancy mr-1"></i> Penilaian</h5>
        <div class="row">
            <?php if ($isSuperadmin || $isAdmin || $canManageAssessmentBidang): ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>Komponen</h3>
                            <p>Kelola Komponen Nilai</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <a href="/admin/assessment/components" class="small-box-footer">Kelola Komponen <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isSuperadmin || $isAdmin): ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Proses Nilai</h3>
                            <p>Input Nilai TPA & Keputusan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <a href="/admin/assessment/scores" class="small-box-footer">Proses Nilai <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($canManageAssessmentBidang): ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>Bidang</h3>
                            <p>Input Nilai Bidang (Prodi)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <a href="/admin/assessment/bidang" class="small-box-footer">Input Nilai Bidang <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Graduation Section -->
    <?php if ($isSuperadmin || $isAdmin): ?>
        <h5 class="mt-4 mb-2"><i class="fas fa-graduation-cap mr-1"></i> Kelulusan</h5>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Daya Tampung</h3>
                        <p>Kuota Penerimaan per Prodi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <a href="/admin/graduation/quotas" class="small-box-footer">Kelola Kuota <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Info -->
    <?php
    $db = \App\Utils\Database::connection();
    $activeSem = \App\Models\Semester::getActive();
    $semesterId = $activeSem['id'] ?? 0;

    $totalWithScore = $db->query("SELECT COUNT(DISTINCT participant_id) as total FROM assessment_scores WHERE participant_id IN (SELECT id FROM participants WHERE semester_id = ?)")->bind($semesterId)->fetchAssoc()['total'] ?? 0;
    $totalComponents = $db->query("SELECT COUNT(*) as total FROM assessment_components")->fetchAssoc()['total'] ?? 0;
    ?>

    <h5 class="mt-4 mb-2"><i class="fas fa-chart-bar mr-1"></i> Statistik Cepat</h5>
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-pen"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Peserta Sudah Dinilai</span>
                    <span class="info-box-number"><?= $totalWithScore ?></span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-list-ul"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Komponen Nilai</span>
                    <span class="info-box-number"><?= $totalComponents ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Guide -->
    <div class="callout callout-info mt-4">
        <h5><i class="fas fa-info-circle mr-1"></i> Alur Penilaian</h5>
        <ol class="mb-0">
            <li><strong>Setup Komponen</strong> - Tentukan komponen nilai (TPA, Bidang, dll) per prodi</li>
            <li><strong>Input Nilai TPA</strong> - Admin input hasil TPA dari CBT</li>
            <li><strong>Input Nilai Bidang</strong> - Admin Prodi input nilai bidang/wawancara</li>
            <li><strong>Keputusan Akhir</strong> - Tetapkan status Lulus/Tidak Lulus</li>
        </ol>
    </div>
</div>
@endsection