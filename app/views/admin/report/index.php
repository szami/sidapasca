<?php ob_start(); ?>

<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark"><i class="fas fa-file-alt mr-2"></i> Laporan Admisi Pascasarjana</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan Ringkasan
                        </h3>
                    </div>
                    <form action="/admin/laporan/cetak" method="POST" target="_blank">
                        <div class="card-body">
                            <div class="form-group on-boarding-input">
                                <label for="semester_id" class="font-weight-normal text-muted">Pilih Semester</label>
                                <select name="semester_id" id="semester_id" class="form-control select2"
                                    style="width: 100%;" required>
                                    <option value="all">-- Semua Semester (Gabungan) --</option>
                                    <?php foreach ($semesters as $sem): ?>
                                        <?php
                                        // Safe check for active semester ID
                                        $isActive = is_array($activeSemester) && isset($activeSemester['id']) && $activeSemester['id'] == $sem['id'];
                                        ?>
                                        <option value="<?php echo $sem['id']; ?>" <?php echo $isActive ? 'selected' : ''; ?>>
                                            <?php echo $sem['nama']; ?>     <?php echo $sem['is_active'] ? '(Aktif)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Pilih periode semester untuk data laporan. Pilih "Semua Semester" untuk rekapitulasi
                                    total.
                                </small>
                            </div>

                            <div class="alert alert-info bg-light border-0 shadow-sm mt-4">
                                <div class="d-flex">
                                    <div class="mr-3 text-info">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold text-info">Informasi Laporan</h6>
                                        <p class="mb-0 text-muted small">
                                            Laporan akan digenerate dalam format <strong>PDF</strong> (ukuran A4).
                                            Data mencakup statistik pendaftar, status pemberkasan, dan status pembayaran
                                            untuk jenjang S2 (Magister) dan S3 (Doktor).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 text-right">
                            <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i> Cetak Laporan PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>