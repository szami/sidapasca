<?php ob_start(); 
$isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);
$canUpload = \App\Utils\RoleHelper::canUploadDocuments();
$photoUrl = !empty($p['photo_filename']) ? '/storage/photos/' . $p['photo_filename'] : '/public/img/default-profile.png'; 
// Fallback if default profile img doesn't exist, browser will show broken or alt. Ideally use a CDN placeholder or integrated asset.
$photoUrl = !empty($p['photo_filename']) ? '/storage/photos/' . $p['photo_filename'] : 'https://ui-avatars.com/api/?name='.urlencode($p['nama_lengkap']).'&background=random&size=150';

$docs = [
    ['type' => 'foto', 'label' => 'Foto Peserta', 'field' => 'photo_filename', 'path' => '/storage/photos/', 'accept' => 'image/jpeg,image/png', 'icon' => 'camera', 'isImage' => true],
    ['type' => 'ktp', 'label' => 'KTP', 'field' => 'ktp_filename', 'path' => '/storage/documents/ktp/', 'accept' => 'image/jpeg,image/png', 'icon' => 'id-card', 'isImage' => true],
    ['type' => 'ijazah', 'label' => 'Ijazah S1', 'field' => 'ijazah_filename', 'path' => '/storage/documents/ijazah/', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'isImage' => true],
    ['type' => 'transkrip', 'label' => 'Transkrip S1', 'field' => 'transkrip_filename', 'path' => '/storage/documents/transkrip/', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'isImage' => false],
];
if ($isS3) {
    $docs[] = ['type' => 'ijazah_s2', 'label' => 'Ijazah S2', 'field' => 'ijazah_s2_filename', 'path' => '/storage/documents/ijazah_s2/', 'accept' => 'image/jpeg,image/png', 'icon' => 'graduation-cap', 'isImage' => true];
    $docs[] = ['type' => 'transkrip_s2', 'label' => 'Transkrip S2', 'field' => 'transkrip_s2_filename', 'path' => '/storage/documents/transkrip_s2/', 'accept' => 'application/pdf', 'icon' => 'file-pdf', 'isImage' => false];
}
?>
<style>
    .profile-sidebar { position: sticky; top: 20px; }
    .profile-user-img { width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .status-badge-lg { font-size: 0.8rem; padding: 10px 15px; border-radius: 12px; font-weight: 700; letter-spacing: 0.5px; width: 100%; display: block; text-align: center; margin-bottom: 10px; }
    
    .nav-tabs-premium { border-bottom: 2px solid #e9ecef; }
    .nav-tabs-premium .nav-link { border: none; font-weight: 600; color: #6c757d; padding: 15px 25px; font-size: 1rem; position: relative; transition: all 0.3s; }
    .nav-tabs-premium .nav-link.active { color: #007bff; background: transparent; }
    .nav-tabs-premium .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background: #007bff; border-radius: 3px 3px 0 0; }
    .nav-tabs-premium .nav-link:hover { color: #0056b3; }

    .data-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #f1f3f5; overflow: hidden; }
    .data-card-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #f1f3f5; font-weight: 700; color: #495057; display: flex; align-items: center; }
    .data-card-body { padding: 20px; }
    
    .info-group { margin-bottom: 20px; }
    .info-label { display: block; font-size: 0.75rem; font-weight: 700; color: #adb5bd; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
    .info-value { font-size: 1.05rem; color: #343a40; font-weight: 500; border-bottom: 1px dashed #e9ecef; padding-bottom: 5px; }
    
    .doc-nav-pills .nav-link { text-align: left; padding: 12px 15px; border-radius: 8px; margin-bottom: 8px; color: #495057; font-weight: 500; transition: all 0.2s; border: 1px solid transparent; }
    .doc-nav-pills .nav-link:hover { background: #f8f9fa; }
    .doc-nav-pills .nav-link.active { background: #e7f1ff; color: #007bff; border-color: #b8daff; font-weight: 600; }
    .doc-nav-pills .nav-link i { width: 20px; text-align: center; margin-right: 8px; }
    
    .doc-viewer { background: #343a40; border-radius: 12px; padding: 15px; min-height: 550px; display: flex; align-items: center; justify-content: center; position: relative; }
    .doc-viewer img { max-height: 520px; max-width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-radius: 4px; }
    .doc-viewer iframe { width: 100%; height: 520px; border: none; background: white; border-radius: 4px; }
    .doc-empty { color: rgba(255,255,255,0.3); text-align: center; }
    
    .btn-action-group .btn { border-radius: 8px; font-weight: 600; padding: 10px 20px; }
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
                        <p class="text-muted small mb-3"><?= htmlspecialchars($p['nomor_peserta'] ?? 'Calon Mahasiswa') ?></p>
                        
                        <div class="px-3">
                            <div class="status-badge-lg" style="background: <?= $p['status_berkas'] === 'lulus' ? '#d1e7dd' : ($p['status_berkas'] === 'gagal' ? '#f8d7da' : '#fff3cd') ?>; color: <?= $p['status_berkas'] === 'lulus' ? '#0f5132' : ($p['status_berkas'] === 'gagal' ? '#842029' : '#664d03') ?>">
                                Berkas: <?= strtoupper($p['status_berkas'] ?? 'PENDING') ?>
                            </div>
                            <div class="status-badge-lg" style="background: <?= $p['status_pembayaran'] ? '#cfe2ff' : '#e2e3e5' ?>; color: <?= $p['status_pembayaran'] ? '#084298' : '#41464b' ?>">
                                <?= $p['status_pembayaran'] ? 'PEMBAYARAN LUNAS' : 'BELUM BAYAR' ?>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="text-left px-2">
                             <div class="mb-2">
                                <small class="text-muted d-block">PRODI</small>
                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($p['nama_prodi'] ?? '-') ?></span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">EMAIL</small>
                                <span class="text-dark"><?= htmlspecialchars($p['email']) ?></span>
                            </div>
                        </div>

                        <?php if (\App\Utils\RoleHelper::isSuperadmin()): ?>
                        <hr>
                        <a href="/admin/participants/edit/<?= $p['id'] ?>" class="btn btn-primary btn-block rounded-pill">
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
                            <a class="nav-link active" data-toggle="tab" href="#tab-biodata" role="tab"><i class="fas fa-user-circle mr-2"></i>Data Peserta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-dokumen" role="tab"><i class="fas fa-folder-open mr-2"></i>Dokumen Persyaratan</a>
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
                                            <div class="info-value"><?= htmlspecialchars($p['tempat_lahir'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Tanggal Lahir</span>
                                            <div class="info-value"><?= $p['tgl_lahir'] ? date('d F Y', strtotime($p['tgl_lahir'])) : '-' ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-group">
                                            <span class="info-label">Jenis Kelamin</span>
                                            <div class="info-value"><?= htmlspecialchars($p['jenis_kelamin'] ?? '-') ?></div>
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
                                            <div class="info-value"><?= htmlspecialchars($p['status_pernikahan'] ?? '-') ?></div>
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
                                            <div class="info-value"><?= htmlspecialchars($p['alamat_ktp'] ?? '-') ?></div>
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
                                            <div class="info-value"><?= htmlspecialchars($p['kecamatan'] ?? '-') ?></div>
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
                                            <div class="info-value"><i class="fab fa-whatsapp text-success mr-1"></i> <?= htmlspecialchars($p['no_hp'] ?? '-') ?></div>
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
                                            <div class="info-value"><?= htmlspecialchars($p['nama_prodi'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Kode Program Studi</span>
                                            <div class="info-value"><?= htmlspecialchars($p['kode_prodi'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Nomor Billing</span>
                                            <div class="info-value font-weight-bold text-primary"><?= htmlspecialchars($p['no_billing'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                     <div class="col-md-6">
                                        <div class="info-group">
                                            <span class="info-label">Terdaftar Sejak</span>
                                            <div class="info-value"><?= date('d F Y', strtotime($p['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- DOCUMENTS TAB -->
                    <div class="tab-pane fade" id="tab-dokumen">
                         <?php if ($canUpload): ?>
                        <div class="mb-3 text-right">
                             <button type="button" class="btn btn-warning shadow-sm" onclick="syncAllDocs()">
                                <i class="fas fa-sync-alt mr-1"></i> Sinkron Semua Dokumen
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Nav Pills -->
                            <div class="col-md-4 mb-3">
                                <div class="nav flex-column nav-pills doc-nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <?php foreach ($docs as $i => $doc): $hasDoc = !empty($p[$doc['field']]); ?>
                                    <a class="nav-link <?= $i === 0 ? 'active' : '' ?>" id="v-pills-<?= $doc['type'] ?>-tab" data-toggle="pill" href="#v-pills-<?= $doc['type'] ?>" role="tab">
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
                                    <i class="fas fa-info-circle mr-1"></i> Pilih dokumen di daftar untuk melihat preview dan opsi pengelolaan.
                                </div>
                            </div>
                            
                            <!-- Viewer -->
                            <div class="col-md-8">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <?php foreach ($docs as $i => $doc): 
                                        $hasDoc = !empty($p[$doc['field']]);
                                        $docUrl = $hasDoc ? $doc['path'] . $p[$doc['field']] : '';
                                    ?>
                                    <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="v-pills-<?= $doc['type'] ?>" role="tabpanel">
                                        <div class="data-card mb-0">
                                            <div class="data-card-header justify-content-between">
                                                <span>Preview: <?= $doc['label'] ?></span>
                                                <?php if ($hasDoc): ?>
                                                <a href="<?= $docUrl ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Fullscreen
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="data-card-body p-0">
                                                <div class="doc-viewer">
                                                    <?php if ($hasDoc): ?>
                                                        <?php if ($doc['isImage']): ?>
                                                            <img src="<?= $docUrl ?>" alt="<?= $doc['label'] ?>" id="img-<?= $doc['type'] ?>">
                                                        <?php else: ?>
                                                            <iframe src="<?= $docUrl ?>"></iframe>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="doc-empty">
                                                            <i class="fas fa-file-upload fa-4x mb-3"></i>
                                                            <h5>Dokumen Belum Tersedia</h5>
                                                            <p>Silakan upload atau sinkronisasi</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Action Buttons -->
                                                <div class="p-3 bg-light border-top">
                                                     <div class="row">
                                                         <?php if ($hasDoc && $doc['isImage']): ?>
                                                         <div class="col-md-6 mb-2">
                                                             <button class="btn btn-info btn-block" onclick="rotateImg('img-<?= $doc['type'] ?>')">
                                                                <i class="fas fa-undo mr-1"></i> Putar Gambar
                                                            </button>
                                                         </div>
                                                         <?php endif; ?>
                                                         
                                                         <?php if ($canUpload): ?>
                                                             <input type="file" id="file-<?= $doc['type'] ?>" accept="<?= $doc['accept'] ?>" class="d-none" onchange="uploadDoc('<?= $doc['type'] ?>')">
                                                             
                                                             <div class="col-md-6 mb-2">
                                                                 <button class="btn btn-primary btn-block" onclick="$('#file-<?= $doc['type'] ?>').click()">
                                                                    <i class="fas fa-upload mr-1"></i> <?= $hasDoc ? 'Ganti File' : 'Upload File' ?>
                                                                </button>
                                                             </div>
                                                             
                                                             <div class="col-md-6 mb-2">
                                                                 <button class="btn btn-warning btn-block" onclick="syncDoc('<?= $doc['type'] ?>')">
                                                                    <i class="fas fa-cloud-download-alt mr-1"></i> Sinkron Server
                                                                </button>
                                                             </div>
                                                             
                                                             <?php if ($hasDoc): ?>
                                                             <div class="col-md-6 mb-2">
                                                                 <button class="btn btn-danger btn-block" onclick="deleteDoc('<?= $doc['type'] ?>')">
                                                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                                                </button>
                                                             </div>
                                                             <?php endif; ?>
                                                             
                                                         <?php endif; ?>
                                                     </div>
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

function rotateImg(imgId) {
    if (!rotations[imgId]) rotations[imgId] = 0;
    rotations[imgId] = (rotations[imgId] + 90) % 360;
    document.getElementById(imgId).style.transform = `rotate(${rotations[imgId]}deg)`;
}

<?php if ($canUpload): ?>
function uploadDoc(type) {
    const fileInput = document.getElementById('file-' + type);
    if (!fileInput.files[0]) return;
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    Swal.fire({title: 'Mengupload...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
    
    fetch(`/admin/participants/${participantId}/upload-doc/${type}`, {method: 'POST', body: formData})
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire({icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire({icon: 'error', title: 'Gagal', text: data.message});
        }
    })
    .catch(e => { Swal.close(); Swal.fire({icon: 'error', title: 'Error', text: e.message}); });
}

function deleteDoc(type) {
    Swal.fire({
        title: 'Hapus Dokumen?',
        text: 'Dokumen akan dihapus permanen',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
            fetch(`/admin/participants/${participantId}/delete-doc/${type}`, {method: 'DELETE'})
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({icon: 'success', title: 'Dihapus!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
                } else {
                    Swal.fire({icon: 'error', title: 'Gagal', text: data.message});
                }
            });
        }
    });
}

function syncDoc(type) {
    Swal.fire({title: 'Menyinkronkan...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
    fetch(`/admin/participants/${participantId}/auto-download-docs`, {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({types: [type]})
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire({icon: 'success', title: 'Berhasil!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire({icon: 'error', title: 'Gagal', html: `<p>${data.message}</p>`});
        }
    });
}

function syncAllDocs() {
     Swal.fire({title: 'Sinkron Semua...', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
    fetch(`/admin/participants/${participantId}/auto-download-docs`, {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({types: ['foto', 'ktp', 'ijazah', 'transkrip']})
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire({icon: 'success', title: 'Selesai!', timer: 1500, showConfirmButton: false}).then(() => location.reload());
        } else {
            Swal.fire({icon: 'warning', title: 'Selesai', html: `<p>${data.message}</p>`}).then(() => location.reload());
        }
    });
}
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>