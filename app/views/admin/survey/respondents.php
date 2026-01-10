<?php ob_start(); ?>
<?php $title = 'Responden: ' . htmlspecialchars($survey['title']); ?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i> Responden Survei
                </h3>
                <div class="card-tools">
                    <a href="/admin/surveys" class="btn btn-tool" title="Kembali">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="callout callout-info mb-4">
                    <h5>
                        <?php echo htmlspecialchars($survey['title']); ?>
                    </h5>
                    <p>
                        <?php echo htmlspecialchars($survey['description']); ?>
                    </p>
                </div>

                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Tanggal Kirim</th>
                            <th>Identitas</th>
                            <th>Tipe Responden</th>
                            <th>Saran/Masukan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($responses as $r): ?>
                            <tr>
                                <td>
                                    <?php echo $i++; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($r['submitted_at'])); ?>
                                </td>
                                <td>
                                    <?php if ($r['respondent_type'] == 'participant' && !empty($r['nama_lengkap'])): ?>
                                        <strong>
                                            <?php echo htmlspecialchars($r['nama_lengkap']); ?>
                                        </strong><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($r['nomor_peserta'] ?? '-'); ?>
                                        </small>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($r['respondent_identifier']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        class="badge badge-<?php echo ($r['respondent_type'] == 'participant' ? 'primary' : 'secondary'); ?>">
                                        <?php echo ucfirst($r['respondent_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($r['suggestion'])): ?>
                                        <?php echo htmlspecialchars($r['suggestion']); ?>
                                    <?php else: ?>
                                        <em class="text-muted">- Tidak ada saran -</em>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/surveys/response/<?php echo $r['id']; ?>" class="btn btn-xs btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    
                                    <form action="/admin/surveys/response/delete/<?php echo $r['id']; ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data responden ini? Data jawaban akan hilang permanen dan pengguna (jika peserta) harus mengisi ulang.');">
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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