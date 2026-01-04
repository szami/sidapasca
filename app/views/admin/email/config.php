<?php ob_start(); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog mr-2"></i> Konfigurasi Email SMTP</h3>
            </div>

            <form action="/admin/email/config/save" method="POST">
                <div class="card-body">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i> Konfigurasi email berhasil disimpan!
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Metode Pengiriman</label>
                        <select name="driver" id="driverSelect" class="form-control">
                            <option value="smtp" <?php echo ($config['driver'] ?? 'smtp') == 'smtp' ? 'selected' : ''; ?>>
                                SMTP (Standar)</option>
                            <option value="gas" <?php echo ($config['driver'] ?? '') == 'gas' ? 'selected' : ''; ?>>Google
                                Apps Script (Webhook)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>From Email *</label>
                        <input type="email" name="from_email" class="form-control"
                            value="<?php echo $config['from_email'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>From Name *</label>
                        <input type="text" name="from_name" class="form-control"
                            value="<?php echo $config['from_name'] ?? 'PMB PPs ULM'; ?>" required>
                    </div>

                    <hr>

                    <div id="gasConfig"
                        class="form-group <?php echo ($config['driver'] ?? 'smtp') == 'gas' ? '' : 'd-none'; ?>">
                        <label>Script URL (Webhook) *</label>
                        <input type="url" name="api_url" class="form-control"
                            value="<?php echo $config['api_url'] ?? ''; ?>"
                            placeholder="https://script.google.com/macros/s/...">
                        <small class="text-muted">Masukkan URL Web App dari Google Apps Script yang telah
                            dideploy.</small>
                    </div>

                    <div id="smtpConfig" class="<?php echo ($config['driver'] ?? 'smtp') == 'smtp' ? '' : 'd-none'; ?>">
                        <div class="form-group">
                            <label>SMTP Host *</label>
                            <input type="text" name="smtp_host" class="form-control"
                                value="<?php echo $config['smtp_host'] ?? 'smtp.gmail.com'; ?>" required>
                            <small class="text-muted">Contoh: smtp.gmail.com, smtp.mail.yahoo.com</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>SMTP Port *</label>
                                    <input type="number" name="smtp_port" class="form-control"
                                        value="<?php echo $config['smtp_port'] ?? 587; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Encryption *</label>
                                    <select name="smtp_encryption" class="form-control" required>
                                        <option value="tls" <?php echo ($config['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($config['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>SMTP Username (Email) *</label>
                            <input type="text" name="smtp_username" class="form-control"
                                value="<?php echo $config['smtp_username'] ?? ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label>SMTP Password
                                <?php echo $config ? '' : '*'; ?>
                            </label>
                            <input type="password" name="smtp_password" class="form-control"
                                placeholder="<?php echo $config ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan password'; ?>"
                                <?php echo $config ? '' : 'required'; ?>>
                        </div>
                    </div> <!-- End smtpConfig -->
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Konfigurasi
                    </button>
                    <button type="button" id="testConnectionBtn" class="btn btn-info">
                        <i class="fas fa-plug mr-1"></i> Test Koneksi
                    </button>

                    <!-- Log Area -->
                    <div id="testLog" class="mt-3 p-3 bg-dark rounded d-none"
                        style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.85rem; white-space: pre-wrap;">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#testConnectionBtn').click(function () {
        var btn = $(this);
        var logArea = $('#testLog');

        // Reset and show log
        logArea.removeClass('d-none').html('<span class="text-info">> Menginisialisasi tes koneksi...</span>\n');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Testing...');

        $.post('/admin/email/config/test', {
            smtp_host: $('input[name="smtp_host"]').val(),
            smtp_port: $('input[name="smtp_port"]').val(),
            smtp_username: $('input[name="smtp_username"]').val(),
            smtp_password: $('input[name="smtp_password"]').val(),
            smtp_host: $('input[name="smtp_host"]').val(),
            smtp_port: $('input[name="smtp_port"]').val(),
            smtp_username: $('input[name="smtp_username"]').val(),
            smtp_password: $('input[name="smtp_password"]').val(),
            smtp_encryption: $('select[name="smtp_encryption"]').val(),
            driver: $('#driverSelect').val(),
            api_url: $('input[name="api_url"]').val()
        }, function (response) {
            if (response.success) {
                toastr.success('Koneksi berhasil!');
                logArea.append('<span class="text-success">> ' + response.message + '</span>\n');
            } else {
                toastr.error('Koneksi gagal');
                logArea.append('<span class="text-danger">> ' + response.message + '</span>\n');
            }
        }, 'json')
            .fail(function (xhr, status, error) {
                toastr.error('Terjadi kesalahan sistem');
                logArea.append('<span class="text-danger">> SYSTEM ERROR: ' + error + '</span>\n');
                logArea.append('<span class="text-muted">' + xhr.responseText + '</span>\n');
            })
            .always(function () {
                btn.prop('disabled', false).html('<i class="fas fa-plug mr-1"></i> Test Koneksi');
                logArea.append('<span class="text-muted">> Selesai.</span>');
                // Scroll to bottom
                logArea.scrollTop(logArea[0].scrollHeight);
            });
    });

    // Toggle logic
    $('#driverSelect').change(function () {
        if ($(this).val() == 'gas') {
            $('#smtpConfig').addClass('d-none');
            $('#gasConfig').removeClass('d-none');
        } else {
            $('#gasConfig').addClass('d-none');
            $('#smtpConfig').removeClass('d-none');
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>