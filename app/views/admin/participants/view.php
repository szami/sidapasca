<?php ob_start(); ?>
<style>
    .premium-view-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .data-row {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .data-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .data-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .data-value {
        font-size: 1.1rem;
        color: #2d3748;
        font-weight: 500;
    }

    .section-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        margin: 30px 0 20px 0;
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .doc-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .doc-card:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .doc-preview {
        width: 100%;
        height: 300px;
        object-fit: contain;
        background: #f8f9fa;
    }

    .badge-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="premium-view-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-2">
                    <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                </h2>
                <div class="d-flex gap-3 align-items-center">
                    <span><i class="fas fa-envelope mr-2"></i>
                        <?php echo htmlspecialchars($p['email']); ?>
                    </span>
                    <?php if (!empty($p['nomor_peserta'])): ?>
                        <span><i class="fas fa-id-badge mr-2"></i>
                            <?php echo htmlspecialchars($p['nomor_peserta']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <?php if ((\App\Utils\RoleHelper::isSuperadmin())): ?>
                    <a href="/admin/participants/edit/<?php echo $p['id']; ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-edit mr-2"></i>Edit Data
                    </a>
                <?php endif; ?>
                <a href="/admin/participants" class="btn btn-outline-light btn-lg ml-2">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Biodata -->
    <div class="section-header">
        <i class="fas fa-user-circle"></i>
        <span>Data Pribadi</span>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Tempat Lahir</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['tempat_lahir'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Tanggal Lahir</div>
                <div class="data-value">
                    <?php echo $p['tgl_lahir'] ? date('d F Y', strtotime($p['tgl_lahir'])) : '-'; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Jenis Kelamin</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['jenis_kelamin'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Agama</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['agama'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Status Pernikahan</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['status_pernikahan'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">No. HP</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['no_hp'] ?? '-'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alamat -->
    <div class="section-header">
        <i class="fas fa-map-marker-alt"></i>
        <span>Alamat</span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="data-row">
                <div class="data-label">Alamat KTP</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['alamat_ktp'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="data-row">
                <div class="data-label">Kecamatan</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['kecamatan'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="data-row">
                <div class="data-label">Kota/Kabupaten</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['kota'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="data-row">
                <div class="data-label">Provinsi</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['provinsi'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="data-row">
                <div class="data-label">Kode Pos</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['kode_pos'] ?? '-'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Info -->
    <div class="section-header">
        <i class="fas fa-graduation-cap"></i>
        <span>Informasi Akademik</span>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="data-row">
                <div class="data-label">Program Studi</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['nama_prodi'] ?? '-'); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="data-row">
                <div class="data-label">Kode Prodi</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['kode_prodi'] ?? '-'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents (Tabbed View-Only) -->
    <div class="section-header">
        <i class="fas fa-folder-open"></i>
        <span>Dokumen Peserta</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs" id="docTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab-foto">
                        <i class="fas fa-camera mr-1"></i> Foto
                        <?php if (!empty($p['photo_filename'])): ?><i
                                class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-ktp">
                        <i class="fas fa-id-card mr-1"></i> KTP
                        <?php if (!empty($p['ktp_filename'])): ?><i
                                class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-ijazah">
                        <i class="fas fa-graduation-cap mr-1"></i> Ijazah S1
                        <?php if (!empty($p['ijazah_filename'])): ?><i
                                class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-transkrip">
                        <i class="fas fa-file-alt mr-1"></i> Transkrip S1
                        <?php if (!empty($p['transkrip_filename'])): ?><i
                                class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                    </a>
                </li>
                <?php if (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false): ?>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-ijazah-s2">
                            <i class="fas fa-graduation-cap mr-1"></i> Ijazah S2
                            <?php if (!empty($p['ijazah_s2_filename'])): ?><i
                                    class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-transkrip-s2">
                            <i class="fas fa-file-alt mr-1"></i> Transkrip S2
                            <?php if (!empty($p['transkrip_s2_filename'])): ?><i
                                    class="fas fa-check-circle text-success ml-1"></i><?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-4">
                <!-- Foto Tab -->
                <div class="tab-pane fade show active" id="tab-foto">
                    <div class="row">
                        <div class="col-md-8 text-center">
                            <?php if (!empty($p['photo_filename'])): ?>
                                <img src="/storage/photos/<?php echo $p['photo_filename']; ?>" alt="Foto" id="foto-preview"
                                    class="img-thumbnail" style="max-width:100%;max-height:500px;transition:transform 0.3s">
                                <div class="mt-3">
                                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>
                                        Tersedia</small>
                                </div>
                            <?php else: ?>
                                <div class="border p-5 rounded bg-light">
                                    <i class="fas fa-user-circle fa-5x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0"><strong>Belum ada foto</strong></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($p['photo_filename'])): ?>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tools mr-1"></i> Alat</h6>
                                    </div>
                                    <div class="card-body">
                                        <button type="button" class="btn btn-info btn-block"
                                            onclick="rotateImage('foto-preview')">
                                            <i class="fas fa-sync-alt mr-1"></i> Putar Foto
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- KTP Tab -->
                <div class="tab-pane fade" id="tab-ktp">
                    <div class="row">
                        <div class="col-md-10 text-center">
                            <?php if (!empty($p['ktp_filename'])): ?>
                                <img src="/storage/documents/ktp/<?php echo $p['ktp_filename']; ?>" alt="KTP"
                                    id="ktp-preview" class="img-thumbnail"
                                    style="max-width:100%;max-height:700px;transition:transform 0.3s">
                                <div class="mt-3">
                                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>
                                        Tersedia</small>
                                </div>
                            <?php else: ?>
                                <div class="border p-5 rounded bg-light">
                                    <i class="fas fa-id-card fa-5x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0"><strong>Belum ada KTP</strong></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <?php if (!empty($p['ktp_filename'])): ?>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tools mr-1"></i> Alat</h6>
                                    </div>
                                    <div class="card-body">
                                        <button type="button" class="btn btn-info btn-block"
                                            onclick="rotateImage('ktp-preview')">
                                            <i class="fas fa-sync-alt mr-1"></i> Putar
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Ijazah S1 Tab -->
                <div class="tab-pane fade" id="tab-ijazah">
                    <div class="row">
                        <div class="col-md-10 text-center">
                            <?php if (!empty($p['ijazah_filename'])): ?>
                                <img src="/storage/documents/ijazah/<?php echo $p['ijazah_filename']; ?>" alt="Ijazah"
                                    id="ijazah-preview" class="img-thumbnail"
                                    style="max-width:100%;max-height:700px;transition:transform 0.3s">
                                <div class="mt-3">
                                    <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>
                                        Tersedia</small>
                                </div>
                            <?php else: ?>
                                <div class="border p-5 rounded bg-light">
                                    <i class="fas fa-graduation-cap fa-5x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0"><strong>Belum ada Ijazah</strong></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <?php if (!empty($p['ijazah_filename'])): ?>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-tools mr-1"></i> Alat</h6>
                                    </div>
                                    <div class="card-body">
                                        <button type="button" class="btn btn-info btn-block"
                                            onclick="rotateImage('ijazah-preview')">
                                            <i class="fas fa-sync-alt mr-1"></i> Putar
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Transkrip S1 Tab -->
                <div class="tab-pane fade" id="tab-transkrip">
                    <?php if (!empty($p['transkrip_filename'])): ?>
                        <div class="text-center mb-3">
                            <h6 class="text-muted">
                                <i class="fas fa-file-pdf mr-1 text-danger"></i>
                                <?php echo htmlspecialchars($p['transkrip_filename']); ?>
                            </h6>
                            <a href="/storage/documents/transkrip/<?php echo $p['transkrip_filename']; ?>" target="_blank"
                                class="btn btn-sm btn-danger">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                            </a>
                        </div>
                        <div class="border rounded" style="height: 700px; overflow: auto;">
                            <iframe src="/storage/documents/transkrip/<?php echo $p['transkrip_filename']; ?>" width="100%"
                                height="100%" style="border: none;">
                            </iframe>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="border p-5 rounded bg-light">
                                <i class="fas fa-file-alt fa-5x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0"><strong>Belum ada Transkrip S1</strong></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ijazah S2 Tab (for S3 students) -->
                <?php if (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false): ?>
                    <div class="tab-pane fade" id="tab-ijazah-s2">
                        <div class="row">
                            <div class="col-md-10 text-center">
                                <?php if (!empty($p['ijazah_s2_filename'])): ?>
                                    <img src="/storage/documents/ijazah_s2/<?php echo $p['ijazah_s2_filename']; ?>"
                                        alt="Ijazah S2" id="ijazah-s2-preview" class="img-thumbnail"
                                        style="max-width:100%;max-height:700px;transition:transform 0.3s">
                                    <div class="mt-3">
                                        <small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>
                                            Tersedia</small>
                                    </div>
                                <?php else: ?>
                                    <div class="border p-5 rounded bg-light">
                                        <i class="fas fa-graduation-cap fa-5x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0"><strong>Belum ada Ijazah S2</strong></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <?php if (!empty($p['ijazah_s2_filename'])): ?>
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-tools mr-1"></i> Alat</h6>
                                        </div>
                                        <div class="card-body">
                                            <button type="button" class="btn btn-info btn-block"
                                                onclick="rotateImage('ijazah-s2-preview')">
                                                <i class="fas fa-sync-alt mr-1"></i> Putar
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Transkrip S2 Tab -->
                    <div class="tab-pane fade" id="tab-transkrip-s2">
                        <?php if (!empty($p['transkrip_s2_filename'])): ?>
                            <div class="text-center mb-3">
                                <h6 class="text-muted">
                                    <i class="fas fa-file-pdf mr-1 text-primary"></i>
                                    <?php echo htmlspecialchars($p['transkrip_s2_filename']); ?>
                                </h6>
                                <a href="/storage/documents/transkrip_s2/<?php echo $p['transkrip_s2_filename']; ?>"
                                    target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                                </a>
                            </div>
                            <div class="border rounded" style="height: 700px; overflow: auto;">
                                <iframe src="/storage/documents/transkrip_s2/<?php echo $p['transkrip_s2_filename']; ?>"
                                    width="100%" height="100%" style="border: none;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <div class="border p-5 rounded bg-light">
                                    <i class="fas fa-file-alt fa-5x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0"><strong>Belum ada Transkrip S2</strong></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Client-side rotation for viewing purposes
        let rotations = {};

        function rotateImage(imageId) {
            if (!rotations[imageId]) rotations[imageId] = 0;
            rotations[imageId] = (rotations[imageId] + 90) % 360;
            document.getElementById(imageId).style.transform = `rotate(${rotations[imageId]}deg)`;
        }
    </script>

    <!-- Status -->
    <div class="section-header">
        <i class="fas fa-info-circle"></i>
        <span>Status</span>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Status Berkas</div>
                <div class="data-value">
                    <span class="badge-status 
                        <?php if ($p['status_berkas'] === 'lulus')
                            echo 'bg-success';
                        elseif ($p['status_berkas'] === 'gagal')
                            echo 'bg-danger';
                        else
                            echo 'bg-warning'; ?>">
                        <?php echo strtoupper($p['status_berkas'] ?? 'PENDING'); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Status Pembayaran</div>
                <div class="data-value">
                    <span
                        class="badge-status <?php echo $p['status_pembayaran'] == 1 ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $p['status_pembayaran'] == 1 ? 'LUNAS' : 'BELUM'; ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="data-row">
                <div class="data-label">Nomor Billing</div>
                <div class="data-value">
                    <?php echo htmlspecialchars($p['no_billing'] ?? '-'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>