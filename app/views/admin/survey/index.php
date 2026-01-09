<?php ob_start(); ?>
<?php $title = 'Manajemen SKM'; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Survei & Evaluasi</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Judul Survei</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Jumlah Responden</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($surveys as $s): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($s['title']); ?>
                                    </strong><br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($s['description']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($s['target_role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($s['is_active']): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $s['response_count']; ?> Responden
                                </td>
                                <td>
                                    <a href="/admin/surveys/edit/<?php echo $s['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-cog"></i> Atur
                                    </a>
                                    <a href="/survey/<?php echo $s['id']; ?>" target="_blank"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-link"></i> Link
                                    </a>
                                    <a href="/admin/surveys/report/<?php echo $s['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-chart-bar"></i> Laporan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>