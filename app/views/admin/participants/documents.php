<?php
ob_start();
$isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);
?>

<style>
    .doc-manage-card {
        border-radius: 1rem;
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .doc-manage-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .doc-manage-card.has-doc {
        border-color: #10b981;
    }

    .doc-manage-card.no-doc {
        border-color: #f59e0b;
    }

    .preview-container {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-gradient {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="header-gradient text-white p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 font-weight-bold">
                        <i class="fas fa-folder-open mr-2"></i>Kelola Dokumen Peserta
                    </h4>
                    <p class="mb-0 opacity-75">
                        <?= htmlspecialchars($p['nama_lengkap']) ?> |
                        <?= htmlspecialchars($p['nomor_peserta'] ?? 'Belum ada no peserta') ?>
                    </p>
                </div>
                <div>
                    <a href="/admin/participants/view/<?= $p['id'] ?>" class="btn btn-light">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Info -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h5 mb-0 text-primary">
                        <?= htmlspecialchars($p['email']) ?>
                    </div>
                    <small class="text-muted">Email</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h5 mb-0">
                        <?= htmlspecialchars($p['nama_prodi'] ?? '-') ?>
                    </div>
                    <small class="text-muted">Program Studi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <span
                        class="badge badge-<?= $p['status_berkas'] === 'lulus' ? 'success' : ($p['status_berkas'] === 'gagal' ? 'danger' : 'warning') ?> py-2 px-3">
                        <?= strtoupper($p['status_berkas'] ?? 'pending') ?>
                    </span>
                    <div><small class="text-muted">Status Berkas</small></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <span class="badge badge-<?= $p['status_pembayaran'] ? 'success' : 'secondary' ?> py-2 px-3">
                        <?= $p['status_pembayaran'] ? 'LUNAS' : 'BELUM' ?>
                    </span>
                    <div><small class="text-muted">Pembayaran</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Grid -->
    <div class="row">
        <?php
        $docTypes = [
            ['type' => 'foto', 'label' => 'Foto Peserta', 'field' => 'photo_filename', 'accept' => 'image/jpeg,image/png', 'icon' => 'camera', 'info' => 'JPG/PNG, maks 2MB', 'isImage' => true],
            ['type' => 'ktp', 'label' => 'KTP', 'field' => 'ktp_filename', 'accept' => 'image/jpeg,image/png', 'icon' => 'id-card', 'info' => 'JPG/PNG, maks 5MB', 'isImage' => true],
            ['type' => 'ijazah', 'label' => 'Ijazah S1', 'field' => 'ijazah_filename', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'info' => 'JPG/PNG, maks 5MB', 'isImage' => true],
            ['type' => 'transkrip', 'label' => 'Transkrip S1', 'field' => 'transkrip_filename', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'info' => 'PDF, maks 10MB', 'isImage' => false],
        ];
        if ($isS3) {
            $docTypes[] = ['type' => 'ijazah_s2', 'label' => 'Ijazah S2', 'field' => 'ijazah_s2_filename', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'info' => 'JPG/PNG, maks 5MB', 'isImage' => true];
            $docTypes[] = ['type' => 'transkrip_s2', 'label' => 'Transkrip S2', 'field' => 'transkrip_s2_filename', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'info' => 'PDF, maks 10MB', 'isImage' => false];
        }

        foreach ($docTypes as $doc):
            $dbFile = $p[$doc['field']] ?? '';
            $hasDoc = !empty($dbFile);
            $docUrl = '';

            if ($hasDoc) {
                // Smart Path Detection
                if (strpos($dbFile, 'photos/') !== false || strpos($dbFile, 'documents/') !== false) {
                    // New Structure: 20241/photos/file.jpg
                    $docUrl = '/storage/' . $dbFile;
                } else {
                    // Legacy Structure
                    if ($doc['type'] === 'foto') {
                        $docUrl = '/storage/photos/' . $dbFile;
                    } else {
                        // Legacy Doc: storage/documents/type/semester/file
                        // But wait, $dbFile usually was just 'semester/file' in old uploads?
                        // Or did old uploads store 'type/semester/file'?
                        // Checked ParticipantController old logic: $targetDir = $cfg['folder'] . '/' . $subfolder; 
                        // $dbPath = $subfolder . '/' . $newFilename;
                        // So it was 'semester/filename' relative to specific type folder.
                        $docUrl = '/storage/documents/' . $doc['type'] . '/' . $dbFile;
                    }
                }
            }
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card doc-manage-card shadow-sm <?= $hasDoc ? 'has-doc' : 'no-doc' ?>">
                    <div class="card-header py-3 <?= $hasDoc ? 'bg-success text-white' : 'bg-warning' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-<?= $doc['icon'] ?> mr-2"></i>
                                <?= $doc['label'] ?>
                            </h6>
                            <?php if ($hasDoc): ?>
                                <span class="badge badge-light"><i class="fas fa-check"></i> Ada</span>
                            <?php else: ?>
                                <span class="badge badge-dark"><i class="fas fa-times"></i> Belum</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Preview -->
                        <div class="preview-container mb-3">
                            <?php if ($hasDoc): ?>
                                <?php if ($doc['isImage']): ?>
                                    <img src="<?= $docUrl ?>" alt="<?= $doc['label'] ?>" class="img-fluid rounded"
                                        style="max-height: 180px;">
                                <?php else: ?>
                                    <div class="text-center">
                                        <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
                                        <div><a href="<?= $docUrl ?>" target="_blank" class="btn btn-sm btn-outline-danger">Lihat
                                                PDF</a></div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-<?= $doc['icon'] ?> fa-4x mb-2 opacity-25"></i>
                                    <p class="mb-0">Belum ada dokumen</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <small><i class="fas fa-info-circle mr-1"></i>
                                <?= $doc['info'] ?>
                            </small>
                        </div>

                        <!-- Actions -->
                        <div class="text-center">
                            <a href="/admin/document-helper" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Kelola di Helper
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const participantId = <?= $p['id'] ?>;


</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>