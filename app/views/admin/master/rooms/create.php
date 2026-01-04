<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Ruang Ujian</h3>
            </div>
            <form action="/admin/master/rooms/store" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Fakultas</label>
                        <input type="text" name="fakultas" class="form-control" placeholder="Contoh: Fakultas Teknik"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Nama Ruang / Lab</label>
                        <input type="text" name="nama_ruang" class="form-control" placeholder="Contoh: Lab Komputer 1"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas (Orang)</label>
                        <input type="number" name="kapasitas" class="form-control" placeholder="Contoh: 30" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="/admin/master/rooms" class="btn btn-default float-right">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php';
?>