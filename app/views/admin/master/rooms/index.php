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
                <table class="table table-bordered table-striped" id="roomTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Fakultas</th>
                            <th>Nama Ruang</th>
                            <th width="15%">Kapasitas</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX Populated -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        const table = $('#roomTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": APP_URL + "/api/master/rooms",
            "order": [[0, "desc"]],
            "columns": [
                { "data": "id" },
                { "data": "fakultas" },
                { "data": "nama_ruang" },
                { "data": "kapasitas", "className": "text-center" },
                {
                    "data": "id",
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data) {
                        return `
                        <a href="${APP_URL}/admin/master/rooms/edit/${data}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <a href="${APP_URL}/admin/master/rooms/delete/${data}" class="btn btn-danger btn-xs" onclick="return confirm('Hapus ruang ini?')"><i class="fas fa-trash"></i></a>
                    `;
                    }
                }
            ]
        });
    });
</script>
</div>
</div>
</div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../../layouts/admin.php';
?>