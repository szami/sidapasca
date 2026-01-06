<?php ob_start(); ?>
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
                                            <img src="/storage/photos/<?php echo $p['photo_filename']; ?>" alt="Foto"
                                                class="rounded shadow-sm border"
                                                style="width: 120px; height: 160px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                                style="width: 120px; height: 160px;">
                                                <i class="fas fa-user-circle fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-xs btn-outline-primary"
                                                onclick="document.querySelector('a[href=\'#tab-foto\']').click(); document.getElementById('docTabs').scrollIntoView();">
                                                <i class="fas fa-edit"></i> Edit Foto
                                            </button>
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
                    </ul>
                    <!-- Tab Content -->
                    <div class="tab-content mt-4">
                        <!-- Foto Tab -->
                        <div class="tab-pane fade show active" id="tab-foto">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <?php if (!empty($p['photo_filename'])): ?>
                                        <img src="/storage/photos/<?php echo $p['photo_filename']; ?>" alt="Foto"
                                            class="img-thumbnail" style="max-width:100%;max-height:300px">
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
                                    <div class="alert alert-info mb-3"><strong><i class="fas fa-info-circle mr-1"></i>
                                            Info:</strong> JPG/PNG, maks 2MB</div>
                                    <input type="file" class="form-control-file mb-3" id="foto-input" accept="image/*">
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="uploadDoc(<?php echo $p['id']; ?>, 'foto')"><i
                                            class="fas fa-upload mr-1"></i> Upload</button>
                                    <?php if (!empty($p['photo_filename'])): ?>
                                        <div class="btn-group ml-2" role="group">
                                            <button type="button" class="btn btn-secondary btn-sm" title="Rotate View"
                                                onclick="rotateView('foto')">
                                                <i class="fas fa-sync-alt mr-1"></i> Rotate View
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm ml-2"
                                            onclick="deleteDoc(<?php echo $p['id']; ?>, 'foto')"><i
                                                class="fas fa-trash mr-1"></i> Hapus</button>
                                    <?php endif; ?>
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
                                            <img src="/storage/documents/ktp/<?php echo $p['ktp_filename']; ?>" alt="KTP"
                                                class="img-thumbnail" style="max-width:100%;max-height:1000px">
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
                                        <div class="card-body p-3">
                                            <div class="alert alert-info py-2 px-3 text-xs mb-3">
                                                <i class="fas fa-info-circle mr-1"></i> JPG/PNG, maks 5MB
                                            </div>
                                            <input type="file" class="form-control-file mb-3 border p-1 rounded text-sm"
                                                id="ktp-input" accept="image/*">

                                            <div class="d-flex flex-column gap-2">
                                                <button type="button" class="btn btn-primary btn-sm btn-block mb-2"
                                                    onclick="uploadDoc(<?php echo $p['id']; ?>, 'ktp')">
                                                    <i class="fas fa-upload mr-1"></i> Ganti/Upload
                                                </button>

                                                <?php if (!empty($p['ktp_filename'])): ?>
                                                    <button type="button" class="btn btn-info btn-sm btn-block mb-2"
                                                        title="Rotate View" onclick="rotateView('ktp')">
                                                        <i class="fas fa-sync-alt mr-1"></i> Putar Tampilan
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-block"
                                                        onclick="deleteDoc(<?php echo $p['id']; ?>, 'ktp')">
                                                        <i class="fas fa-trash mr-1"></i> Hapus File
                                                    </button>
                                                <?php endif; ?>
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
                                            <img src="/storage/documents/ijazah/<?php echo $p['ijazah_filename']; ?>"
                                                alt="Ijazah" class="img-thumbnail" style="max-width:100%;max-height:1000px">
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
                                        <div class="card-body p-3">
                                            <div class="alert alert-info py-2 px-3 text-xs mb-3">
                                                <i class="fas fa-info-circle mr-1"></i> JPG/PNG, maks 5MB
                                            </div>
                                            <input type="file" class="form-control-file mb-3 border p-1 rounded text-sm"
                                                id="ijazah-input" accept="image/*">

                                            <div class="d-flex flex-column gap-2">
                                                <button type="button" class="btn btn-primary btn-sm btn-block mb-2"
                                                    onclick="uploadDoc(<?php echo $p['id']; ?>, 'ijazah')">
                                                    <i class="fas fa-upload mr-1"></i> Ganti/Upload
                                                </button>

                                                <?php if (!empty($p['ijazah_filename'])): ?>
                                                    <button type="button" class="btn btn-info btn-sm btn-block mb-2"
                                                        title="Rotate View" onclick="rotateView('ijazah')">
                                                        <i class="fas fa-sync-alt mr-1"></i> Putar Tampilan
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-block"
                                                        onclick="deleteDoc(<?php echo $p['id']; ?>, 'ijazah')">
                                                        <i class="fas fa-trash mr-1"></i> Hapus File
                                                    </button>
                                                <?php endif; ?>
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
                                            <iframe
                                                src="/storage/documents/transkrip/<?php echo $p['transkrip_filename']; ?>"
                                                width="100%" height="100%" style="border: none;"></iframe>
                                        </div>
                                        <div class="mt-2 text-left ml-2">
                                            <small class="text-success font-weight-bold"><i
                                                    class="fas fa-check-circle mr-1"></i> Tersedia</small>
                                            <a href="/storage/documents/transkrip/<?php echo $p['transkrip_filename']; ?>"
                                                target="_blank" class="btn btn-xs btn-outline-primary ml-2">
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
                                        <div class="card-body p-3">
                                            <div class="alert alert-info py-2 px-3 text-xs mb-3">
                                                <i class="fas fa-info-circle mr-1"></i> PDF, maks 10MB
                                            </div>
                                            <input type="file" class="form-control-file mb-3 border p-1 rounded text-sm"
                                                id="transkrip-input" accept="application/pdf">

                                            <div class="d-flex flex-column gap-2">
                                                <button type="button" class="btn btn-primary btn-sm btn-block mb-2"
                                                    onclick="uploadDoc(<?php echo $p['id']; ?>, 'transkrip')">
                                                    <i class="fas fa-upload mr-1"></i> Ganti/Upload
                                                </button>

                                                <?php if (!empty($p['transkrip_filename'])): ?>
                                                    <button type="button" class="btn btn-danger btn-sm btn-block"
                                                        onclick="deleteDoc(<?php echo $p['id']; ?>, 'transkrip')">
                                                        <i class="fas fa-trash mr-1"></i> Hapus File
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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
                                                <img src="/storage/documents/ijazah_s2/<?php echo $p['ijazah_s2_filename']; ?>"
                                                    alt="Ijazah S2" class="img-thumbnail"
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
                                            <div class="card-body p-3">
                                                <div class="alert alert-info py-2 px-3 text-xs mb-3">
                                                    <i class="fas fa-info-circle mr-1"></i> JPG/PNG, maks 5MB
                                                </div>
                                                <input type="file" class="form-control-file mb-3 border p-1 rounded text-sm"
                                                    id="ijazah-s2-input" accept="image/*">

                                                <div class="d-flex flex-column gap-2">
                                                    <button type="button" class="btn btn-primary btn-sm btn-block mb-2"
                                                        onclick="uploadDoc(<?php echo $p['id']; ?>, 'ijazah_s2')">
                                                        <i class="fas fa-upload mr-1"></i> Ganti/Upload
                                                    </button>

                                                    <?php if (!empty($p['ijazah_s2_filename'])): ?>
                                                        <button type="button" class="btn btn-info btn-sm btn-block mb-2"
                                                            title="Rotate View" onclick="rotateView('ijazah_s2')">
                                                            <i class="fas fa-sync-alt mr-1"></i> Putar Tampilan
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm btn-block"
                                                            onclick="deleteDoc(<?php echo $p['id']; ?>, 'ijazah_s2')">
                                                            <i class="fas fa-trash mr-1"></i> Hapus File
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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
                                                <iframe
                                                    src="/storage/documents/transkrip_s2/<?php echo $p['transkrip_s2_filename']; ?>"
                                                    width="100%" height="100%" style="border: none;"></iframe>
                                            </div>
                                            <div class="mt-2 text-left ml-2">
                                                <small class="text-success font-weight-bold"><i
                                                        class="fas fa-check-circle mr-1"></i> Tersedia</small>
                                                <a href="/storage/documents/transkrip_s2/<?php echo $p['transkrip_s2_filename']; ?>"
                                                    target="_blank" class="btn btn-xs btn-outline-primary ml-2">
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
                                            <div class="card-body p-3">
                                                <div class="alert alert-info py-2 px-3 text-xs mb-3">
                                                    <i class="fas fa-info-circle mr-1"></i> PDF, maks 10MB
                                                </div>
                                                <input type="file" class="form-control-file mb-3 border p-1 rounded text-sm"
                                                    id="transkrip-s2-input" accept="application/pdf">

                                                <div class="d-flex flex-column gap-2">
                                                    <button type="button" class="btn btn-primary btn-sm btn-block mb-2"
                                                        onclick="uploadDoc(<?php echo $p['id']; ?>, 'transkrip_s2')">
                                                        <i class="fas fa-upload mr-1"></i> Ganti/Upload
                                                    </button>

                                                    <?php if (!empty($p['transkrip_s2_filename'])): ?>
                                                        <button type="button" class="btn btn-danger btn-sm btn-block"
                                                            onclick="deleteDoc(<?php echo $p['id']; ?>, 'transkrip_s2')">
                                                            <i class="fas fa-trash mr-1"></i> Hapus File
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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
                        <button type="button" class="btn btn-success"
                            onclick="autoDownloadAllDocs(<?php echo $p['id']; ?>)">
                            <i class="fas fa-cloud-download-alt mr-2"></i> Download Semua Dokumen Otomatis
                        </button>
                        <p class="text-muted mt-2 mb-0"><small><i class="fas fa-magic mr-1"></i> Ambil 4 dokumen dari
                                sistem utama</small></p>
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
    // Generic document upload
    function uploadDoc(participantId, type) {
        const inputId = type + '-input';
        const input = document.getElementById(inputId);
        const file = input ? input.files[0] : null;

        if (!file) {
            alert('Pilih file terlebih dahulu');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        fetch(`/admin/participants/${participantId}/upload-doc/${type}`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan: ' + err.message);
            });
    }
    // Generic document delete
    function deleteDoc(participantId, type) {
        if (!confirm(`Yakin ingin menghapus ${type}?`)) return;

        fetch(`/admin/participants/${participantId}/delete-doc/${type}`, {
            method: 'DELETE'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan: ' + err.message);
            });
    }
    // Auto-download all documents
    function autoDownloadAllDocs(participantId) {
        if (!confirm('Download semua dokumen dari sistem utama?')) return;

        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Downloading...';

        fetch(`/admin/participants/${participantId}/auto-download-docs`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let msg = data.message + '\n\nDetail:\n';
                    msg += '- Foto: ' + (data.results.foto.success ? '✓' : '✗') + '\n';
                    msg += '- KTP: ' + (data.results.ktp.success ? '✓' : '✗') + '\n';
                    msg += '- Ijazah: ' + (data.results.ijazah.success ? '✓' : '✗') + '\n';
                    msg += '- Transkrip: ' + (data.results.transkrip.success ? '✓' : '✗');
                    alert(msg);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

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

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>