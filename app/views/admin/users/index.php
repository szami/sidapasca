<?php ob_start(); ?>

<!-- Alert Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php if ($_GET['success'] === 'created'): ?>
            <i class="fas fa-check-circle"></i> User berhasil ditambahkan!
        <?php elseif ($_GET['success'] === 'updated'): ?>
            <i class="fas fa-check-circle"></i> User berhasil diupdate!
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <i class="fas fa-check-circle"></i> User berhasil dihapus!
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i>
        <?php if ($_GET['error'] === 'not_found'): ?>
            User tidak ditemukan!
        <?php elseif ($_GET['error'] === 'cannot_delete_self'): ?>
            Tidak dapat menghapus akun sendiri!
        <?php elseif ($_GET['error'] === 'last_superadmin'): ?>
            Tidak dapat menghapus satu-satunya superadmin!
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Manajemen User</h3>
        <div class="card-tools">
            <a href="/admin/users/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah User
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover" id="usersTable">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="20%">Username</th>
                    <th width="15%">Role</th>
                    <th width="20%">Prodi</th>
                    <th width="20%">Dibuat</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- AJAX Populated -->
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        const currentUserId = <?php echo $_SESSION['admin'] ?? 0; ?>;
        const allowDelete = "<?php echo \App\Models\Setting::get('allow_delete', '1'); ?>" === "1";

        $('#usersTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": APP_URL + "/api/master/users",
            "columns": [
                { "data": "id" },
                {
                    "data": "username",
                    "render": function (data) { return `<strong>${data}</strong>`; }
                },
                {
                    "data": "role_display",
                    "render": function (data, type, row) {
                        return `<span class="badge ${row.role_badge}">${data}</span>`;
                    }
                },
                {
                    "data": "prodi_id",
                    "render": function (data) {
                        return data ? `<code>${data}</code>` : '<span class="text-muted">-</span>';
                    }
                },
                {
                    "data": "created_at",
                    "render": function (data) {
                        return data ? new Date(data).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
                    }
                },
                {
                    "data": "id",
                    "orderable": false,
                    "render": function (data, type, row) {
                        let btns = `<a href="${APP_URL}/admin/users/edit/${data}" class="btn btn-sm btn-warning mr-1"><i class="fas fa-edit"></i></a>`;
                        if (data != currentUserId && allowDelete) {
                            btns += `<a href="${APP_URL}/admin/users/delete/${data}" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user ${row.username}?')"><i class="fas fa-trash"></i></a>`;
                        }
                        return btns;
                    }
                }
            ],
            "order": [[0, "desc"]]
        });
    });
</script>
</div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>