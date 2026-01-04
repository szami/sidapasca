<?php ob_start(); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Pengaturan Aplikasi</h3>
            </div>
            <form action="/admin/settings/save" method="POST" enctype="multipart/form-data">
                <div class="card-body">
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
                                <small class="text-muted">Format: PNG, JPG (Pekerjaan terbaik pada aspek rasio 1:1 atau
                                    horizontal)</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Favicon</label>
                                <?php $currentFavicon = \App\Models\Setting::get('app_favicon'); ?>
                                <?php if ($currentFavicon): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo $currentFavicon; ?>" alt="Favicon" class="img-thumbnail"
                                            style="max-height: 32px;">
                                    </div>
                                <?php endif; ?>
                                <div class="custom-file">
                                    <input type="file" name="app_favicon" class="custom-file-input" id="faviconFile"
                                        accept="image/x-icon,image/png">
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
                            <option value="Asia/Makassar" <?php echo $tz == 'Asia/Makassar' ? 'selected' : ''; ?>>WITA
                                (Asia/Makassar)</option>
                            <option value="Asia/Jakarta" <?php echo $tz == 'Asia/Jakarta' ? 'selected' : ''; ?>>WIB
                                (Asia/Jakarta)</option>
                            <option value="Asia/Jayapura" <?php echo $tz == 'Asia/Jayapura' ? 'selected' : ''; ?>>WIT
                                (Asia/Jayapura)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitch1"
                                name="allow_exam_card_download" value="1" <?php echo ($allow_download ?? '0') == '1' ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="customSwitch1">Izinkan Peserta Download Kartu
                                Ujian</label>
                        </div>
                        <small class="text-muted">Jika diaktifkan, tombol download akan muncul di dashboard
                            peserta.</small>
                    </div>

                    <div class="form-group border-top pt-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="customSwitchDelete"
                                name="allow_delete" value="1" <?php echo ($allow_delete ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label class="custom-control-label text-danger" for="customSwitchDelete">Tampilkan Tombol
                                Hapus (Danger Zone)</label>
                        </div>
                        <small class="text-muted">Jika dinonaktifkan, tombol hapus (tong sampah) akan disembunyikan dari
                            semua halaman admin (Daftar Peserta & User).</small>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Backup Database</h3>
            </div>
            <div class="card-body">
                <h5>Download Backup Database</h5>
                <p class="text-muted">Download file backup database SQLite. File backup dapat digunakan untuk restore
                    data jika terjadi kesalahan.</p>

                <div class="alert alert-info">
                    <i class="icon fas fa-info-circle"></i>
                    <strong>Rekomendasi:</strong><br>
                    • Lakukan backup secara berkala (minimal 1x seminggu)<br>
                    • Simpan backup di lokasi yang aman<br>
                    • Lakukan backup sebelum melakukan Clean Database<br>
                    • Nama file backup akan include timestamp
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
                <p class="text-muted">Upload file backup (.sqlite) untuk mengembalikan database ke kondisi backup
                    tersebut.</p>

                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>PERHATIAN!</strong><br>
                    • Database saat ini akan otomatis di-backup terlebih dahulu<br>
                    • Semua data saat ini akan diganti dengan data dari backup<br>
                    • Proses restore tidak dapat di-undo<br>
                    • Pastikan file backup yang diupload benar dan valid
                </div>

                <form action="/admin/settings/restore-database" method="POST" enctype="multipart/form-data"
                    onsubmit="return confirmRestore()">
                    <div class="form-group">
                        <label>Pilih File Backup (.sqlite)</label>
                        <div class="custom-file">
                            <input type="file" name="backup_file" class="custom-file-input" id="backupFile"
                                accept=".sqlite" required>
                            <label class="custom-file-label" for="backupFile">Pilih file...</label>
                        </div>
                        <small class="form-text text-muted">Hanya file dengan ekstensi .sqlite yang diterima</small>
                    </div>

                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-upload"></i> Restore Database dari Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
            </div>
            <div class="card-body">
                <h5>Clean Database per Semester</h5>
                <p class="text-muted">Menghapus <strong>SEMUA</strong> data peserta pada semester yang dipilih. <strong
                        class="text-danger">TINDAKAN INI TIDAK DAPAT DIBATALKAN!</strong></p>

                <form action="/admin/settings/clean-semester" method="POST" onsubmit="return confirmClean()">
                    <div class="form-group">
                        <label>Pilih Semester</label>
                        <select name="semester_id" class="form-control" required>
                            <option value="">-- Pilih Semester --</option>
                            <?php if (!empty($semesters)): ?>
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
                        • Data yang terhapus tidak dapat dikembalikan<br>
                        • Pastikan Anda sudah melakukan backup database<br>
                        • Semester yang aktif juga dapat di-clean
                    </div>

                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fas fa-trash-alt"></i> Clean Database Semester
                    </button>
                </form>

                <hr class="mt-4">

                <h5>Pemeliharaan Database</h5>
                <p class="text-muted small">
                    Fitur ini akan melakukan <strong>VACUUM</strong> untuk mengecilkan ukuran file database dan
                    <strong>ANALYZE</strong> untuk memperbarui statistik pencarian.
                </p>

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'optimized'): ?>
                    <div class="alert alert-success alert-dismissible py-2 small">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-check"></i> Database Berhasil Dioptimasi!
                    </div>
                <?php endif; ?>

                <a href="/admin/settings/optimize" class="btn btn-primary btn-block"
                    onclick="return confirm('Mulai proses optimasi database?')">
                    <i class="fas fa-magic mr-1"></i> Optimasi & Perbaikan Database
                </a>
            </div>
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>