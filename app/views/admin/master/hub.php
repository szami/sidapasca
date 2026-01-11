<?php ob_start(); ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Master Data</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="/admin">Home</a></li>
            <li class="breadcrumb-item active">Master Data</li>
        </ol>
    </div>
</div>

<?php
$isSuperadmin = \App\Utils\RoleHelper::isSuperadmin();
$isAdmin = \App\Utils\RoleHelper::isAdmin();
$isTU = \App\Utils\RoleHelper::isTU();
?>

<h5 class="mt-4 mb-2"><i class="fas fa-database mr-1"></i> Data Referensi</h5>
<div class="row">
    <?php if ($isSuperadmin): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>Semester</h3>
                    <p>Kelola Periode Penerimaan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <a href="/admin/semesters" class="small-box-footer">Kelola Semester <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isSuperadmin || $isAdmin || $isTU): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Ruang Ujian</h3>
                    <p>Kelola Data Ruangan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <a href="/admin/master/rooms" class="small-box-footer">Kelola Ruang <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Sesi Ujian</h3>
                    <p>Kelola Waktu Ujian</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="/admin/master/sessions" class="small-box-footer">Kelola Sesi <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Stats -->
<?php
$db = \App\Utils\Database::connection();
$totalRooms = $db->query("SELECT COUNT(*) as total FROM exam_rooms")->fetchAssoc()['total'] ?? 0;
$totalSessions = $db->query("SELECT COUNT(*) as total FROM exam_sessions")->fetchAssoc()['total'] ?? 0;
$totalSemesters = $db->query("SELECT COUNT(*) as total FROM semesters")->fetchAssoc()['total'] ?? 0;
?>

<h5 class="mt-4 mb-2"><i class="fas fa-chart-bar mr-1"></i> Statistik Cepat</h5>
<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-layer-group"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Semester</span>
                <span class="info-box-number">
                    <?= $totalSemesters ?>
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-door-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Ruang</span>
                <span class="info-box-number">
                    <?= $totalRooms ?>
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Sesi</span>
                <span class="info-box-number">
                    <?= $totalSessions ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Master Data';
include __DIR__ . '/../../layouts/admin.php';
?>