<?php ob_start();
$isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);
$canUpload = \App\Utils\RoleHelper::canUploadDocuments();
$docs = [
    ['type' => 'foto', 'label' => 'Foto Peserta', 'field' => 'photo_filename', 'legacy_path' => '/storage/photos/', 'accept' => 'image/jpeg,image/png', 'icon' => 'camera', 'isImage' => true],
    ['type' => 'ktp', 'label' => 'KTP', 'field' => 'ktp_filename', 'legacy_path' => '/storage/documents/ktp/', 'accept' => 'image/jpeg,image/png', 'icon' => 'id-card', 'isImage' => true],
    ['type' => 'ijazah', 'label' => 'Ijazah S1', 'field' => 'ijazah_filename', 'legacy_path' => '/storage/documents/ijazah/', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'isImage' => true],
    ['type' => 'transkrip', 'label' => 'Transkrip S1', 'field' => 'transkrip_filename', 'legacy_path' => '/storage/documents/transkrip/', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'isImage' => false],
];
if ($isS3) {
    $docs[] = ['type' => 'ijazah_s2', 'label' => 'Ijazah S2', 'field' => 'ijazah_s2_filename', 'legacy_path' => '/storage/documents/ijazah_s2/', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'isImage' => true];
    $docs[] = ['type' => 'transkrip_s2', 'label' => 'Transkrip S2', 'field' => 'transkrip_s2_filename', 'legacy_path' => '/storage/documents/transkrip_s2/', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'isImage' => false];
}
// Add rekomendasi only if file exists
if (!empty($p['rekomendasi_filename'])) {
    $docs[] = ['type' => 'rekomendasi', 'label' => 'Rekomendasi', 'field' => 'rekomendasi_filename', 'legacy_path' => '/storage/documents/rekomendasi/', 'accept' => 'application/pdf', 'icon' => 'file-signature', 'isImage' => false];
}
// Resolve Photo URL
$photoVal = $p['photo_filename'] ?? '';
if (!empty($photoVal)) {
    if (strpos($photoVal, 'photos/') !== false) {
        $photoUrl = '/storage/' . $photoVal;
    } else {
        $photoUrl = '/storage/photos/' . $photoVal;
    }
} else {
    $photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($p['nama_lengkap']) . '&background=random&size=150';
}
?>
<style>
    .profile-sidebar {
        position: sticky;
        top: 20px;
    }

    .profile-user-img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .status-badge-lg {
        font-size: 0.8rem;
        padding: 10px 15px;
        border-radius: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        width: 100%;
        display: block;
        text-align: center;
        margin-bottom: 10px;
    }

    .nav-tabs-premium {
        border-bottom: 2px solid #e9ecef;
    }

    .nav-tabs-premium .nav-link {
        border: none;
        font-weight: 600;
        color: #6c757d;
        padding: 15px 25px;
        font-size: 1rem;
        position: relative;
        transition: all 0.3s;
    }

    .nav-tabs-premium .nav-link.active {
        color: #007bff;
        background: transparent;
    }

    .nav-tabs-premium .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #007bff;
        border-radius: 3px 3px 0 0;
    }

    .nav-tabs-premium .nav-link:hover {
        color: #0056b3;
    }

    .data-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        margin-bottom: 20px;
        border: 1px solid #f1f3f5;
        overflow: hidden;
    }

    .data-card-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 1px solid #f1f3f5;
        font-weight: 700;
        color: #495057;
        display: flex;
        align-items: center;
    }

    .data-card-body {
        padding: 20px;
    }

    .info-group {
        margin-bottom: 20px;
    }

    .info-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #adb5bd;
        text-transform: uppercase;
        margin-bottom: 5px;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1.05rem;
        color: #343a40;
        font-weight: 500;
        border-bottom: 1px dashed #e9ecef;
        padding-bottom: 5px;
    }

    .doc-nav-pills .nav-link {
        text-align: left;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 8px;
        color: #495057;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .doc-nav-pills .nav-link:hover {
        background: #f8f9fa;
    }

    .doc-nav-pills .nav-link.active {
        background: #e7f1ff;
        color: #007bff;
        border-color: #b8daff;
        font-weight: 600;
    }

    .doc-nav-pills .nav-link i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
    }

    .doc-viewer {
        background: #343a40;
        border-radius: 12px;
        padding: 15px;
        min-height: 550px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .doc-viewer img {
        max-height: 520px;
        max-width: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        border-radius: 4px;
    }

    .doc-viewer iframe {
        width: 100%;
        height: 520px;
        border: none;
        background: white;
        border-radius: 4px;
    }

    .doc-empty {
        color: rgba(255, 255, 255, 0.3);
        text-align: center;
    }

    .btn-action-group .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
    }

    /* PDF.js Viewer Styles */
    .pdf-container {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #525252;
        border-radius: 4px;
        overflow: hidden;
    }

    .pdf-toolbar {
        background: #323232;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: white;
        flex-shrink: 0;
    }

    .pdf-toolbar button {
        background: #4a4a4a;
        border: none;
        color: white;
        padding: 4px 12px;
        cursor: pointer;
        border-radius: 3px;
        font-size: 12px;
    }

    .pdf-toolbar button:hover:not(:disabled) {
        background: #5a5a5a;
    }

    .pdf-toolbar button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pdf-page-info {
        color: white;
        font-size: 12px;
        min-width: 60px;
        text-align: center;
    }

    .pdf-canvas-wrapper {
        flex: 1;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 10px;
        background: #525252;
    }

    .pdf-canvas {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        background: white;
        display: block;
        user-select: none;
    }
</style>

<div class="container-fluid pb-5 pt-3">

    <div class="row">
        <!-- LEFT SIDEBAR -->
        <div class="col-lg-3">
            <div class="profile-sidebar">
                <!-- Profile Card -->
                <div class="card data-card text-center pb-3">
                    <div class="card-body">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?= $photoUrl ?>" class="profile-user-img rounded-circle" alt="User Image">
                        </div>
                        <h5 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($p['nama_lengkap']) ?></h5>
                        <p class="text-muted small mb-3">
                            <?= htmlspecialchars($p['nomor_peserta'] ?? 'Calon Mahasiswa') ?>
                        </p>

                        <div class="px-3">
                            <div class="status-badge-lg"
                                style="background: <?= $p['status_berkas'] === 'lulus' ? '#d1e7dd' : ($p['status_berkas'] === 'gagal' ? '#f8d7da' : '#fff3cd') ?>; color: <?= $p['status_berkas'] === 'lulus' ? '#0f5132' : ($p['status_berkas'] === 'gagal' ? '#842029' : '#664d03') ?>">
                                Berkas: <?= strtoupper($p['status_berkas'] ?? 'PENDING') ?>
                            </div>
                            <div class="status-badge-lg"
                                style="background: <?= $p['status_pembayaran'] ? '#cfe2ff' : '#e2e3e5' ?>; color: <?= $p['status_pembayaran'] ? '#084298' : '#41464b' ?>">
                                <?= $p['status_pembayaran'] ? 'PEMBAYARAN LUNAS' : 'BELUM BAYAR' ?>
                            </div>
                        </div>

                        <hr>

                        <div class="text-left px-2">
                            <div class="mb-2">
                                <small class="text-muted d-block">PRODI</small>
                                <span
                                    class="font-weight-bold text-dark"><?= htmlspecialchars($p['nama_prodi'] ?? '-') ?></span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">EMAIL</small>
                                <span class="text-dark"><?= htmlspecialchars($p['email']) ?></span>
                            </div>
                        </div>

                        <?php if (\App\Utils\RoleHelper::isSuperadmin()): ?>
                            <hr>
                            <a href="/admin/participants/edit/<?= $p['id'] ?>"
                                class="btn btn-primary btn-block rounded-pill">
                                <i class="fas fa-edit mr-2"></i> Edit Data
                            </a>
                        <?php endif; ?>

                        <a href="/admin/participants" class="btn btn-outline-secondary btn-block rounded-pill mt-2">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="col-lg-9">

            <div class="card border-0 shadow-none bg-transparent">
                <div class="card-header p-0 border-0 bg-transparent mb-3">
                    <ul class="nav nav-tabs nav-tabs-premium" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-biodata" role="tab"><i
                                    class="fas fa-user-circle mr-2"></i>Data Peserta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-dokumen" role="tab"><i
                                    class="fas fa-folder-open mr-2"></i>Dokumen Persyaratan</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">

                    <!-- BIODATA TAB -->
                    <div class="tab-pane fade show active" id="tab-biodata">
                        <!-- Personal Info -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <i class="fas fa-id-card mr-2 text-primary"></i> Identitas Pribadi
                            </div>
                            <div class="data-card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Nama Lengkap</span>
                                            <div class="info-value"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Tempat Lahir</span>
                                            <div class="info-value"><?= htmlspecialchars($p['tempat_lahir'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Tanggal Lahir</span>
                                            <div class="info-value">
                                                <?= $p['tgl_lahir'] ? date('d F Y', strtotime($p['tgl_lahir'])) : '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Jenis Kelamin</span>
                                            <div class="info-value"><?= htmlspecialchars($p['jenis_kelamin'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Agama</span>
                                            <div class="info-value"><?= htmlspecialchars($p['agama'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Status Pernikahan</span>
                                            <div class="info-value">
                                                <?= htmlspecialchars($p['status_pernikahan'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address & Contact -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <i class="fas fa-map-marked-alt mr-2 text-primary"></i> Alamat & Kontak
                            </div>
                            <div class="data-card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="info-group">
                                            <span class="info-label">Alamat Lengkap (KTP)</span>
                                            <div class="info-value"><?= htmlspecialchars($p['alamat_ktp'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-group">
                                            <span class="info-label">Provinsi</span>
                                            <div class="info-value"><?= htmlspecialchars($p['provinsi'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-group">
                                            <span class="info-label">Kota/Kabupaten</span>
                                            <div class="info-value"><?= htmlspecialchars($p['kota'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-group">
                                            <span class="info-label">Kecamatan</span>
                                            <div class="info-value"><?= htmlspecialchars($p['kecamatan'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-group">
                                            <span class="info-label">Kode Pos</span>
                                            <div class="info-value"><?= htmlspecialchars($p['kode_pos'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="info-group">
                                            <span class="info-label">Nomor Handphone / WA</span>
                                            <div class="info-value"><i class="fab fa-whatsapp text-success mr-1"></i>
                                                <?= htmlspecialchars($p['no_hp'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic -->
                        <div class="data-card">
                            <div class="data-card-header">
                                <i class="fas fa-graduation-cap mr-2 text-primary"></i> Data Akademik
                            </div>
                            <div class="data-card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Pilihan Program Studi</span>
                                            <div class="info-value"><?= htmlspecialchars($p['nama_prodi'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Kode Program Studi</span>
                                            <div class="info-value"><?= htmlspecialchars($p['kode_prodi'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Nomor Billing</span>
                                            <div class="info-value font-weight-bold text-primary">
                                                <?= htmlspecialchars($p['no_billing'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Terdaftar Sejak</span>
                                            <div class="info-value"><?= date('d F Y', strtotime($p['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- DOCUMENTS TAB -->
                    <div class="tab-pane fade" id="tab-dokumen">

                        <div class="row">
                            <!-- Nav Pills -->
                            <div class="col-md-4 mb-3">
                                <div class="nav flex-column nav-pills doc-nav-pills" id="v-pills-tab" role="tablist"
                                    aria-orientation="vertical">
                                    <?php foreach ($docs as $i => $doc):
                                        $hasDoc = !empty($p[$doc['field']]); ?>
                                        <a class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                                            id="v-pills-<?= $doc['type'] ?>-tab" data-toggle="pill"
                                            href="#v-pills-<?= $doc['type'] ?>" role="tab">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-<?= $doc['icon'] ?>"></i> <?= $doc['label'] ?></span>
                                                <?php if ($hasDoc): ?>
                                                    <i class="fas fa-check-circle text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-4 p-3 bg-light rounded text-muted small">
                                    <i class="fas fa-info-circle mr-1"></i> Pilih dokumen di daftar untuk melihat
                                    preview dan opsi pengelolaan.
                                </div>
                            </div>

                            <!-- Viewer -->
                            <div class="col-md-8">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <?php foreach ($docs as $i => $doc):
                                        $val = $p[$doc['field']] ?? '';
                                        $hasDoc = !empty($val);
                                        $docUrl = '';
                                        if ($hasDoc) {
                                            if (strpos($val, 'documents/') !== false || strpos($val, 'photos/') !== false) {
                                                $docUrl = '/storage/' . $val;
                                            } else {
                                                $docUrl = $doc['legacy_path'] . $val;
                                            }
                                        }
                                        ?>
                                        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                                            id="v-pills-<?= $doc['type'] ?>" role="tabpanel">
                                            <div class="data-card mb-0">
                                                <div class="data-card-header justify-content-between">
                                                    <span>Preview: <?= $doc['label'] ?></span>
                                                    <div>
                                                        <?php if ($hasDoc && $doc['isImage']): ?>
                                                            <div class="btn-group mr-2">
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    onclick="zoomIn('img-<?= $doc['type'] ?>')"
                                                                    title="Zoom In"><i class="fas fa-search-plus"></i></button>
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    onclick="zoomOut('img-<?= $doc['type'] ?>')"
                                                                    title="Zoom Out"><i
                                                                        class="fas fa-search-minus"></i></button>
                                                                <button type="button" class="btn btn-sm btn-light border"
                                                                    onclick="rotateImg('img-<?= $doc['type'] ?>')"
                                                                    title="Putar"><i class="fas fa-sync-alt"></i></button>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($hasDoc): ?>
                                                            <a href="<?= $docUrl ?>" target="_blank"
                                                                class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-external-link-alt mr-1"></i> Fullscreen
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="data-card-body p-0">
                                                    <div class="doc-viewer" style="overflow: hidden;">
                                                        <?php if ($hasDoc): ?>
                                                            <?php if ($doc['isImage']): ?>
                                                                <img src="<?= $docUrl ?>" alt="<?= $doc['label'] ?>"
                                                                    id="img-<?= $doc['type'] ?>"
                                                                    style="transition: transform 0.3s ease;">
                                                            <?php else: ?>
                                                                <!-- PDF.js Viewer -->
                                                                <div class="pdf-container" data-pdf-url="<?= $docUrl ?>"
                                                                    style="width: 100%; height: 520px;">
                                                                    <div class="pdf-toolbar">
                                                                        <button class="pdf-prev btn btn-sm btn-secondary">◄</button>
                                                                        <span class="pdf-page-info mx-2">
                                                                            <span class="pdf-page-num">1</span> / <span
                                                                                class="pdf-page-count">-</span>
                                                                        </span>
                                                                        <button class="pdf-next btn btn-sm btn-secondary">►</button>
                                                                        <button
                                                                            class="pdf-zoom-out btn btn-sm btn-info ml-2">-</button>
                                                                        <button class="pdf-zoom-in btn btn-sm btn-info">+</button>
                                                                    </div>
                                                                    <div class="pdf-canvas-wrapper"
                                                                        style="flex: 1; overflow: auto;">
                                                                        <canvas class="pdf-canvas"></canvas>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="doc-empty">
                                                                <i class="fas fa-file-upload fa-4x mb-3"></i>
                                                                <h5>Dokumen Belum Tersedia</h5>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const participantId = <?= $p['id'] ?>;
    let rotations = {};
    let scales = {};

    function rotateImg(imgId) {
        if (!rotations[imgId]) rotations[imgId] = 0;
        rotations[imgId] = (rotations[imgId] + 90) % 360;
        applyTransform(imgId);
    }

    function zoomIn(imgId) {
        if (!scales[imgId]) scales[imgId] = 1;
        scales[imgId] += 0.1;
        applyTransform(imgId);
    }

    function zoomOut(imgId) {
        if (!scales[imgId]) scales[imgId] = 1;
        if (scales[imgId] > 0.2) scales[imgId] -= 0.1;
        applyTransform(imgId);
    }

    function applyTransform(imgId) {
        const el = document.getElementById(imgId);
        if (!el) return;
        const r = rotations[imgId] || 0;
        const s = scales[imgId] || 1;
        el.style.transform = `rotate(${r}deg) scale(${s})`;
    }

</script>

<!-- PDF.js Library -->
<script src="/public/js/pdf.min.js"></script>
<script src="/public/js/pdf-viewer.js"></script>

<script>
    // Initialize PDF viewers when tab is shown
    document.addEventListener('DOMContentLoaded', function () {
        console.log('[PDF Viewer] Initialization started');

        // Initialize visible PDF viewers
        const visiblePdfContainers = document.querySelectorAll('.tab-pane.active .pdf-container[data-pdf-url]');
        console.log('[PDF Viewer] Found', visiblePdfContainers.length, 'visible PDF containers');
        visiblePdfContainers.forEach((container, index) => {
            const url = container.dataset.pdfUrl;
            console.log(`[PDF Viewer] Container ${index + 1}: URL =`, url);
            if (!container.dataset.initialized) {
                try {
                    new PDFViewer(container);
                    container.dataset.initialized = 'true';
                    console.log(`[PDF Viewer] ✓ Initialized container ${index + 1}`);
                } catch (error) {
                    console.error(`[PDF Viewer] ✗ Failed to initialize container ${index + 1}:`, error);
                }
            }
        });

        // Initialize PDF viewers when tab is clicked
        const docNavLinks = document.querySelectorAll('#v-pills-tab a[data-toggle="pill"]');
        console.log('[PDF Viewer] Found', docNavLinks.length, 'document nav links');
        docNavLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('href');
                console.log('[PDF Viewer] Tab switched to:', targetId);
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    const pdfContainer = targetPane.querySelector('.pdf-container[data-pdf-url]');
                    if (pdfContainer) {
                        const url = pdfContainer.dataset.pdfUrl;
                        console.log('[PDF Viewer] Found PDF container with URL:', url);
                        if (!pdfContainer.dataset.initialized) {
                            try {
                                console.log('[PDF Viewer] Initializing PDF viewer for:', url);
                                new PDFViewer(pdfContainer);
                                pdfContainer.dataset.initialized = 'true';
                                console.log('[PDF Viewer] ✓ Successfully initialized');
                            } catch (error) {
                                console.error('[PDF Viewer] ✗ Failed to initialize:', error);
                            }
                        } else {
                            console.log('[PDF Viewer] Already initialized');
                        }
                    } else {
                        console.log('[PDF Viewer] No PDF container found in tab');
                    }
                } else {
                    console.error('[PDF Viewer] Target pane not found:', targetId);
                }
            });
        });

        console.log('[PDF Viewer] Initialization complete');
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>