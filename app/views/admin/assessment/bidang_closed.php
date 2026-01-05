<?php
$title = 'Penilaian Tes Bidang';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Penilaian Tes Bidang</h3>
                <p class="text-subtitle text-muted">Input nilai Tes Tertulis Bidang dan Status Rekomendasi</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Penilaian Bidang</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card card-outline card-danger shadow-sm">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-calendar-times text-danger" style="font-size: 80px;"></i>
            </div>
            <h3 class="text-danger mb-3">Penilaian Tes Bidang Belum Dibuka</h3>
            <p class="text-muted mb-4">
                Halaman ini hanya dapat diakses pada jadwal yang telah ditentukan oleh Admin.
            </p>

            <div class="alert alert-warning d-inline-block mx-auto">
                <h5 class="mb-2"><i class="fas fa-clock"></i> Jadwal Penilaian:</h5>
                <p class="mb-0">
                    <strong>
                        <?php echo date('d F Y', strtotime($scheduleStart)); ?>
                        pukul
                        <?php echo $timeStart; ?>
                    </strong>
                    <br>sampai<br>
                    <strong>
                        <?php echo date('d F Y', strtotime($scheduleEnd)); ?>
                        pukul
                        <?php echo $timeEnd; ?>
                    </strong>
                </p>
            </div>

            <p class="mt-4 text-muted">
                <i class="fas fa-info-circle"></i> Silakan kembali pada jadwal yang telah ditentukan.
            </p>

            <a href="/admin" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>