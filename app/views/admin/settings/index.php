<?php ob_start(); ?>
<div class="row">
    <div class="col-12 col-sm-12">
        <div class="card card-primary card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="settings-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab"
                            aria-controls="general" aria-selected="true">General Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="database-tab" data-toggle="pill" href="#database" role="tab"
                            aria-controls="database" aria-selected="false">Database Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="integrations-tab" data-toggle="pill" href="#integrations" role="tab"
                            aria-controls="integrations" aria-selected="false">Integrations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" id="danger-tab" data-toggle="pill" href="#danger" role="tab"
                            aria-controls="danger" aria-selected="false">Danger Zone</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="settings-tabContent">

                    <!-- TAB: General Settings -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <form action="/admin/settings/save" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Nama Aplikasi</label>
                                <input type="text" name="app_name" class="form-control"
                                    value="<?php echo \App\Models\Setting::get('app_name', 'SIDA Pasca ULM'); ?>">
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Logo Aplikasi</label>
                                        <?php $currentLogo = \App\Models\Setting::get('app_logo'); ?>
                                        <?php if ($currentLogo): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo $currentLogo; ?>" alt="Logo" class="img-thumbnail"
                                                    style="max-height: 100px;">
                                            </div>
                                        <?php endif; ?>
                                        <div class="custom-file">
                                            <input type="file" name="app_logo" class="custom-file-input" id="logoFile"
                                                accept="image/*">
                                            <label class="custom-file-label" for="logoFile">Pilih Logo...</label>
                                        </div>
                                        <small class="text-muted">Format: PNG, JPG (Pekerjaan terbaik pada aspek rasio
                                            1:1
                                            atau horizontal)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Favicon</label>
                                        <?php $currentFavicon = \App\Models\Setting::get('app_favicon'); ?>
                                        <?php if ($currentFavicon): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo $currentFavicon; ?>" alt="Favicon"
                                                    class="img-thumbnail" style="max-height: 32px;">
                                            </div>
                                        <?php endif; ?>
                                        <div class="custom-file">
                                            <input type="file" name="app_favicon" class="custom-file-input"
                                                id="faviconFile" accept="image/x-icon,image/png">
                                            <label class="custom-file-label" for="faviconFile">Pilih Favicon...</label>
                                        </div>
                                        <small class="text-muted">Format: ICO, PNG (Rekomendasi 32x32px)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Zona Waktu</label>
                                <select name="timezone" class="form-control">
                                    <?php $tz = \App\Models\Setting::get('timezone', 'Asia/Makassar'); ?>
                                    <option value="Asia/Makassar" <?php echo $tz == 'Asia/Makassar' ? 'selected' : ''; ?>>
                                        WITA (Asia/Makassar)</option>
                                    <option value="Asia/Jakarta" <?php echo $tz == 'Asia/Jakarta' ? 'selected' : ''; ?>>
                                        WIB
                                        (Asia/Jakarta)</option>
                                    <option value="Asia/Jayapura" <?php echo $tz == 'Asia/Jayapura' ? 'selected' : ''; ?>>
                                        WIT (Asia/Jayapura)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="customSwitch1"
                                        name="allow_exam_card_download" value="1" <?php echo ($allow_download ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="customSwitch1">Izinkan Peserta Download
                                        Kartu
                                        Ujian</label>
                                </div>
                                <small class="text-muted">Jika diaktifkan, tombol download akan muncul di dashboard
                                    peserta.</small>
                            </div>

                            <div class="form-group border-top pt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="customSwitchDelete"
                                        name="allow_delete" value="1" <?php echo ($allow_delete ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="custom-control-label text-danger" for="customSwitchDelete">Tampilkan
                                        Tombol Hapus (Danger Zone)</label>
                                </div>
                                <small class="text-muted">Jika dinonaktifkan, tombol hapus (tong sampah) akan
                                    disembunyikan
                                    dari semua halaman admin (Daftar Peserta & User).</small>
                            </div>

                            <div class="form-group border-top pt-3">
                                <h6 class="text-danger"><i class="fas fa-tools"></i> Mode Pemeliharaan (Maintenance
                                    Mode)
                                </h6>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="switchMaintenance"
                                        name="maintenance_mode" value="1" <?php echo ($maintenance_mode ?? 'off') == 'on' ? 'checked' : ''; ?>>
                                    <label class="custom-control-label font-weight-bold"
                                        for="switchMaintenance">Aktifkan
                                        Mode Pemeliharaan</label>
                                </div>
                                <small class="text-muted d-block mb-3">Jika diaktifkan, semua login (Peserta & Admin
                                    non-Superadmin) akan diblokir. Hanya akun <strong>Superadmin</strong> yang bisa
                                    login.</small>

                                <label for="maintenance_message">Pesan Pemeliharaan</label>
                                <textarea name="maintenance_message" id="maintenance_message" class="form-control"
                                    rows="2"><?php echo htmlspecialchars($maintenance_message ?? 'Sistem sedang dalam pemeliharaan. Silakan coba lagi beberapa saat lagi.'); ?></textarea>
                                <small class="text-muted">Pesan ini akan ditampilkan kepada siapa saja yang mencoba
                                    login
                                    saat sistem dikunci.</small>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: Database Management -->
                    <div class="tab-pane fade" id="database" role="tabpanel" aria-labelledby="database-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-database"></i> Backup Database</h3>
                                    </div>
                                    <div class="card-body">
                                        <h5>Download Backup Database</h5>
                                        <p class="text-muted">Download file backup database SQLite. File backup dapat
                                            digunakan untuk restore data jika terjadi kesalahan.</p>

                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info-circle"></i>
                                            <strong>Rekomendasi:</strong><br>
                                            • Lakukan backup secara berkala (minimal 1x seminggu)<br>
                                            • Simpan backup di lokasi yang aman<br>
                                            • Lakukan backup sebelum melakukan Clean Database<br>
                                        </div>

                                        <form action="/admin/settings/backup-database" method="POST">
                                            <button type="submit" class="btn btn-info btn-block btn-lg">
                                                <i class="fas fa-download"></i> Download Backup Sekarang
                                            </button>
                                        </form>

                                        <hr>

                                        <h6 class="text-muted mt-3">
                                            <i class="fas fa-info-circle"></i> Informasi Database
                                        </h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Lokasi Database</td>
                                                <td class="text-right"><code>storage/database.sqlite</code></td>
                                            </tr>
                                            <tr>
                                                <td>Ukuran Database</td>
                                                <td class="text-right">
                                                    <?php
                                                    $db_path = __DIR__ . '/../../../storage/database.sqlite';
                                                    $size = file_exists($db_path) ? filesize($db_path) : 0;
                                                    echo round($size / 1024, 2) . ' KB';
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-upload"></i> Restore Database</h3>
                                    </div>
                                    <div class="card-body">
                                        <h5>Upload File Backup untuk Restore</h5>
                                        <p class="text-muted">Upload file backup (.sqlite) untuk mengembalikan database
                                            ke
                                            kondisi backup tersebut.</p>

                                        <div class="alert alert-warning">
                                            <i class="icon fas fa-exclamation-triangle"></i>
                                            <strong>PERHATIAN!</strong><br>
                                            • Database saat ini akan otomatis di-backup terlebih dahulu<br>
                                            • Semua data saat ini akan diganti dengan data dari backup<br>
                                            • Proses restore tidak dapat di-undo
                                        </div>

                                        <form action="/admin/settings/restore-database" method="POST"
                                            enctype="multipart/form-data" onsubmit="return confirmRestore()">
                                            <div class="form-group">
                                                <label>Pilih File Backup (.sqlite)</label>
                                                <div class="custom-file">
                                                    <input type="file" name="backup_file" class="custom-file-input"
                                                        id="backupFile" accept=".sqlite" required>
                                                    <label class="custom-file-label" for="backupFile">Pilih
                                                        file...</label>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-warning btn-block">
                                                <i class="fas fa-upload"></i> Restore Database dari Backup
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <!-- Optimize DB Button in Database Tab -->
                                <div class="card card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-magic"></i> Database Optimization</h3>
                                    </div>
                                    <div class="card-body">
                                        <p>Fitur ini akan melakukan <strong>VACUUM</strong> untuk mengecilkan ukuran
                                            file database dan <strong>ANALYZE</strong> untuk memperbarui statistik
                                            pencarian.</p>

                                        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'optimized'): ?>
                                            <div class="alert alert-success alert-dismissible py-2 small">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-hidden="true">×</button>
                                                <i class="icon fas fa-check"></i> Database Berhasil Dioptimasi!
                                            </div>
                                        <?php endif; ?>

                                        <a href="/admin/settings/optimize" class="btn btn-secondary btn-block"
                                            onclick="return confirm('Mulai proses optimasi database?')">
                                            <i class="fas fa-magic mr-1"></i> Optimasi & Perbaikan Database
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: Integrations -->
                    <div class="tab-pane fade" id="integrations" role="tabpanel" aria-labelledby="integrations-tab">
                        <div class="card card-purple">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Konfigurasi Session Cookie
                                    (Admisipasca)</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i> Cookie ini digunakan untuk fitur
                                    <strong>Import Berkas System</strong>.
                                </div>

                                <?php
                                $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
                                $cookieStatus = !empty($sessionCookie) ? 'configured' : 'not_configured';
                                ?>

                                <div
                                    class="alert alert-<?php echo $cookieStatus === 'configured' ? 'success' : 'warning'; ?>">
                                    <strong>Status:</strong>
                                    <?php if ($cookieStatus === 'configured'): ?>
                                        <i class="fas fa-check-circle mr-1"></i> Session cookie sudah dikonfigurasi
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Session cookie belum dikonfigurasi
                                    <?php endif; ?>
                                </div>

                                <form id="cookieForm">
                                    <div class="form-group">
                                        <label>Session Cookie:</label>
                                        <textarea class="form-control" id="sessionCookie" rows="3"
                                            placeholder="ci_session=abc123; PHPSESSID=xyz789; ..."></textarea>
                                        <small class="form-text text-muted">Mendukung format <strong>Header
                                                String</strong>
                                            (standard) maupun <strong>JSON</strong> (dari EditThisCookie).</small>
                                    </div>
                                    <button type="submit" class="btn btn-purple">
                                        <i class="fas fa-save mr-2"></i>Simpan Session Cookie
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: Danger Zone -->
                    <div class="tab-pane fade" id="danger" role="tabpanel" aria-labelledby="danger-tab">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                            </div>
                            <div class="card-body">
                                <h5>Clean Database per Semester</h5>
                                <p class="text-muted">Menghapus <strong>SEMUA</strong> data peserta pada semester yang
                                    dipilih (beserta skor, berkas, dan log). <strong class="text-danger">TINDAKAN INI
                                        TIDAK SEPENUHNYA DAPAT
                                        DIBATALKAN (KECUALI RESTORE)!</strong></p>

                                <form action="/admin/settings/clean-semester" method="POST"
                                    onsubmit="return confirmClean()">
                                    <div class="form-group">
                                        <label>Pilih Semester</label>
                                        <select name="semester_id" class="form-control" required>
                                            <option value="">-- Pilih Semester --</option>
                                            <?php if (!empty($semesters) && is_array($semesters)): ?>
                                                <?php foreach ($semesters as $sem): ?>
                                                    <option value="<?php echo $sem['id']; ?>">
                                                        <?php echo $sem['nama']; ?>
                                                        <?php if ($sem['is_active']): ?>(Aktif)<?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="alert alert-danger">
                                        <i class="icon fas fa-ban"></i>
                                        <strong>PERINGATAN!</strong><br>
                                        • Semua data peserta akan dihapus secara permanen<br>
                                        • Termasuk Data Ujian, Verifikasi, dan Jawaban Survei<br>
                                        • Pastikan Anda sudah melakukan backup database<br>
                                    </div>

                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> Clean Database Semester
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<script>
    function confirmClean() {
        const semester = document.querySelector('select[name="semester_id"]');
        const semesterText = semester.options[semester.selectedIndex].text;

        if (!semester.value) {
            alert('Pilih semester terlebih dahulu!');
            return false;
        }

        // First confirmation
        if (!confirm('⚠️ PERINGATAN!\n\nAnda akan menghapus SEMUA data peserta di:\n' + semesterText + '\n\nTindakan ini TIDAK DAPAT DIBATALKAN!\n\nLanjutkan?')) {
            return false;
        }

        // Second confirmation with typing
        const confirmText = prompt('Untuk konfirmasi, ketik: HAPUS SEMUA\n\n(ketik dengan huruf kapital semua)');

        if (confirmText !== 'HAPUS SEMUA') {
            alert('Konfirmasi tidak sesuai. Proses dibatalkan.');
            return false;
        }

        // Final confirmation
        return confirm('INI ADALAH KONFIRMASI TERAKHIR!\n\nSemua data peserta di semester "' + semesterText + '" akan dihapus permanen.\n\nApakah Anda BENAR-BENAR YAKIN?');
    }

    function confirmRestore() {
        const fileInput = document.querySelector('input[name="backup_file"]');

        if (!fileInput.files || !fileInput.files[0]) {
            alert('Pilih file backup terlebih dahulu!');
            return false;
        }

        const fileName = fileInput.files[0].name;
        const fileExt = fileName.split('.').pop().toLowerCase();

        // Validate extension
        if (fileExt !== 'sqlite') {
            alert('File harus berekstensi .sqlite!\n\nFile yang dipilih: ' + fileName);
            return false;
        }

        // First confirmation
        if (!confirm('⚠️ PERINGATAN RESTORE DATABASE!\n\nAnda akan me-restore database dari file:\n' + fileName + '\n\nDatabase saat ini akan di-backup otomatis sebelum di-restore.\n\nLanjutkan?')) {
            return false;
        }

        // Second confirmation with typing
        const confirmText = prompt('Untuk konfirmasi, ketik: RESTORE DATABASE\n\n(ketik dengan huruf kapital semua)');

        if (confirmText !== 'RESTORE DATABASE') {
            alert('Konfirmasi tidak sesuai. Proses dibatalkan.');
            return false;
        }

        // Final confirmation
        return confirm('INI ADALAH KONFIRMASI TERAKHIR!\n\nDatabase akan di-restore dari file "' + fileName + '".\nSemua data saat ini akan terganti.\n\nApakah Anda BENAR-BENAR YAKIN?');
    }

    // Update custom file input label dynamically
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('custom-file-input')) {
            const fileName = e.target.files[0]?.name || 'Pilih file...';
            const label = e.target.nextElementSibling;
            if (label && label.classList.contains('custom-file-label')) {
                label.textContent = fileName;
            }
        }
    });

    // Save session cookie (Admisipasca)
    const cookieForm = document.getElementById('cookieForm');
    if (cookieForm) {
        cookieForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const cookie = document.getElementById('sessionCookie').value.trim();

            if (!cookie) {
                alert('Session cookie tidak boleh kosong');
                return;
            }

            // Show loading state
            const btn = cookieForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            btn.disabled = true;

            fetch('/admin/settings/save-cookie', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'session_cookie=' + encodeURIComponent(cookie)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let msg = data.message;
                        if (data.format_detected) {
                            msg += '\n\nFormat terdeteksi: ' + data.format_detected;
                        }
                        alert(msg);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>