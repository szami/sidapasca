<?php ob_start(); ?>

<div class="content-header">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Presensi Ujian</h1>
        </div>
        <div class="col-sm-6 text-right">
            <a href="/admin/attendance/print-berita-acara?ruang=<?php echo urlencode($room); ?>&sesi=<?php echo urlencode($sesi); ?>&tanggal=<?php echo urlencode($tanggal); ?>" 
               target="_blank" class="btn btn-info mr-2">
                <i class="fas fa-print mr-1"></i> Cetak Berita Acara
            </a>
            <a href="/admin/attendance" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-check mr-2 text-success"></i>
                    Detail Sesi: <?php echo $sesi; ?> | <?php echo $room; ?> | <?php echo date('d/m/Y', strtotime($tanggal)); ?>
                </h3>
            </div>
            <form action="/admin/attendance/save" method="POST">
                <input type="hidden" name="room" value="<?php echo $room; ?>">
                <input type="hidden" name="sesi" value="<?php echo $sesi; ?>">
                <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Silakan tandai peserta yang hadir. Peserta yang tidak dicentang akan dianggap <strong>Tidak Hadir</strong>.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50" class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" id="checkAll" checked>
                                            <label for="checkAll" class="custom-control-label font-weight-bold">Cek All</label>
                                        </div>
                                    </th>
                                    <th>No. Peserta</th>
                                    <th>Nama Lengkap</th>
                                    <th>Program Studi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($participants)): ?>
                                    <?php foreach ($participants as $p): ?>
                                        <tr>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input attendance-check" type="checkbox" 
                                                           id="p_<?php echo $p['id']; ?>" name="present[]" 
                                                           value="<?php echo $p['id']; ?>"
                                                           <?php echo ($p['is_present'] === null || $p['is_present'] == 1) ? 'checked' : ''; ?>>
                                                    <label for="p_<?php echo $p['id']; ?>" class="custom-control-label"></label>
                                                </div>
                                            </td>
                                            <td class="text-monospace"><?php echo $p['nomor_peserta']; ?></td>
                                            <td class="font-weight-bold"><?php echo strtoupper($p['nama_lengkap']); ?></td>
                                            <td><?php echo strtoupper($p['nama_prodi']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <span class="text-muted">Tidak ada peserta yang dijadwalkan di sesi ini.</span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 d-flex justify-content-between">
                    <span class="text-muted mt-2">
                        Total Peserta: <strong><?php echo count($participants); ?></strong>
                    </span>
                    <button type="submit" class="btn btn-success px-4" <?php echo empty($participants) ? 'disabled' : ''; ?>>
                        <i class="fas fa-save mr-1"></i> Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.attendance-check');

        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });

        // Update CheckAll state based on individual checkboxes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const totalChecked = document.querySelectorAll('.attendance-check:checked').length;
                checkAll.checked = totalChecked === checkboxes.length;
                checkAll.indeterminate = totalChecked > 0 && totalChecked < checkboxes.length;
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>
