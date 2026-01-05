<?php
$title = 'Komponen Penilaian';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Komponen Penilaian</h3>
                <p class="text-subtitle text-muted">Atur komponen TPA dan Tes Bidang</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Assessment</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <?php if ($isAdminProdi): ?>
        <!-- Threshold Setting for Admin Prodi -->
        <div class="card card-outline card-info shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-3 px-4">
                <h5 class="card-title text-info mb-0"><i class="fas fa-sliders-h me-2"></i> Pengaturan Nilai Minimum
                    Kelulusan</h5>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="/admin/assessment/threshold/save" method="POST" class="form-inline">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label for="nilai_minimum" class="form-label">Nilai Minimum Total Bidang untuk dinyatakan
                                LULUS:</label>
                            <div class="input-group">
                                <input type="number" name="nilai_minimum" id="nilai_minimum"
                                    class="form-control form-control-lg" value="<?php echo $minimumThreshold ?? 0; ?>"
                                    min="0" max="1000" step="1" style="max-width: 150px;">
                                <div class="input-group-append">
                                    <span class="input-group-text">poin</span>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Peserta dengan total nilai Bidang ≥ nilai ini akan otomatis disarankan "Lulus".
                                Set ke 0 untuk menonaktifkan auto-suggest.
                            </small>
                        </div>
                        <div class="col-md-6 text-md-right mt-3 mt-md-0">
                            <button type="submit" class="btn btn-info shadow-sm px-4">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card card-outline card-primary shadow-sm">
        <div
            class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0 pt-4 px-4">
            <h4 class="card-title fw-bold text-primary"><i class="bi bi-layers-fill me-2"></i> Daftar Komponen</h4>
            <button type="button" class="btn btn-primary shadow-sm px-4" data-toggle="modal"
                data-target="#addComponentModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Komponen
            </button>
        </div>
        <div class="card-body px-4 pb-4">
            <!-- Alert Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-light-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill text-success me-2"></i> Aksi berhasil dilakukan.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-light-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Terjadi kesalahan atau akses ditolak.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="table-responsive rounded border">
                <table class="table table-hover mb-0" id="table1">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Nama Komponen</th>
                            <th class="px-4 py-3">Lingkup Prodi</th>
                            <th class="px-4 py-3 text-center">Bobot</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($components as $c): ?>
                            <tr class="align-middle">
                                <td class="px-4">
                                    <?php if ($c['type'] === 'TPA'): ?>
                                        <span
                                            class="badge bg-light-primary text-primary border border-primary px-3 py-2 rounded-pill">
                                            <i class="bi bi-award me-1"></i> TPA
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="badge bg-light-success text-success border border-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-braces me-1"></i> BIDANG
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 fw-bold text-dark">
                                    <?php echo htmlspecialchars($c['nama_komponen']); ?>
                                </td>
                                <td class="px-4">
                                    <?php
                                    if (empty($c['prodi_id'])) {
                                        echo '<span class="d-inline-flex align-items-center text-muted"><i class="bi bi-globe2 me-2"></i> Global / Semua Prodi</span>';
                                    } else {
                                        echo '<span class="d-inline-flex align-items-center text-dark"><i class="bi bi-bookmarks me-2"></i> ' . htmlspecialchars($c['prodi_id']) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-4 text-center">
                                    <span class="badge bg-light-secondary text-dark border px-3">
                                        <?php echo $c['bobot_persen']; ?>%
                                    </span>
                                </td>
                                <td class="px-4 text-end">
                                    <a href="/admin/assessment/components/delete/<?php echo $c['id']; ?>"
                                        class="btn btn-sm btn-outline-danger shadow-sm"
                                        onclick="return confirm('Yakin hapus? Skor terkait akan ikut terhapus.')"
                                        title="Hapus" data-toggle="tooltip">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($components)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="my-3">
                                        <i class="bi bi-clipboard-x display-4 text-secondary opacity-50"></i>
                                    </div>
                                    <h5 class="fw-bold text-secondary">Belum ada komponen</h5>
                                    <p class="mb-0">Silakan tambahkan komponen penilaian baru.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    /* Custom Badge & Alert Colors equivalent to newer BS versions if missing */
    .bg-light-primary {
        background-color: #e6f2ff !important;
    }

    .bg-light-success {
        background-color: #e6fffa !important;
    }

    .bg-light-danger {
        background-color: #ffe6e6 !important;
    }

    .bg-light-secondary {
        background-color: #f2f2f2 !important;
    }

    .alert-light-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .alert-light-danger {
        background-color: #f8d7da;
        color: #842029;
    }
</style>

<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/assessment/components/store" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Komponen Penilaian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (!$isAdminProdi): ?>
                        <!-- Tipe Komponen - Only for Admin/Superadmin -->
                        <div class="form-group mb-3">
                            <label for="type">Tipe Komponen</label>
                            <select name="type" id="type" class="custom-select shadow-sm" required onchange="toggleProdi()">
                                <option value="TPA">TPA (Berlaku Semua Prodi)</option>
                                <option value="BIDANG">Tes Bidang (Spesifik Prodi)</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Hidden type for Admin Prodi (always BIDANG) -->
                        <input type="hidden" name="type" value="BIDANG">
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="nama_komponen">Nama Komponen</label>
                        <input type="text" name="nama_komponen" id="nama_komponen" class="form-control"
                            placeholder="Contoh: Tes Wawancara, Tes Tulis, Kemampuan Verbal" required>
                    </div>

                    <?php if (!$isAdminProdi): ?>
                        <!-- Program Studi - Only for Admin/Superadmin -->
                        <div class="form-group mb-3" id="prodiGroup" style="display:none;">
                            <label for="prodi_id">Program Studi</label>
                            <select name="prodi_id" id="prodi_id" class="custom-select shadow-sm">
                                <option value="">-- Pilih Prodi (Opsional) --</option>
                                <?php foreach ($prodiList as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p['code'] ?? $p['name']); ?>">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Kosongkan jika komponen TPA berlaku umum.</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group mb-3">
                        <label for="bobot_persen">Bobot % (Opsional)</label>
                        <input type="number" name="bobot_persen" id="bobot_persen" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleProdi() {
        var type = document.getElementById('type').value;
        var prodiGroup = document.getElementById('prodiGroup');

        // Logic: TPA hide prodi (always null/global). Bidang show prodi selection (unless Admin Prodi handles it automatically)
        // Actually, Admin Prodi logic is handled PHP side (hidden input).
        // So distinct logic for Superadmin vs Admin Prodi.

        <?php if ($isAdminProdi): ?>
            // Admin Prodi always edits Bidang for their prodi. Just show info?
            if (type === 'BIDANG') {
                prodiGroup.style.display = 'block';
            } else {
                prodiGroup.style.display = 'none';
            }
        <?php else: ?>
            if (type === 'BIDANG') {
                prodiGroup.style.display = 'block';
                document.getElementById('prodi_id').required = true;
            } else {
                prodiGroup.style.display = 'none';
                document.getElementById('prodi_id').required = false;
            }
        <?php endif; ?>
    }

    // Init
    document.addEventListener('DOMContentLoaded', function () {
        toggleProdi();
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>