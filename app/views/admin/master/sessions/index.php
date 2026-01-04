<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Master Sesi Ujian</h3>
                <div class="card-tools">
                    <a href="/admin/master/sessions/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>
                        Tambah Sesi</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sesi</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Ruangan (Lab)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $index => $s): ?>
                            <tr>
                                <td>
                                    <?php echo $index + 1; ?>
                                </td>
                                <td>
                                    <b>
                                        <?php echo htmlspecialchars($s['nama_sesi']); ?>
                                    </b><br>
                                    <span class="badge badge-<?php echo $s['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $s['is_active'] ? 'Aktif' : 'Non-Aktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d-m-Y', strtotime($s['tanggal'])); ?>
                                </td>
                                <td>
                                    <?php echo $s['waktu_mulai'] . ' - ' . $s['waktu_selesai']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($s['nama_ruang']); ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($s['fakultas']); ?></small>
                                </td>
                                <td>
                                    <a href="/admin/master/sessions/edit/<?php echo $s['id']; ?>"
                                        class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                                    <a href="/admin/master/sessions/delete/<?php echo $s['id']; ?>"
                                        class="btn btn-danger btn-xs" onclick="return confirm('Hapus sesi ini?')"><i
                                            class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php';
?>