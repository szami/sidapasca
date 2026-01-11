<?php ob_start(); ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Database Migration</h1>
                <a href="/admin/tools" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke System Tools
                </a>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                    <li class="breadcrumb-item"><a href="/admin/tools">System Tools</a></li>
                    <li class="breadcrumb-item active">Migration</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Database Schema Migration</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Module ini membandingkan tabel yang ada di database dengan
                    definisi skema yang diharapkan.
                </div>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status as $table => $state): ?>
                            <tr>
                                <td>
                                    <?= $table ?>
                                </td>
                                <td>
                                    <?php if ($state === 'OK'): ?>
                                        <span class="badge badge-success">OK</span>
                                    <?php elseif ($state === 'DATA_MISMATCH'): ?>
                                        <span class="badge badge-warning">DATA MISMATCH</span>
                                    <?php elseif ($state === 'EXISTS'): ?>
                                        <span class="badge badge-success">EXISTS</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">MISSING</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($state === 'MISSING'): ?>
                                        <button class="btn btn-sm btn-primary btn-sync" data-table="<?= $table ?>">
                                            <i class="fas fa-sync"></i> Create & Seed
                                        </button>
                                    <?php elseif ($state === 'DATA_MISMATCH'): ?>
                                        <button class="btn btn-sm btn-warning btn-sync" data-table="<?= $table ?>">
                                            <i class="fas fa-database"></i> Update Data
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-check"></i> Latest</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title text-dark"><i class="fas fa-wrench" style="margin-right: 8px;"></i> Available
                    Patches / Fixes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Script Name</th>
                            <th>Path</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patches)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No patches available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patches as $patch): ?>
                                <tr>
                                    <td class="font-weight-bold">
                                        <?= htmlspecialchars($patch['filename']) ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($patch['path']) ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-dark btn-run-patch"
                                            data-filename="<?= htmlspecialchars($patch['filename']) ?>">
                                            <i class="fas fa-play"></i> Run Patch
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.btn-sync', function () {
        let btn = $(this);
        let table = btn.data('table');

        if (!confirm('Create table ' + table + '?')) return;

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Syncing...');

        $.post('/admin/tools/migration/sync', { table: table }, function (res) {
            if (res.success) {
                alert(res.message);
                location.reload();
            } else {
                alert('Error: ' + res.message);
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Sync (Create Table)');
            }
        });
    });

    $(document).on('click', '.btn-run-patch', function () {
        let btn = $(this);
        let filename = btn.data('filename');

        if (!confirm('Run patch script: ' + filename + '?\nThis action cannot be undone.')) return;

        let originalContent = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Running...');

        $.post('/admin/tools/migration/patch', { filename: filename }, function (res) {
            btn.prop('disabled', false).html(originalContent);

            if (res.success) {
                // Show output in a nice modal or alert, here just alert for simplicity as requested
                Swal.fire({
                    title: 'Patch Executed',
                    text: res.message, // Output from script
                    icon: 'success',
                    width: '600px'
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: res.message,
                    icon: 'error'
                });
            }
        }).fail(function () {
            btn.prop('disabled', false).html(originalContent);
            Swal.fire('Error', 'Network error occurred.', 'error');
        });
    });
</script>

<?php
$content = ob_get_clean();
$title = "Database Migration";
include __DIR__ . '/../../layouts/admin.php';
?>