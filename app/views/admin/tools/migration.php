<?php ob_start(); ?>

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
                                    <?php if ($state === 'EXISTS'): ?>
                                        <span class="badge badge-success">EXISTS</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">MISSING</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($state === 'MISSING'): ?>
                                        <button class="btn btn-sm btn-primary btn-sync" data-table="<?= $table ?>">
                                            <i class="fas fa-sync"></i> Sync (Create Table)
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-check"></i> OK</span>
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
</script>

<?php
$content = ob_get_clean();
$title = "Database Migration";
include __DIR__ . '/../../layouts/admin.php';
?>