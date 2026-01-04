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
        <table class="table table-bordered table-hover">
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
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?php echo $user['id']; ?>
                        </td>
                        <td><strong>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </strong></td>
                        <td>
                            <span class="badge <?php echo \App\Utils\RoleHelper::getRoleBadgeClass($user['role']); ?>">
                                <?php echo \App\Utils\RoleHelper::getRoleDisplayName($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['prodi_id']): ?>
                                <code><?php echo $user['prodi_id']; ?></code>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('d M Y H:i', strtotime($user['created_at'])); ?>
                        </td>
                        <td>
                            <a href="/admin/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['id'] != $_SESSION['admin']): ?>
                                <a href="/admin/users/delete/<?php echo $user['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus user <?php echo htmlspecialchars($user['username']); ?>?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>