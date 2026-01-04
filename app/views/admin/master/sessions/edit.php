<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Sesi Ujian</h3>
            </div>
            <form action="/admin/master/sessions/update/<?php echo $s['id']; ?>" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sesi</label>
                        <input type="text" name="nama_sesi" class="form-control"
                            value="<?php echo htmlspecialchars($s['nama_sesi']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ruang / Lab</label>
                        <select name="exam_room_id" class="form-control" required>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?php echo $r['id']; ?>" <?php echo $r['id'] == $s['exam_room_id'] ? 'selected' : ''; ?>>
                                    <?php echo $r['nama_ruang'] . ' (' . $r['kapasitas'] . ' orang)'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Ujian</label>
                        <input type="date" name="tanggal" class="form-control" value="<?php echo $s['tanggal']; ?>"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="text" name="waktu_mulai" class="form-control"
                                    value="<?php echo $s['waktu_mulai']; ?>" placeholder="08:00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Selesai</label>
                                <input type="text" name="waktu_selesai" class="form-control"
                                    value="<?php echo $s['waktu_selesai']; ?>" placeholder="10:00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="/admin/master/sessions" class="btn btn-default float-right">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php';
?>