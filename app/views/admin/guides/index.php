<?php
ob_start();
$role = $_SESSION['admin_role'] ?? 'admin';
$isAdmin = in_array($role, [\App\Utils\RoleHelper::ROLE_SUPERADMIN, \App\Utils\RoleHelper::ROLE_ADMIN]);
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $isAdmin ? $title : 'Panduan Sistem' ?></h1>
            </div>
            <div class="col-sm-6 text-right">
                <?php if ($isAdmin): ?>
                    <a href="/admin/guides/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Panduan
                    </a>
                <?php endif; ?>
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
                            <?php if ($isAdmin): ?>
                                <th>Target Role</th>
                                <th>Status</th>
                            <?php endif; ?>
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

<!-- View Guide Modal -->
<div class="modal fade" id="viewGuideModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewGuideTitle">Judul Panduan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGuideContent" style="max-height: 70vh; overflow-y: auto;">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const isAdmin = <?= json_encode($isAdmin) ?>;

        const table = $('#guidesTable').DataTable({
            "processing": true,
            "ajax": "/admin/guides/api-data",
            "columns": [
                { "data": "order_index" },
                { "data": "title" },
                ...(isAdmin ? [
                    {
                        "data": "role",
                        "render": function (data) {
                            let badge = 'badge-secondary';
                            if (data === 'participant') badge = 'badge-success';
                            if (data === 'admin') badge = 'badge-info';
                            if (data === 'superadmin') badge = 'badge-danger';
                            if (data === 'admin_prodi') badge = 'badge-warning';
                            if (data === 'upkh') badge = 'badge-primary';
                            if (data === 'tu') badge = 'badge-secondary';
                            return '<span class="badge ' + badge + '">' + data.toUpperCase() + '</span>';
                        }
                    },
                    {
                        "data": "is_active",
                        "render": function (data) {
                            return data == 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non-Aktif</span>';
                        }
                    }
                ] : []),
                {
                    "data": "id",
                    "render": function (data, type, row) {
                        let btns = `<button type="button" class="btn btn-sm btn-info view-guide" data-id="${data}" title="Baca Panduan"><i class="fas fa-book-open"></i> BACA</button>`;

                        if (isAdmin) {
                            let statusBtn = row.is_active == 1 ?
                                `<a href="/admin/guides/deactivate/${data}" class="btn btn-sm btn-secondary" title="Non-Aktifkan"><i class="fas fa-eye-slash"></i></a>` :
                                `<a href="/admin/guides/activate/${data}" class="btn btn-sm btn-success" title="Aktifkan"><i class="fas fa-eye"></i></a>`;

                            btns += `
                                <a href="/admin/guides/edit/${data}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                ${statusBtn}
                                <a href="/admin/guides/delete/${data}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus panduan ini?')"><i class="fas fa-trash"></i></a>
                            `;
                        }

                        return btns;
                    }
                }
            ],
            "responsive": true,
            "autoWidth": false,
            "order": [[0, "asc"]]
        });

        // View Guide Event
        $('#guidesTable').on('click', '.view-guide', function () {
            const data = table.row($(this).parents('tr')).data();
            $('#viewGuideTitle').text(data.title);

            // Create a temporary div to decode HTML entities if they are double encoded
            const txt = document.createElement("textarea");
            txt.innerHTML = data.content;
            $('#viewGuideContent').html(txt.value);

            $('#viewGuideModal').modal('show');
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>