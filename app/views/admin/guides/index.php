<?php
ob_start();
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="/admin/guides/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Panduan
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table id="guidesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Judul</th>
                            <th>Target Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $('#guidesTable').DataTable({
            "processing": true,
            "ajax": "/admin/guides/api-data",
            "columns": [
                { "data": "order_index" },
                { "data": "title" },
                {
                    "data": "role",
                    "render": function (data) {
                        let badge = 'badge-secondary';
                        if (data === 'participant') badge = 'badge-success';
                        if (data === 'admin') badge = 'badge-info';
                        if (data === 'superadmin') badge = 'badge-danger';
                        if (data === 'admin_prodi') badge = 'badge-warning';
                        return '<span class="badge ' + badge + '">' + data.toUpperCase() + '</span>';
                    }
                },
                {
                    "data": "is_active",
                    "render": function (data) {
                        return data == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non-Aktif</span>';
                    }
                },
                {
                    "data": "id",
                    "render": function (data, type, row) {
                        let statusBtn = row.is_active == 1 ?
                            `<a href="/admin/guides/deactivate/${data}" class="btn btn-sm btn-secondary" title="Non-Aktifkan"><i class="fas fa-eye-slash"></i></a>` :
                            `<a href="/admin/guides/activate/${data}" class="btn btn-sm btn-success" title="Aktifkan"><i class="fas fa-eye"></i></a>`;

                        return `
                        <a href="/admin/guides/edit/${data}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        ${statusBtn}
                        <a href="/admin/guides/delete/${data}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus panduan ini?')"><i class="fas fa-trash"></i></a>
                    `;
                    }
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "order": [[0, "asc"]] // Sort by Order Index ASC
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>