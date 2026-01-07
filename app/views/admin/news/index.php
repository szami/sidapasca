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
                <a href="/admin/news/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Berita
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table id="newsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Tanggal Publikasi</th>
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
        var table = $('#newsTable').DataTable({
            "processing": true,
            "ajax": "/admin/news/api-data",
            "columns": [
                { "data": "id" },
                { "data": "title" },
                {
                    "data": "category",
                    "render": function (data) {
                        return '<span class="badge badge-info">' + data + '</span>';
                    }
                },
                {
                    "data": "content_type",
                    "render": function (data) {
                        return data === 'image_only' ? '<i class="fas fa-image"></i> Gambar' : '<i class="fas fa-file-alt"></i> Teks + Gambar';
                    }
                },
                {
                    "data": "is_published",
                    "render": function (data) {
                        return data == 1 ? '<span class="badge badge-success">Published</span>' : '<span class="badge badge-secondary">Draft</span>';
                    }
                },
                {
                    "data": "published_at",
                    "render": function (data) {
                        return data ? new Date(data).toLocaleString('id-ID') : '-';
                    }
                },
                {
                    "data": "id",
                    "render": function (data, type, row) {
                        return `
                        <a href="/admin/news/edit/${data}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <a href="/admin/news/delete/${data}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus berita ini?')"><i class="fas fa-trash"></i></a>
                    `;
                    }
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "order": [[5, "desc"]] // Sort by Published At DESC
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>