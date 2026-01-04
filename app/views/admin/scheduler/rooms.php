<?php ob_start(); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Monitor Ruang Ujian & Penjadwalan</h3>
                <div class="card-tools">
                    <a href="/admin/scheduler" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Kembali
                        ke Penjadwalan</a>
                </div>
            </div>
            <div class="card-body">
                <div id="accordion">
                    <?php if (empty($sessions)): ?>
                        <div class="alert alert-info">Belum ada sesi ujian yang dibuat untuk semester aktif.</div>
                    <?php endif; ?>

                    <?php foreach ($sessions as $index => $s): ?>
                        <?php
                        $count = count($s['participants'] ?? []);
                        $capacity = $s['kapasitas'];
                        $percent = $capacity > 0 ? ($count / $capacity) * 100 : 0;
                        $color = $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success');
                        ?>
                        <div class="card card-outline card-<?php echo $color; ?> mb-2">
                            <div class="card-header" style="background-color: #f9f9f9;">
                                <h4 class="card-title w-100">
                                    <a class="d-block w-100 text-dark" data-toggle="collapse"
                                        href="#collapse<?php echo $index; ?>">
                                        <i class="fas fa-clock mr-2 text-<?php echo $color; ?>"></i>
                                        <strong><?php echo $s['nama_sesi']; ?></strong> |
                                        <span class="text-primary"><?php echo $s['nama_ruang']; ?></span> |
                                        <span class="font-weight-normal"><?php echo $s['tanggal']; ?></span>
                                        <small class="text-muted ml-1">(<?php echo $s['waktu_mulai']; ?> -
                                            <?php echo $s['waktu_selesai']; ?>)</small>

                                        <span class="float-right badge badge-<?php echo $color; ?> p-2 shadow-sm">
                                            <i class="fas fa-users mr-1"></i>
                                            <?php echo $count; ?> / <?php echo $capacity; ?> Peserta
                                        </span>
                                    </a>
                                </h4>
                            </div>
                            <div id="collapse<?php echo $index; ?>" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">No</th>
                                                <th>Nomor Peserta</th>
                                                <th>Nama Lengkap</th>
                                                <th>Prodi</th>
                                                <th style="width: 100px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($s['participants'])): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Belum ada peserta di sesi ini.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($s['participants'] as $i => $p): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo $i + 1; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $p['nomor_peserta']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $p['nama_lengkap']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $p['nama_prodi']; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-xs btn-warning btn-move"
                                                                data-id="<?php echo $p['id']; ?>"
                                                                data-name="<?php echo $p['nama_lengkap']; ?>" data-toggle="modal"
                                                                data-target="#moveModal">
                                                                <i class="fas fa-exchange-alt"></i> Pindah
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Move Modal -->
<div class="modal fade" id="moveModal" tabindex="-1" role="dialog" aria-labelledby="moveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/scheduler/assign" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moveModalLabel">Pindah Sesi Ujian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Pindahkan peserta <b><span id="pName"></span></b> ke sesi lain:</p>
                    <input type="hidden" name="participant_ids[]" id="pId">
                    <div class="form-group">
                        <label>Pilih Sesi Tujuan</label>
                        <select name="session_id" class="form-control" required>
                            <option value="">-- Pilih Sesi --</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?php echo $s['id']; ?>">
                                    <?php echo $s['nama_sesi']; ?> -
                                    <?php echo $s['nama_ruang']; ?> (
                                    <?php echo $s['tanggal']; ?>)
                                    [Sisa:
                                    <?php echo $s['kapasitas'] - count($s['participants'] ?? []); ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Kapasitas akan diperiksa sebelum dipindahkan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('.btn-move').on('click', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#pId').val(id);
            $('#pName').text(name);
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>