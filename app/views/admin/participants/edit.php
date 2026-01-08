<?php ob_start();
// Path Resolution Helper
$resolvePath = function ($filename, $type) {
    if (empty($filename))
        return '';
    // Check if new structure (already contains path)
    if (strpos($filename, 'photos/') !== false || strpos($filename, 'documents/') !== false) {
        return '/storage/' . $filename;
    }
    // Legacy mapping
    $legacyBase = '/storage';
    if ($type === 'foto')
        return "$legacyBase/photos/$filename";
    return "$legacyBase/documents/$type/$filename";
};

$photoUrl = $resolvePath($p['photo_filename'], 'foto');
$ktpUrl = $resolvePath($p['ktp_filename'], 'ktp');
$ijazahUrl = $resolvePath($p['ijazah_filename'], 'ijazah');
$transkripUrl = $resolvePath($p['transkrip_filename'], 'transkrip');
$ijazahS2Url = $resolvePath($p['ijazah_s2_filename'], 'ijazah_s2');
$transkripS2Url = $resolvePath($p['transkrip_s2_filename'], 'transkrip_s2');
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --info-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        --premium-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        --premium-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.03);
    }

    .premium-card {
        border-radius: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .premium-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--premium-shadow-hover) !important;
    }

    .form-control-premium {
        border-radius: 0.6rem;
        border: 1.5px solid #edf2f7;
        padding: 0.6rem 0.9rem;
        transition: all 0.2s;
        background-color: #f8fafc;
    }

    .form-control-premium:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .section-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: block;
    }

    .icon-container {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .bg-primary-soft {
        background-color: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .bg-success-soft {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .bg-info-soft {
        background-color: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
    }

    .bg-purple-soft {
        background-color: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .header-gradient {
        background: var(--primary-gradient);
        color: white;
        border-radius: 1rem 1rem 0 0;
        padding: 1.5rem 2rem;
    }

    .btn-premium {
        border-radius: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-premium:hover {
        transform: scale(1.02);
    }

    .custom-switch-premium .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1rem;
        background-color: #cbd5e1;
    }

    .custom-switch-premium .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        background-color: #fff;
        border-radius: 50%;
    }

    .custom-switch-premium .custom-control-input:checked~.custom-control-label::before {
        background-color: #10b981;
    }

    .custom-switch-premium .custom-control-input:checked~.custom-control-label::after {
        transform: translateX(1.25rem);
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

<div class="row justify-content-center">
    <?php if (isset($_GET['error'])): ?>
        <div class="col-xl-11 mb-3">
            <?php if ($_GET['error'] === 'duplicate_nomor'): ?>
                <div class="alert alert-danger shadow-sm border-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Gagal Update!</strong> Nomor Peserta tersebut sudah
                    digunakan oleh peserta lain. Mohon gunakan nomor yang berbeda.
                </div>
            <?php elseif ($_GET['error'] === 'unauthorized'): ?>
                <div class="alert alert-danger shadow-sm border-0">
                    <i class="fas fa-lock mr-2"></i> <strong>Akses Ditolak!</strong> Anda tidak memiliki izin untuk melakukan
                    aksi ini.
                </div>
            <?php else: ?>
                <div class="alert alert-danger shadow-sm border-0">
                    <i class="fas fa-exclamation-circle mr-2"></i> Terjadi kesalahan:
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="col-xl-11 mb-3">
            <div class="alert alert-success shadow-sm border-0">
                <i class="fas fa-check-circle mr-2"></i> Data berhasil diperbarui.
            </div>
        </div>
    <?php endif; ?>

    <div class="col-xl-11">
        <form action="/admin/participants/update/<?php echo $p['id']; ?>" method="POST">
            <!-- Header Card -->
            <div class="card premium-card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="header-gradient d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 font-weight-bold">Update Data Peserta</h4>
                        <p class="mb-0 opacity-75">ID Peserta: #<?php echo $p['id']; ?> | <i
                                class="fas fa-calendar-alt mr-1"></i> Terdaftar pada:
                            <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                        </p>
                    </div>
                    <div class="text-right d-none d-md-block">
                        <span class="badge badge-light py-2 px-3 rounded-pill">
                            <i class="fas fa-barcode mr-1"></i>
                            <?php echo $p['nomor_peserta'] ?: 'BELUM ADA NO. PESERTA'; ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-8 border-right-md pr-md-5">
                            <label class="section-label">Informasi Utama & Identitas</label>

                            <div class="d-flex align-items-start mb-4">
                                <!-- Photo Section in Header -->
                                <div class="mr-4 text-center">
                                    <div class="position-relative">
                                        <?php if (!empty($p['photo_filename'])): ?>
                                            <img src="<?php echo $photoUrl; ?>" alt="Foto" class="rounded shadow-sm border"
                                                style="width: 120px; height: 160px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                                style="width: 120px; height: 160px;">
                                                <i class="fas fa-user-circle fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <div class="mt-2">
                                                <!-- Client-side rotate for header thumbnail? Or Server side -->
                                                <button type="button" class="btn btn-xs btn-outline-secondary"
                                                    onclick="rotateDoc('foto')" title="Putar Permanen"><i
                                                        class="fas fa-sync-alt"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group mb-3">
                                                <label class="small text-muted mb-1">Nama Lengkap Sesuai
                                                    KTP/Ijazah</label>
                                                <input type="text" name="nama_lengkap"
                                                    class="form-control form-control-premium font-weight-bold"
                                                    value="<?php echo $p['nama_lengkap']; ?>" required
                                                    style="font-size: 1.1rem;">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group mb-3">
                                                <label class="small text-muted mb-1">Nomor Peserta</label>
                                                <input type="text" name="nomor_peserta"
                                                    class="form-control form-control-premium font-weight-bold text-primary"
                                                    value="<?php echo $p['nomor_peserta']; ?>"
                                                    placeholder="Belum generate">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="small text-muted mb-1">Tempat Lahir</label>
                                                <input type="text" name="tempat_lahir"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['tempat_lahir'] ?? ''; ?>"
                                                    placeholder="BANJARMASIN">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="small text-muted mb-1">Tanggal Lahir (YYYY-MM-DD)</label>
                                                <input type="date" name="tgl_lahir"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['tgl_lahir'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Program Studi Pilihan</label>
                                        <input type="text" name="nama_prodi"
                                            class="form-control form-control-premium font-weight-bold"
                                            value="<?php echo $p['nama_prodi'] ?? ''; ?>"
                                            placeholder="Nama Program Studi">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Alamat Email (Username)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 bg-transparent px-0 mr-2"><i
                                                        class="fas fa-envelope text-primary"></i></span>
                                            </div>
                                            <input type="email" name="email" class="form-control form-control-premium"
                                                value="<?php echo $p['email']; ?>" required
                                                placeholder="user@domain.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Nomor Billing</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 bg-transparent px-0 mr-2"><i
                                                        class="fas fa-file-invoice text-muted"></i></span>
                                            </div>
                                            <input type="text" name="no_billing"
                                                class="form-control form-control-premium bg-light"
                                                value="<?php echo $p['no_billing']; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 pl-md-5">
                            <label class="section-label">Status & Administrasi</label>
                            <div class="form-group mb-3">
                                <label class="small text-muted mb-1">Semester Aktif</label>
                                <select name="semester_id" class="form-control form-control-premium">
                                    <?php foreach ($semesters as $sem): ?>
                                        <option value="<?php echo $sem['id']; ?>" <?php echo $sem['id'] == $p['semester_id'] ? 'selected' : ''; ?>>
                                            <?php echo $sem['nama']; ?> (<?php echo $sem['kode']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row align-items-center mb-4">
                                <div class="col-12 mb-3">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Status Berkas</label>
                                        <select name="status_berkas" class="form-control form-control-premium p-2">
                                            <option value="pending" <?php echo $p['status_berkas'] == 'pending' ? 'selected' : ''; ?>>🕒 Pending</option>
                                            <option value="lulus" <?php echo $p['status_berkas'] == 'lulus' ? 'selected' : ''; ?>>✅ Lulus</option>
                                            <option value="gagal" <?php echo $p['status_berkas'] == 'gagal' ? 'selected' : ''; ?>>❌ Gagal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch custom-switch-premium">
                                            <input type="checkbox" class="custom-control-input" id="payCheck"
                                                name="status_pembayaran" <?php echo $p['status_pembayaran'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label small font-weight-bold"
                                                for="payCheck">Konfirmasi Pembayaran</label>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="alert alert-warning border-0 shadow-sm rounded-lg p-3">
                                <small class="d-block font-weight-bold mb-1"><i
                                        class="fas fa-exclamation-triangle mr-1"></i> Catatan:</small>
                                <small class="text-muted d-block" style="line-height: 1.4;">
                                    Pastikan status pembayaran sudah dikonfirmasi sebelum meluluskan berkas peserta.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <div class="row">
                <!-- Data Pribadi -->
                <div class="col-12 mb-4">
                    <div class="card premium-card shadow-sm border-0">
                        <div class="card-header border-0 bg-white pt-4 px-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-container bg-info-soft">
                                    <i class="fas fa-id-card-alt"></i>
                                </div>
                                <h5 class="mb-0 font-weight-bold text-dark">Data Pribadi & Identitas</h5>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-7">
                                            <div class="form-group mb-3">
                                                <label class="section-label">Jenis Kelamin</label>
                                                <select name="jenis_kelamin" class="form-control form-control-premium">
                                                    <option value="L" <?php echo ($p['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki (Male)</option>
                                                    <option value="P" <?php echo ($p['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan (Female)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-group mb-3">
                                                <label class="section-label">Agama</label>
                                                <input type="text" name="agama"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['agama'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="section-label">Nomor WhatsApp / HP</label>
                                        <input type="text" name="no_hp"
                                            class="form-control form-control-premium font-weight-bold text-primary"
                                            value="<?php echo $p['no_hp'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Alamat Lengkap (KTP)</label>
                                        <textarea name="alamat_ktp" class="form-control form-control-premium" rows="3"
                                            placeholder="Masukkan alamat lengkap sesuai KTP..."><?php echo $p['alamat_ktp'] ?? ''; ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="section-label">Kecamatan</label>
                                                <input type="text" name="kecamatan"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['kecamatan'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group mb-2">
                                                <label class="section-label">Kota / Kabupaten</label>
                                                <input type="text" name="kota" class="form-control form-control-premium"
                                                    value="<?php echo $p['kota'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label class="section-label">Kode Pos</label>
                                                <input type="text" name="kode_pos"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['kode_pos'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pekerjaan & Karir -->
                <div class="col-12 mb-4">
                    <div class="card premium-card shadow-sm border-0">
                        <div class="card-header border-0 bg-white pt-4 px-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-container bg-success-soft">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h5 class="mb-0 font-weight-bold text-dark">Informasi Pekerjaan</h5>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Profesi / Jabatan Utama</label>
                                        <input type="text" name="pekerjaan"
                                            class="form-control form-control-premium font-weight-bold"
                                            value="<?php echo $p['pekerjaan'] ?? ''; ?>"
                                            placeholder="Contoh: Dosen, Pegawai Negeri, dll">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="section-label">Instansi / Unit Kerja / Perusahaan</label>
                                        <input type="text" name="instansi_pekerjaan"
                                            class="form-control form-control-premium"
                                            value="<?php echo $p['instansi_pekerjaan'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Alamat Kantor / Instansi</label>
                                        <textarea name="alamat_pekerjaan" class="form-control form-control-premium"
                                            rows="2"><?php echo $p['alamat_pekerjaan'] ?? ''; ?></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="section-label">Nomor Telpon Kantor</label>
                                        <input type="text" name="telpon_pekerjaan"
                                            class="form-control form-control-premium"
                                            value="<?php echo $p['telpon_pekerjaan'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pendidikan S1 -->
                <div class="col-12 mb-4">
                    <div class="card premium-card shadow-sm border-0">
                        <div class="card-header border-0 bg-white pt-4 px-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-container bg-info-soft">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h5 class="mb-0 font-weight-bold text-dark">Riwayat Pendidikan Sarjana (S1)</h5>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="section-label">Nama Perguruan Tinggi</label>
                                        <input type="text" name="s1_perguruan_tinggi"
                                            class="form-control form-control-premium font-weight-bold"
                                            value="<?php echo $p['s1_perguruan_tinggi'] ?? ''; ?>">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="section-label">Fakultas</label>
                                                <input type="text" name="s1_fakultas"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['s1_fakultas'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="section-label">Program Studi</label>
                                                <input type="text" name="s1_prodi"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['s1_prodi'] ?? ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="section-label">Tahun Masuk</label>
                                            <input type="text" name="s1_tahun_masuk"
                                                class="form-control form-control-premium"
                                                value="<?php echo $p['s1_tahun_masuk'] ?? ''; ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="section-label">Tahun Lulus</label>
                                            <input type="text" name="s1_tahun_tamat"
                                                class="form-control form-control-premium"
                                                value="<?php echo $p['s1_tahun_tamat'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="section-label">IPK Akhir</label>
                                            <input type="text" name="s1_ipk"
                                                class="form-control form-control-premium font-weight-bold text-primary"
                                                value="<?php echo $p['s1_ipk'] ?? ''; ?>" style="font-size: 1.1rem;">
                                        </div>
                                        <div class="col-6">
                                            <label class="section-label">Gelar S1</label>
                                            <input type="text" name="s1_gelar" class="form-control form-control-premium"
                                                value="<?php echo $p['s1_gelar'] ?? ''; ?>" placeholder="Contoh: S.Kom">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pendidikan S2 (Conditional for S3 Applicants) -->
                <?php
                $isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);
                if ($isS3):
                    ?>
                    <div class="col-12 mb-4">
                        <div class="card premium-card shadow-sm border-0">
                            <div class="card-header border-0 bg-white pt-4 px-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-container bg-purple-soft">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h5 class="mb-0 font-weight-bold text-dark">Riwayat Pendidikan Magister (S2)</h5>
                                </div>
                            </div>
                            <div class="card-body px-4 pb-4 pt-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="section-label">Nama Perguruan Tinggi</label>
                                            <input type="text" name="s2_perguruan_tinggi"
                                                class="form-control form-control-premium font-weight-bold"
                                                value="<?php echo $p['s2_perguruan_tinggi'] ?? ''; ?>">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="section-label">Fakultas</label>
                                                    <input type="text" name="s2_fakultas"
                                                        class="form-control form-control-premium"
                                                        value="<?php echo $p['s2_fakultas'] ?? ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="section-label">Program Studi</label>
                                                    <input type="text" name="s2_prodi"
                                                        class="form-control form-control-premium"
                                                        value="<?php echo $p['s2_prodi'] ?? ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label class="section-label">Tahun Masuk</label>
                                                <input type="text" name="s2_tahun_masuk"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['s2_tahun_masuk'] ?? ''; ?>">
                                            </div>
                                            <div class="col-6">
                                                <label class="section-label">Tahun Lulus</label>
                                                <input type="text" name="s2_tahun_tamat"
                                                    class="form-control form-control-premium"
                                                    value="<?php echo $p['s2_tahun_tamat'] ?? ''; ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <label class="section-label">IPK Akhir</label>
                                                <input type="text" name="s2_ipk"
                                                    class="form-control form-control-premium font-weight-bold text-purple"
                                                    value="<?php echo $p['s2_ipk'] ?? ''; ?>" style="font-size: 1.1rem;">
                                            </div>
                                            <div class="col-6">
                                                <label class="section-label">Gelar S2</label>
                                                <input type="text" name="s2_gelar" class="form-control form-control-premium"
                                                    value="<?php echo $p['s2_gelar'] ?? ''; ?>" placeholder="Contoh: M.Kom">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Document Management Section (Tabbed) -->
            <div class="card premium-card shadow-sm border-0 mb-4" id="document-management-section">
                <div class="card-header border-0 bg-white pt-4 px-4">
                    <h5 class="mb-0 font-weight-bold text-dark">
                        <i class="fas fa-folder-open mr-2"></i>Dokumen Peserta
                    </h5>
                    <small class="text-muted">Kelola semua dokumen peserta (Foto, KTP, Ijazah, Transkrip)</small>
                </div>
                <div class="card-body px-4 pb-4 pt-2">
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
                                <i class="fas fa-graduation-cap mr-1"></i> Ijazah
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
                        <!-- S2 Tabs -->
                        <?php if ($isS3): ?>
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
                        <!-- Rekomendasi Tab (conditional) -->
                        <?php if (!empty($p['rekomendasi_filename'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-rekomendasi">
                                    <i class="fas fa-file-signature mr-1"></i> Rekomendasi
                                    <i class="fas fa-check-circle text-success ml-1"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <!-- Tab Content -->
                    <div class="tab-content mt-4">
                        <!-- Foto Tab -->
                        <div class="tab-pane fade show active" id="tab-foto">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <?php if (!empty($p['photo_filename'])): ?>
                                        <img src="<?php echo $photoUrl; ?>" alt="Foto" class="img-thumbnail"
                                            style="max-width:100%;max-height:300px">
                                        <div class="mt-2"><small class="text-success"><i class="fas fa-check-circle"></i>
                                                Tersedia</small></div>
                                    <?php else: ?>
                                        <div class="border p-4 rounded"><i
                                                class="fas fa-user-circle fa-5x text-muted mb-2"></i>
                                            <p class="text-muted mb-0"><small>Belum ada</small></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-outline-dark btn-sm"
                                            onclick="rotateDoc('foto')">
                                            <i class="fas fa-sync-alt mr-1"></i> Putar Foto (Permanen)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- KTP Tab -->
                        <div class="tab-pane fade" id="tab-ktp">
                            <div class="row">
                                <div
                                    class="<?php echo !empty($p['ktp_filename']) ? 'col-md-9' : 'col-md-12'; ?> text-center">
                                    <?php if (!empty($p['ktp_filename'])): ?>
                                        <div class="document-viewer-container border rounded bg-light p-2 mb-3">
                                            <img src="<?php echo $ktpUrl; ?>" alt="KTP" class="img-thumbnail" id="img-ktp"
                                                style="max-width:100%;max-height:1000px">
                                        </div>
                                        <div class="mt-2 text-left ml-2"><small class="text-success font-weight-bold"><i
                                                    class="fas fa-check-circle mr-1"></i>
                                                Tersedia</small></div>
                                    <?php else: ?>
                                        <div class="border p-5 rounded bg-light mb-3"><i
                                                class="fas fa-id-card fa-5x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0"><strong>Belum ada dokumen KTP</strong></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo !empty($p['ktp_filename']) ? 'col-md-3' : 'col-md-12'; ?>">
                                    <div class="card shadow-none border">
                                        <div class="card-header bg-light">
                                            <h3 class="card-title text-sm font-weight-bold"><i
                                                    class="fas fa-cog mr-1"></i> Kelola KTP</h3>
                                        </div>
                                        <div class="card-body p-3 text-center">
                                            <p class="small text-muted mb-3">Tools</p>
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block"
                                                onclick="rotateDoc('ktp')">
                                                <i class="fas fa-sync-alt mr-1"></i> Putar
                                            </button>
                                            <div class="mt-2 btn-group w-100">
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="zoomDoc('ktp', 0.2)"><i
                                                        class="fas fa-search-plus"></i></button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="zoomDoc('ktp', -0.2)"><i
                                                        class="fas fa-search-minus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Ijazah Tab -->
                        <div class="tab-pane fade" id="tab-ijazah">
                            <div class="row">
                                <div
                                    class="<?php echo !empty($p['ijazah_filename']) ? 'col-md-9' : 'col-md-12'; ?> text-center">
                                    <?php if (!empty($p['ijazah_filename'])): ?>
                                        <div class="document-viewer-container border rounded bg-light p-2 mb-3">
                                            <img src="<?php echo $ijazahUrl; ?>" alt="Ijazah" class="img-thumbnail"
                                                id="img-ijazah" style="max-width:100%;max-height:1000px">
                                        </div>
                                        <div class="mt-2 text-left ml-2"><small class="text-success font-weight-bold"><i
                                                    class="fas fa-check-circle mr-1"></i>
                                                Tersedia</small></div>
                                    <?php else: ?>
                                        <div class="border p-5 rounded bg-light mb-3"><i
                                                class="fas fa-graduation-cap fa-5x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0"><strong>Belum ada dokumen Ijazah</strong></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo !empty($p['ijazah_filename']) ? 'col-md-3' : 'col-md-12'; ?>">
                                    <div class="card shadow-none border">
                                        <div class="card-header bg-light">
                                            <h3 class="card-title text-sm font-weight-bold"><i
                                                    class="fas fa-cog mr-1"></i> Kelola Ijazah</h3>
                                        </div>
                                        <div class="card-body p-3 text-center">
                                            <p class="small text-muted mb-3">Tools</p>
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-block"
                                                onclick="rotateDoc('ijazah')">
                                                <i class="fas fa-sync-alt mr-1"></i> Putar
                                            </button>
                                            <div class="mt-2 btn-group w-100">
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="zoomDoc('ijazah', 0.2)"><i
                                                        class="fas fa-search-plus"></i></button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="zoomDoc('ijazah', -0.2)"><i
                                                        class="fas fa-search-minus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Transkrip Tab -->
                        <div class="tab-pane fade" id="tab-transkrip">
                            <div class="row">
                                <div
                                    class="<?php echo !empty($p['transkrip_filename']) ? 'col-md-9' : 'col-md-12'; ?> text-center">
                                    <?php if (!empty($p['transkrip_filename'])): ?>
                                        <div class="document-viewer-container border rounded bg-light p-2 mb-3"
                                            style="height: 800px;">
                                            <!-- PDF.js Viewer -->
                                            <div class="pdf-container" data-pdf-url="<?php echo $transkripUrl; ?>"
                                                style="width: 100%; height: 100%;">
                                                <div class="pdf-toolbar">
                                                    <button class="pdf-prev btn btn-sm btn-secondary">◄</button>
                                                    <span class="pdf-page-info mx-2">
                                                        <span class="pdf-page-num">1</span> / <span
                                                            class="pdf-page-count">-</span>
                                                    </span>
                                                    <button class="pdf-next btn btn-sm btn-secondary">►</button>
                                                    <button class="pdf-zoom-out btn btn-sm btn-info ml-2">-</button>
                                                    <button class="pdf-zoom-in btn btn-sm btn-info">+</button>
                                                </div>
                                                <div class="pdf-canvas-wrapper" style="flex: 1; overflow: auto;">
                                                    <canvas class="pdf-canvas"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-left ml-2">
                                            <small class="text-success font-weight-bold"><i
                                                    class="fas fa-check-circle mr-1"></i> Tersedia</small>
                                            <a href="<?php echo $transkripUrl; ?>" target="_blank"
                                                class="btn btn-xs btn-outline-primary ml-2">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="border p-5 rounded bg-light mb-3"><i
                                                class="fas fa-file-pdf fa-5x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0"><strong>Belum ada dokumen Transkrip</strong></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo !empty($p['transkrip_filename']) ? 'col-md-3' : 'col-md-12'; ?>">
                                    <div class="card shadow-none border">
                                        <div class="card-header bg-light">
                                            <h3 class="card-title text-sm font-weight-bold"><i
                                                    class="fas fa-cog mr-1"></i> Kelola Transkrip</h3>
                                        </div>
                                        <div class="card-body p-3 text-center">
                                            <p class="small text-muted mb-3">Gunakan Document Helper untuk mengelola
                                                file ini.</p>
                                            <a href="/admin/document-helper" class="btn btn-primary btn-sm btn-block">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka Helper
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Ijazah S2 Tab -->
                        <?php if ($isS3): ?>
                            <div class="tab-pane fade" id="tab-ijazah-s2">
                                <div class="row">
                                    <div
                                        class="<?php echo !empty($p['ijazah_s2_filename']) ? 'col-md-9' : 'col-md-12'; ?> text-center">
                                        <?php if (!empty($p['ijazah_s2_filename'])): ?>
                                            <div class="document-viewer-container border rounded bg-light p-2 mb-3">
                                                <img src="<?php echo $ijazahS2Url; ?>" alt="Ijazah S2" class="img-thumbnail"
                                                    style="max-width:100%;max-height:1000px">
                                            </div>
                                            <div class="mt-2 text-left ml-2"><small class="text-success font-weight-bold"><i
                                                        class="fas fa-check-circle mr-1"></i>
                                                    Tersedia</small></div>
                                        <?php else: ?>
                                            <div class="border p-5 rounded bg-light mb-3"><i
                                                    class="fas fa-graduation-cap fa-5x text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0"><strong>Belum ada dokumen Ijazah S2</strong></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="<?php echo !empty($p['ijazah_s2_filename']) ? 'col-md-3' : 'col-md-12'; ?>">
                                        <div class="card shadow-none border">
                                            <div class="card-header bg-light">
                                                <h3 class="card-title text-sm font-weight-bold"><i
                                                        class="fas fa-cog mr-1"></i> Kelola Ijazah S2</h3>
                                            </div>
                                            <div class="card-body p-3 text-center">
                                                <p class="small text-muted mb-3">Gunakan Document Helper untuk mengelola
                                                    file ini.</p>
                                                <a href="/admin/document-helper" class="btn btn-primary btn-sm btn-block">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Helper
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transkrip S2 Tab -->
                            <div class="tab-pane fade" id="tab-transkrip-s2">
                                <div class="row">
                                    <div
                                        class="<?php echo !empty($p['transkrip_s2_filename']) ? 'col-md-9' : 'col-md-12'; ?> text-center">
                                        <?php if (!empty($p['transkrip_s2_filename'])): ?>
                                            <div class="document-viewer-container border rounded bg-light p-2 mb-3"
                                                style="height: 800px;">
                                                <!-- PDF.js Viewer -->
                                                <div class="pdf-container" data-pdf-url="<?php echo $transkripS2Url; ?>"
                                                    style="width: 100%; height: 100%;">
                                                    <div class="pdf-toolbar">
                                                        <button class="pdf-prev btn btn-sm btn-secondary">◄</button>
                                                        <span class="pdf-page-info mx-2">
                                                            <span class="pdf-page-num">1</span> / <span
                                                                class="pdf-page-count">-</span>
                                                        </span>
                                                        <button class="pdf-next btn btn-sm btn-secondary">►</button>
                                                        <button class="pdf-zoom-out btn btn-sm btn-info ml-2">-</button>
                                                        <button class="pdf-zoom-in btn btn-sm btn-info">+</button>
                                                    </div>
                                                    <div class="pdf-canvas-wrapper" style="flex: 1; overflow: auto;">
                                                        <canvas class="pdf-canvas"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-left ml-2">
                                                <small class="text-success font-weight-bold"><i
                                                        class="fas fa-check-circle mr-1"></i> Tersedia</small>
                                                <a href="<?php echo $transkripS2Url; ?>" target="_blank"
                                                    class="btn btn-xs btn-outline-primary ml-2">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="border p-5 rounded bg-light mb-3"><i
                                                    class="fas fa-file-pdf fa-5x text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0"><strong>Belum ada dokumen Transkrip S2</strong></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div
                                        class="<?php echo !empty($p['transkrip_s2_filename']) ? 'col-md-3' : 'col-md-12'; ?>">
                                        <div class="card shadow-none border">
                                            <div class="card-header bg-light">
                                                <h3 class="card-title text-sm font-weight-bold"><i
                                                        class="fas fa-cog mr-1"></i> Kelola Transkrip S2</h3>
                                            </div>
                                            <div class="card-body p-3 text-center">
                                                <p class="small text-muted mb-3">Gunakan Document Helper untuk mengelola
                                                    file ini.</p>
                                                <a href="/admin/document-helper" class="btn btn-primary btn-sm btn-block">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Helper
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Rekomendasi Tab Content -->
                        <?php if (!empty($p['rekomendasi_filename'])):
                            // Resolve rekomendasi URL
                            $rekomendasiFilename = $p['rekomendasi_filename'];
                            if (strpos($rekomendasiFilename, 'documents/') !== false) {
                                $rekomendasiUrl = '/storage/' . $rekomendasiFilename;
                            } else {
                                $rekomendasiUrl = '/storage/documents/rekomendasi/' . $rekomendasiFilename;
                            }
                            ?>
                            <div class="tab-pane fade" id="tab-rekomendasi">
                                <div class="row">
                                    <div class="col-md-9 text-center">
                                        <div class="document-viewer-container border rounded bg-light p-2 mb-3"
                                            style="height: 800px;">
                                            <!-- PDF.js Viewer -->
                                            <div class="pdf-container" data-pdf-url="<?php echo $rekomendasiUrl; ?>"
                                                style="width: 100%; height: 100%;">
                                                <div class="pdf-toolbar">
                                                    <button class="pdf-prev btn btn-sm btn-secondary">◄</button>
                                                    <span class="pdf-page-info mx-2">
                                                        <span class="pdf-page-num">1</span> / <span
                                                            class="pdf-page-count">-</span>
                                                    </span>
                                                    <button class="pdf-next btn btn-sm btn-secondary">►</button>
                                                    <button class="pdf-zoom-out btn btn-sm btn-info ml-2">-</button>
                                                    <button class="pdf-zoom-in btn btn-sm btn-info">+</button>
                                                </div>
                                                <div class="pdf-canvas-wrapper" style="flex: 1; overflow: auto;">
                                                    <canvas class="pdf-canvas"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-left ml-2">
                                            <small class="text-success font-weight-bold"><i
                                                    class="fas fa-check-circle mr-1"></i> Tersedia</small>
                                            <a href="<?php echo $rekomendasiUrl; ?>" target="_blank"
                                                class="btn btn-xs btn-outline-primary ml-2">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card shadow-none border">
                                            <div class="card-header bg-light">
                                                <h3 class="card-title text-sm font-weight-bold"><i
                                                        class="fas fa-cog mr-1"></i> Kelola Rekomendasi</h3>
                                            </div>
                                            <div class="card-body p-3 text-center">
                                                <p class="small text-muted mb-3">Gunakan Document Helper untuk mengelola
                                                    file ini.</p>
                                                <a href="/admin/document-helper" class="btn btn-primary btn-sm btn-block">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Helper
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Auto-download All -->
                    <hr class="my-4">
                    <div class="text-center">
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle mr-2"></i>
                            Sinkronisasi Dokumen Server sekarang dipusatkan di halaman <strong>Document Helper</strong>.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer / Action Buttons -->
            <div class="card premium-card shadow-sm border-0 mb-5">
                <div class="card-body p-4 d-flex justify-content-between align-items-center bg-white">
                    <a href="/admin/participants" class="btn btn-link text-muted font-weight-bold">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                    </a>
                    <button type="submit" class="btn btn-primary btn-premium px-5 shadow">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    // JS Logic for View Rotation (Visual only, no persistence)
    // removed CRUD functions uploadDoc, deleteDoc, autoDownloadAllDocs

    // State to track rotation for each type
    const rotationState = {
        foto: 0,
        ktp: 0,
        ijazah: 0
    };

    function rotateView(type) {
        // Increment by 90 degrees
        rotationState[type] = (rotationState[type] + 90) % 360;

        // Find the image in the current tab
        const tabPane = document.getElementById('tab-' + type);
        const img = tabPane.querySelector('img.img-thumbnail');

        if (img) {
            img.style.transition = 'transform 0.3s ease';
            img.style.transform = `rotate(${rotationState[type]}deg)`;

            // Adjust margin to prevent overlap if rotated 90/270
            if (rotationState[type] === 90 || rotationState[type] === 270) {
                // If the image is wider than it is tall, we might need extra vertical padding
                // But for most KTP/Photos, simple rotation is enough.
                // We'll add some margin to ensure it doesn't clip
                img.style.margin = '40px 0';
            } else {
                img.style.margin = '0';
            }
        }
    }
</script>

</script>

<script>
    function rotateDoc(type) {
        if (!confirm('Putar gambar 90 derajat searah jarum jam? (Aksi ini permanen, halaman akan direload)')) return;

        // Disable button/Show spinner...
        const btn = event.target.closest('button');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        }

        fetch(`/admin/participants/<?= $p['id'] ?>/rotate-doc/${type}`, {
            method: 'POST'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal memutar gambar: ' + (data.message || 'Unknown error'));
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Putar';
                    }
                }
            })
            .catch(e => {
                alert('Error: ' + e);
                if (btn) btn.disabled = false;
            });
    }

    // Simple Zoom Logic for Edit Page
    let editScales = {};
    function zoomDoc(type, delta) {
        const img = document.getElementById('img-' + type);
        if (!img) return;

        if (!editScales[type]) editScales[type] = 1;
        editScales[type] += delta;
        if (editScales[type] < 0.2) editScales[type] = 0.2;

        img.style.transform = `scale(${editScales[type]})`;
        img.style.transition = 'transform 0.2s';
        img.style.transformOrigin = 'top center'; // Better for long documents
    }
</script>

<!-- PDF.js Library -->
<script src="/public/js/pdf.min.js"></script>
<script src="/public/js/pdf-viewer.js"></script>

<script>
    // Initialize PDF viewers when tab is shown
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize visible PDF viewers
        const visiblePdfContainers = document.querySelectorAll('.tab-pane.active .pdf-container[data-pdf-url]');
        visiblePdfContainers.forEach(container => {
            if (!container.dataset.initialized) {
                new PDFViewer(container);
                container.dataset.initialized = 'true';
            }
        });

        // Initialize PDF viewers when tab is clicked
        const docTabs = document.querySelectorAll('#docTabs a[data-toggle="tab"]');
        docTabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                const targetId = e.target.getAttribute('href');
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    const pdfContainer = targetPane.querySelector('.pdf-container[data-pdf-url]');
                    if (pdfContainer && !pdfContainer.dataset.initialized) {
                        new PDFViewer(pdfContainer);
                        pdfContainer.dataset.initialized = 'true';
                    }
                }
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>