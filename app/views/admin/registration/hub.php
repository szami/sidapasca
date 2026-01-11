<?php ob_start(); ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Registrasi / Daftar Ulang</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="/admin">Home</a></li>
            <li class="breadcrumb-item active">Registrasi / Daftar Ulang</li>
        </ol>
    </div>
</div>

<?php
$isSuperadmin = \App\Utils\RoleHelper::isSuperadmin();
$isAdmin = \App\Utils\RoleHelper::isAdmin();
?>

<h5 class="mt-4 mb-2"><i class="fas fa-file-invoice-dollar mr-1"></i> SIREMA Integration</h5>
<div class="row">
    <?php if ($isSuperadmin || $isAdmin): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Export Tagihan</h3>
                    <p>Generate File untuk SIREMA</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <a href="/admin/payment-export" class="small-box-footer">Export Tagihan <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Future: Import Status Pembayaran -->
    <!-- Future: Sync Data Mahasiswa Baru -->
</div>

<!-- Info -->
<div class="callout callout-info mt-4">
    <h5><i class="fas fa-info-circle mr-1"></i> Tentang SIREMA</h5>
    <p class="mb-0">SIREMA (Sistem Registrasi Mahasiswa) adalah sistem pembayaran terintegrasi ULM. Modul ini mengelola
        proses tagihan dan verifikasi pembayaran untuk peserta yang lulus seleksi.</p>
</div>

<?php
$content = ob_get_clean();
$title = 'Registrasi / Daftar Ulang';
include __DIR__ . '/../../layouts/admin.php';
?>