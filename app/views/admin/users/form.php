<?php ob_start(); ?>

<?php
$title = $isEdit ? 'Edit User' : 'Tambah User';
$action = $isEdit ? "/admin/users/update/{$user['id']}" : '/admin/users/store';
?>

<!-- Alert Messages -->
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i>
        <?php if ($_GET['error'] === 'empty_fields'): ?>
            Semua field wajib diisi!
        <?php elseif ($_GET['error'] === 'username_exists'): ?>
            Username sudah digunakan!
        <?php elseif ($_GET['error'] === 'prodi_required'): ?>
            Prodi wajib dipilih untuk role Admin Prodi!
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-edit"></i>
            <?php echo $title; ?>
        </h3>
    </div>
    <form action="<?php echo $action; ?>" method="POST">
        <div class="card-body">
            <!-- Role Selection -->
            <div class="form-group">
                <label>Role <span class="text-danger">*</span></label>
                <select name="role" id="roleSelect" class="form-control" required>
                    <option value="">- Pilih Role -</option>
                    <option value="superadmin" <?php echo ($user['role'] ?? '') === 'superadmin' ? 'selected' : ''; ?>>
                        Super Admin (Full Access)
                    </option>
                    <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                        Administrator (Full Access)
                    </option>
                    <option value="admin_prodi" <?php echo ($user['role'] ?? '') === 'admin_prodi' ? 'selected' : ''; ?>
                        >
                        Admin Prodi (Read Only, per Prodi)
                    </option>
                </select>
                <small class="text-muted">
                    ⚠️ <strong>Admin Prodi</strong> hanya bisa melihat dan download data prodi yang ditugaskan
                </small>
            </div>

            <!-- Prodi Selection (shown only for admin_prodi) -->
            <div class="form-group" id="prodiGroup" style="display: none;">
                <label>Pilih Prodi <span class="text-danger">*</span></label>
                <select name="prodi_id" id="prodiSelect" class="form-control">
                    <option value="">- Pilih Prodi -</option>
                    <?php foreach ($prodis as $prodi): ?>
                        <option value="<?php echo $prodi['kode_prodi']; ?>" <?php echo ($user['prodi_id'] ?? '') === $prodi['kode_prodi'] ? 'selected' : ''; ?>>
                            <?php echo $prodi['kode_prodi']; ?> -
                            <?php echo $prodi['nama_prodi']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Username akan otomatis diisi sesuai kode prodi
                </small>
            </div>

            <!-- Username (auto/manual based on role) -->
            <div class="form-group" id="usernameGroup">
                <label>Username <span class="text-danger">*</span></label>
                <input type="text" name="username" id="usernameInput" class="form-control"
                    value="<?php echo $user['username'] ?? ''; ?>" placeholder="Username" required>
                <small class="text-muted" id="usernameHelp">
                    Username untuk login
                </small>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label>
                    Password
                    <?php if (!$isEdit): ?>
                        <span class="text-danger">*</span>
                    <?php else: ?>
                        <span class="text-muted">(Kosongkan jika tidak ingin mengubah)</span>
                    <?php endif; ?>
                </label>
                <input type="password" name="password" class="form-control" placeholder="Password" <?php echo !$isEdit ? 'required' : ''; ?>>
                <small class="text-muted" id="passwordHelp">
                    <?php if (!$isEdit): ?>
                        Password minimal 6 karakter. Untuk Admin Prodi, gunakan kode prodi sebagai password default.
                    <?php else: ?>
                        Kosongkan jika tidak ingin mengubah password
                    <?php endif; ?>
                </small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="/admin/users" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {
        const roleSelect = $('#roleSelect');
        const prodiGroup = $('#prodiGroup');
        const prodiSelect = $('#prodiSelect');
        const usernameInput = $('#usernameInput');
        const usernameHelp = $('#usernameHelp');
        const passwordHelp = $('#passwordHelp');

        function updateForm() {
            const role = roleSelect.val();

            if (role === 'admin_prodi') {
                // Show prodi selector
                prodiGroup.show();
                prodiSelect.prop('required', true);

                // Auto-fill username from prodi
                usernameInput.prop('readonly', true);
                usernameHelp.html('<i class="fas fa-lock"></i> Username otomatis = Kode Prodi');

                // Update username when prodi changes
                const selectedProdi = prodiSelect.val();
                if (selectedProdi) {
                    usernameInput.val(selectedProdi);
                <?php if (!$isEdit): ?>
                            passwordHelp.html('Password default: gunakan kode prodi yang sama dengan username');
                <?php endif; ?>
            }
            } else {
                // Hide prodi selector
                prodiGroup.hide();
                prodiSelect.prop('required', false);

                // Manual username
                usernameInput.prop('readonly', false);
                usernameHelp.html('Username untuk login');

                // Clear prodi if not admin_prodi
                if (role !== 'admin_prodi') {
                    prodiSelect.val('');
                }
            }
        }

        // Update on role change
        roleSelect.on('change', updateForm);

        // Update on prodi change
        prodiSelect.on('change', function () {
            if (roleSelect.val() === 'admin_prodi') {
                usernameInput.val($(this).val());
            }
        });

        // Initialize on page load
        updateForm();
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>