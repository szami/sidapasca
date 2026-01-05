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
                <table class="table table-bordered table-striped" id="sessionTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Nama Sesi</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Ruangan (Lab)</th>
                            <th width="10%">Aksi</th>
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
        const table = $('#sessionTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": APP_URL + "/api/master/sessions",
            "order": [[0, "desc"]],
            "columns": [
                { "data": "id" },
                {
                    "data": "nama_sesi",
                    "render": function (data, type, row) {
                        const badge = row.is_active == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non-Aktif</span>';
                        return `<b>${data}</b><br>${badge}`;
                    }
                },
                {
                    "data": "tanggal",
                    "render": function (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID');
                    }
                },
                {
                    "data": "waktu_mulai",
                    "render": function (data, type, row) {
                        return `${data.substring(0, 5)} - ${row.waktu_selesai.substring(0, 5)}`;
                    }
                },
                {
                    "data": "nama_ruang",
                    "render": function (data, type, row) {
                        return `${data}<br><small class="text-muted">${row.fakultas}</small>`;
                    }
                },
                {
                    "data": "id",
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data) {
                        return `
                        <a href="${APP_URL}/admin/master/sessions/edit/${data}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                        <a href="${APP_URL}/admin/master/sessions/delete/${data}" class="btn btn-danger btn-xs" onclick="return confirm('Hapus sesi ini?')"><i class="fas fa-trash"></i></a>
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