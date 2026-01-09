<?php ob_start(); ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daftar Hadir Peserta Ujian</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="/admin/exam" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Manajemen Ujian
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Daftar Hadir</h3>
                    </div>
                    <form method="GET" action="/admin/attendance-print" target="_blank">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="sesi">Sesi Ujian</label>
                                <select name="sesi" id="sesi" class="form-control">
                                    <option value="all">Semua Sesi</option>
                                    <?php if (!empty($sessions)): ?>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?php echo htmlspecialchars($session); ?>">
                                                <?php echo htmlspecialchars($session); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Pilih sesi ujian atau semua sesi</small>
                            </div>

                            <div class="form-group">
                                <label for="ruang">Ruang Ujian</label>
                                <select name="ruang" id="ruang" class="form-control">
                                    <option value="all">Semua Ruangan</option>
                                    <?php if (!empty($rooms)): ?>
                                        <?php foreach ($rooms as $room): ?>
                                            <option value="<?php echo htmlspecialchars($room); ?>">
                                                <?php echo htmlspecialchars($room); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Pilih ruangan ujian atau semua ruangan</small>
                            </div>

                            <div class="form-group">
                                <label for="perPageSelect">Jumlah Data per Halaman</label>
                                <select id="perPageSelect" class="form-control" onchange="toggleCustomPerPage(this)">
                                    <option value="15">15 baris</option>
                                    <option value="18" selected>18 baris (default)</option>
                                    <option value="20">20 baris</option>
                                    <option value="25">25 baris</option>
                                    <option value="30">30 baris</option>
                                    <option value="custom">Kustom...</option>
                                </select>
                                <input type="number" name="perPage" id="perPageInput" class="form-control mt-2" min="5"
                                    max="50" value="18" style="display: none;" placeholder="Masukkan jumlah (5-50)">
                                <small class="form-text text-muted">Tentukan berapa banyak peserta per halaman cetak
                                    (5-50)</small>
                            </div>

                            <script>
                                function toggleCustomPerPage(select) {
                                    var input = document.getElementById('perPageInput');
                                    if (select.value === 'custom') {
                                        input.style.display = 'block';
                                        input.focus();
                                    } else {
                                        input.style.display = 'none';
                                        input.value = select.value;
                                    }
                                }
                                // Initialize on page load
                                document.addEventListener('DOMContentLoaded', function () {
                                    var select = document.getElementById('perPageSelect');
                                    var input = document.getElementById('perPageInput');
                                    input.value = select.value;
                                });
                            </script>

                            <div class="alert alert-info">
                                <i class="icon fas fa-info"></i>
                                <strong>Info:</strong> Daftar hadir akan dibuka di tab baru dan siap untuk dicetak.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" onclick="submitForm('/admin/attendance-print')"
                                    class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-list-alt mr-1"></i> Cetak Daftar Hadir
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" onclick="submitForm('/admin/attendance/print-berita-acara')"
                                    class="btn btn-info btn-lg btn-block">
                                    <i class="fas fa-file-contract mr-1"></i> Cetak Berita Acara
                                </button>
                            </div>
                        </div>
                </div>
                </form>

                <script>
                    function submitForm(action) {
                        var form = document.querySelector('form[action="/admin/attendance-print"]');
                        // If form action is already dynamic, we use that. 
                        // But here we might just have the default action. 
                        // Let's rely on the form element directly.
                        if (!form) form = document.forms[0]; // Fallback

                        form.action = action;
                        form.submit();
                    }
                </script>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
                </div>
                <div class="card-body">
                    <h5>Semester Aktif:</h5>
                    <p class="lead">
                        <?php echo $semesterName ?? '-'; ?>
                    </p>

                    <hr>

                    <h5>Statistik Peserta:</h5>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Pendaftar</td>
                            <td class="text-right"><strong>
                                    <?php echo $stats['total'] ?? 0; ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>Lulus + Bayar + Nomor</td>
                            <td class="text-right"><strong class="text-info">
                                    <?php echo $stats['eligible'] ?? 0; ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>Sudah Dijadwalkan</td>
                            <td class="text-right"><strong class="text-success">
                                    <?php echo $stats['scheduled'] ?? 0; ?>
                                </strong></td>
                        </tr>
                    </table>

                    <?php if (($stats['eligible'] ?? 0) > 0 && ($stats['scheduled'] ?? 0) == 0): ?>
                        <div class="alert alert-warning">
                            <i class="icon fas fa-exclamation-triangle"></i>
                            Ada <strong>
                                <?php echo $stats['eligible']; ?> peserta
                            </strong> yang belum dijadwalkan.
                            <a href="/admin/scheduler">Jadwalkan sekarang »</a>
                        </div>
                    <?php elseif (($stats['eligible'] ?? 0) == 0): ?>
                        <div class="alert alert-danger">
                            <i class="icon fas fa-times-circle"></i>
                            Belum ada peserta yang memenuhi kriteria untuk dicetak.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>