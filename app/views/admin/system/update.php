<?php ob_start(); ?>

<!-- Alert Messages -->
<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> <?php echo $_GET['message'] ?? 'Update berhasil dilakukan!'; ?>
        </div>
    <?php elseif ($_GET['status'] === 'no_update'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-info-circle"></i> <?php echo $_GET['message'] ?? 'Sistem sudah up-to-date'; ?>
        </div>
    <?php elseif ($_GET['status'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_GET['message'] ?? 'Update gagal!'; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row">
    <!-- Version Info Card -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Versi</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="40%"><strong>Versi Saat Ini:</strong></td>
                        <td><span
                                class="badge badge-primary badge-lg">v<?php echo $currentVersion['version'] ?? '1.0.0'; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Last Update:</strong></td>
                        <td><?php echo $currentVersion['updated_at'] ?? '-'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Git Branch:</strong></td>
                        <td>
                            <?php if ($gitStatus['available']): ?>
                                <code><?php echo $gitStatus['branch']; ?></code>
                            <?php else: ?>
                                <span class="text-muted">Git not available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Git Commit:</strong></td>
                        <td>
                            <?php if ($gitStatus['available']): ?>
                                <code><?php echo $gitStatus['commit']; ?></code>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Repository:</strong></td>
                        <td>
                            <?php if ($remoteUrl): ?>
                                <small><a href="<?php echo $remoteUrl; ?>"
                                        target="_blank"><?php echo $remoteUrl; ?></a></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- System Requirements Card -->
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> System Requirements</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="60%">Git Installed</td>
                        <td>
                            <?php if ($requirements['git_installed']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>PHP exec() Enabled</td>
                        <td>
                            <?php if ($requirements['exec_enabled']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Version File Writable</td>
                        <td>
                            <?php if ($requirements['version_file_writable']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation"></i> No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Backup Dir Writable</td>
                        <td>
                            <?php if ($requirements['backup_dir_writable']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation"></i> No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Working Directory Clean</td>
                        <td>
                            <?php if ($gitStatus['clean']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Clean</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation"></i> Has Changes</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Deployment Tool Information Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-rocket"></i> Deployment & Synchronization Tool</h3>
            </div>
            <div class="card-body">
                <p>
                    Anda dapat menyinkronkan kode antara folder <strong>devsida</strong> dan
                    <strong>sidapasca-ulm</strong> di host yang sama menggunakan script otomasi ini.
                    Script ini akan menjalankan perintah <code>git pull</code> dan <code>migration</code> database
                    secara aman.
                </p>
                <div class="alert alert-light border">
                    <strong>URL Deployment:</strong><br>
                    <code
                        class="text-primary"><?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/deploy.php?token=sidapasca_deploy_2026_xyz</code>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Langkah Sinkronisasi:</h6>
                        <ol class="small">
                            <li>Lakukan <strong>Git Push</strong> dari komputer lokal (atau server dev).</li>
                            <li>Buka URL di atas melalui browser (atau trigger via cron/webhook).</li>
                            <li>Aplikasi di folder ini akan otomatis menarik kode terbaru dari GitHub.</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning py-2 mb-0">
                            <strong><i class="fas fa-exclamation-triangle"></i> Keamanan:</strong><br>
                            <small>Sangat disarankan untuk mengubah <code>DEPLOY_TOKEN</code> di file
                                <code>deploy.php</code> agar hanya Anda yang bisa men-trigger update ini.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sync-alt"></i> Update System</h3>
            </div>
            <div class="card-body">
                <div id="update-status-area">
                    <p class="mb-3">
                        Klik tombol "Check for Updates" untuk memeriksa apakah ada versi baru dari GitHub.
                        Sistem akan otomatis membuat backup database sebelum melakukan update.
                    </p>

                    <!-- Check Update Button -->
                    <button type="button" id="btn-check-update" class="btn btn-info">
                        <i class="fas fa-search"></i> Check for Updates
                    </button>

                    <!-- Loading Spinner (Hidden by default) -->
                    <div id="loading-spinner" class="mt-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <span class="ml-2">Checking for updates...</span>
                    </div>

                    <!-- Update Available Section (Hidden by default) -->
                    <div id="update-available" class="mt-3 alert alert-success" style="display: none;">
                        <h5><i class="fas fa-download"></i> Update Tersedia!</h5>
                        <p id="update-info"></p>

                        <form action="/admin/system/perform-update" method="POST" id="form-update">
                            <button type="submit" class="btn btn-success btn-lg"
                                onclick="return confirm('Apakah Anda yakin ingin melakukan update? Database akan di-backup otomatis.')">
                                <i class="fas fa-cloud-download-alt"></i> Update Now
                            </button>
                        </form>
                    </div>

                    <!-- No Update Section (Hidden by default) -->
                    <div id="no-update" class="mt-3 alert alert-info" style="display: none;">
                        <i class="fas fa-check-circle"></i> Sistem sudah menggunakan versi terbaru. Tidak ada update
                        yang tersedia.
                    </div>

                    <!-- Error Section (Hidden by default) -->
                    <div id="update-error" class="mt-3 alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> <span id="error-message"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update History Card -->
<?php if (!empty($updateHistory)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Update History</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th width="15%">Date</th>
                                <th width="15%">From Version</th>
                                <th width="15%">To Version</th>
                                <th width="10%">Status</th>
                                <th>Message</th>
                                <th width="15%">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($updateHistory as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?></td>
                                    <td><code><?php echo $log['version_from']; ?></code></td>
                                    <td><code><?php echo $log['version_to']; ?></code></td>
                                    <td>
                                        <?php if ($log['status'] === 'success'): ?>
                                            <span class="badge badge-success">Success</span>
                                        <?php elseif ($log['status'] === 'no_update'): ?>
                                            <span class="badge badge-info">No Update</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($log['message']); ?></small></td>
                                    <td><?php echo $log['username'] ?? 'System'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript for AJAX Check -->
<script>
    $(document).ready(function () {
        $('#btn-check-update').on('click', function () {
            // Hide all status divs
            $('#update-available, #no-update, #update-error').hide();

            // Show loading
            $('#loading-spinner').show();
            $(this).prop('disabled', true);

            // AJAX call to check for updates
            $.ajax({
                url: '/admin/system/check-update',
                method: 'GET',
                success: function (response) {
                    $('#loading-spinner').hide();
                    $('#btn-check-update').prop('disabled', false);

                    if (response.success && response.data) {
                        const data = response.data;

                        if (data.error) {
                            // Show error
                            $('#error-message').text(data.message);
                            $('#update-error').show();
                        } else if (data.has_update) {
                            // Show update available
                            const infoText = `Versi baru tersedia! (${data.latest_commit || 'latest'})`;
                            $('#update-info').html(infoText);
                            $('#update-available').show();
                        } else {
                            // No update
                            $('#no-update').show();
                        }
                    } else {
                        $('#error-message').text('Gagal memeriksa update. Silakan coba lagi.');
                        $('#update-error').show();
                    }
                },
                error: function () {
                    $('#loading-spinner').hide();
                    $('#btn-check-update').prop('disabled', false);
                    $('#error-message').text('Koneksi error. Silakan coba lagi.');
                    $('#update-error').show();
                }
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>