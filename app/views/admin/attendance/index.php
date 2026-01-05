<?php ob_start(); ?>

<div class="content-header">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Presensi Ujian</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-clipboard-check mr-2 text-primary"></i>
                    Daftar Sesi Ujian -
                    <?php echo $activeSemester['nama']; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> Presensi berhasil disimpan!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered datatable">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Sesi</th>
                                <th>Ruangan</th>
                                <th class="text-center">Kapasitas</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                                <tr>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($s['tanggal'])); ?>
                                    </td>
                                    <td>
                                        <?php echo date('H:i', strtotime($s['waktu_mulai'])); ?> -
                                        <?php echo date('H:i', strtotime($s['waktu_selesai'])); ?>
                                    </td>
                                    <td>
                                        <?php echo $s['nama_sesi']; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $s['nama_ruang']; ?>
                                        </span>
                                        <br><small class="text-muted">
                                            <?php echo $s['fakultas']; ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $s['kapasitas']; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">
                                            <?php echo $s['assigned_count']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">
                                            <?php echo $s['attended_count']; ?> /
                                            <?php echo $s['assigned_count']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($s['assigned_count'] > 0): ?>
                                            <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/attendance/entry?ruang=<?php echo urlencode($s['nama_ruang']); ?>&sesi=<?php echo urlencode($s['nama_sesi']); ?>&tanggal=<?php echo urlencode($s['tanggal']); ?>"
                                                class="btn btn-sm btn-primary px-3">
                                                <i class="fas fa-edit mr-1"></i> Isi Presensi
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary disabled" title="Belum ada peserta">
                                                <i class="fas fa-edit mr-1"></i> Isi Presensi
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>