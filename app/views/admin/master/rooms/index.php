<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Master Ruang Ujian</h3>
                <div class="card-tools">
                    <a href="/admin/master/rooms/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>
                        Tambah Ruang</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Fakultas</th>
                            <th>Nama Ruang</th>
                            <th>Kapasitas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $index => $room): ?>
                            <tr>
                                <td>
                                    <?php echo $index + 1; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($room['fakultas']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($room['nama_ruang']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($room['kapasitas']); ?>
                                </td>
                                <td>
                                    <a href="/admin/master/rooms/edit/<?php echo $room['id']; ?>"
                                        class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                                    <a href="/admin/master/rooms/delete/<?php echo $room['id']; ?>"
                                        class="btn btn-danger btn-xs" onclick="return confirm('Hapus ruang ini?')"><i
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