<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Sesi Ujian</h3>
            </div>
            <form action="/admin/master/sessions/store" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Sesi</label>
                        <input type="text" name="nama_sesi" class="form-control" placeholder="Contoh: Sesi 1 / Pagi"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Ruang / Lab (Bisa lebih dari satu)</label>
                        <div class="row"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            <?php
                            // Group rooms by Faculty
                            $roomsByFaculty = [];
                            foreach ($rooms as $r) {
                                $roomsByFaculty[$r['fakultas']][] = $r;
                            }
                            ?>
                            <?php foreach ($roomsByFaculty as $faculty => $facultyRooms): ?>
                                <div class="col-12 mb-2">
                                    <strong class="text-primary"><?php echo htmlspecialchars($faculty); ?></strong>
                                </div>
                                <?php foreach ($facultyRooms as $r): ?>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox"
                                                id="room_<?php echo $r['id']; ?>" name="exam_room_ids[]"
                                                value="<?php echo $r['id']; ?>">
                                            <label for="room_<?php echo $r['id']; ?>"
                                                class="custom-control-label font-weight-normal">
                                                <?php echo htmlspecialchars($r['nama_ruang']) . ' (' . $r['kapasitas'] . ')'; ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-12">
                                    <hr class="my-1">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Ujian</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="time" name="waktu_mulai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Jam Selesai</label>
                                <input type="time" name="waktu_selesai" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
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