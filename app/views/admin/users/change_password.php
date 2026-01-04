<?php ob_start(); ?>

<!-- Alert Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> Password berhasil diubah!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i>
        <?php if ($_GET['error'] === 'empty_fields'): ?>
            Semua field wajib diisi!
        <?php elseif ($_GET['error'] === 'password_mismatch'): ?>
            Password baru dan konfirmasi tidak cocok!
        <?php elseif ($_GET['error'] === 'wrong_password'): ?>
            Password lama salah!
        <?php elseif ($_GET['error'] === 'password_too_short'): ?>
            Password minimal 6 karakter!
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key"></i> Ganti Password</h3>
            </div>
            <form action="/admin/change-password" method="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Password Lama <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control"
                            placeholder="Masukkan password lama" required>
                    </div>

                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" id="newPassword" class="form-control"
                            placeholder="Masukkan password baru (min 6 karakter)" required>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control"
                            placeholder="Ulangi password baru" required>
                        <small id="passwordMatch" class="form-text"></small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Password Baru
                    </button>
                    <a href="/admin" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="card-body">
                <p><strong>Ketentuan Password:</strong></p>
                <ul>
                    <li>Minimal 6 karakter</li>
                    <li>Gunakan kombinasi huruf dan angka untuk keamanan lebih baik</li>
                    <li>Jangan gunakan password yang mudah ditebak</li>
                </ul>

                <p class="text-muted mt-3">
                    <i class="fas fa-shield-alt"></i> Password akan di-enkripsi dengan aman di database
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const newPassword = $('#newPassword');
        const confirmPassword = $('#confirmPassword');
        const passwordMatch = $('#passwordMatch');

        function checkPasswordMatch() {
            if (confirmPassword.val() === '') {
                passwordMatch.html('');
                return;
            }

            if (newPassword.val() === confirmPassword.val()) {
                passwordMatch.html('<span class="text-success"><i class="fas fa-check"></i> Password cocok</span>');
            } else {
                passwordMatch.html('<span class="text-danger"><i class="fas fa-times"></i> Password tidak cocok</span>');
            }
        }

        newPassword.on('keyup', checkPasswordMatch);
        confirmPassword.on('keyup', checkPasswordMatch);
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>