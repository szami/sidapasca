<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Update Sistem</h3>
            </div>
            <div class="card-body">
                <p>Fitur ini digunakan untuk memperbarui aplikasi ke versi terbaru (jika terhubung dengan Git).</p>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">Sistem berhasil diperbarui (Simulasi).</div>
                <?php endif; ?>

                <form action="/admin/system/perform-update" method="POST">
                    <button type="submit" class="btn btn-warning btn-block"
                        onclick="return confirm('Apakah Anda yakin ingin melakukan update sistem?')">
                        <i class="fas fa-sync"></i> Lakukan Update Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>