<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Ruang Ujian</h3>
            </div>
            <form action="/admin/master/rooms/update/<?php echo $room['id']; ?>" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Fakultas</label>
                        <input type="text" name="fakultas" class="form-control"
                            value="<?php echo htmlspecialchars($room['fakultas']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Ruang / Lab</label>
                        <input type="text" name="nama_ruang" class="form-control"
                            value="<?php echo htmlspecialchars($room['nama_ruang']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kapasitas (Orang)</label>
                        <input type="number" name="kapasitas" class="form-control"
                            value="<?php echo htmlspecialchars($room['kapasitas']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Link Google Maps (Opsional)</label>
                        <input type="text" name="google_map_link" class="form-control"
                            value="<?php echo htmlspecialchars($room['google_map_link'] ?? ''); ?>"
                            placeholder="https://maps.google.com/...">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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