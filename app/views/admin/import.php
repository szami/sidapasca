<?php ob_start(); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Import Data Peserta</h1>
    <p class="text-gray-600">Pastikan Anda memilih semester yang benar sebelum melakukan import.</p>
</div>

<!-- Import Form -->
<div class="row">
    <!-- Auto-Download Section (NEW) -->
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cloud-download-alt mr-2"></i>Import Otomatis
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Download dan import langsung dari server utama</p>

                <!-- Cookie Status -->
                <div class="alert alert-<?php echo $cookieStatus === 'configured' ? 'success' : 'warning'; ?> mb-3">
                    <strong><i class="fas fa-cookie mr-1"></i> Cookie Status:</strong>
                    <?php if ($cookieStatus === 'configured'): ?>
                        <span class="text-success">✓ Configured</span>
                    <?php else: ?>
                        <span class="text-warning">⚠ Belum dikonfigurasi.</span>
                        <a href="/admin/document-import" class="alert-link">Configure di sini</a>
                    <?php endif; ?>
                </div>

                <!-- Semester Selection -->
                <div class="form-group">
                    <label>Pilih Semester</label>
                    <select id="autoSemester" class="form-control">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?php echo $sem['id']; ?>" <?php echo $sem['is_active'] ? 'selected' : ''; ?>>
                                <?php echo $sem['kode'] . ' - ' . $sem['nama']; ?>
                                <?php echo $sem['is_active'] ? '(Aktif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Import Behavior Selector for Auto-Import (NEW) -->
                <div class="form-group">
                    <label><strong>Perilaku Import</strong></label>
                    <select id="autoBehavior" class="form-control">
                        <option value="insert_new_only" selected>
                            ✅ Hanya Import Data Baru
                        </option>
                        <option value="update_existing">
                            🔄 Update Data yang Ada
                        </option>
                        <option value="insert_and_update">
                            ↔️ Import Baru + Update
                        </option>
                    </select>
                    <small class="text-muted">
                        Pilih perilaku untuk data yang sudah ada di database
                    </small>
                </div>

                <!-- Download Buttons -->
                <div class="form-group">
                    <label>Pilih Data untuk Auto-Import</label>
                    <div class="btn-group-vertical w-100">
                        <button class="btn btn-outline-primary mb-2" onclick="autoImport('dikirim')" <?php echo $cookieStatus !== 'configured' ? 'disabled' : ''; ?>>
                            <i class="fas fa-file-import mr-2"></i>1. Formulir Masuk (Dikirim)
                        </button>
                        <button class="btn btn-outline-success mb-2" onclick="autoImport('lulus')" <?php echo $cookieStatus !== 'configured' ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-circle mr-2"></i>2. Lulus Administrasi
                        </button>
                        <button class="btn btn-outline-danger mb-2" onclick="autoImport('gagal')" <?php echo $cookieStatus !== 'configured' ? 'disabled' : ''; ?>>
                            <i class="fas fa-times-circle mr-2"></i>3. Gagal Administrasi
                        </button>
                        <button class="btn btn-outline-info" onclick="autoImport('kartu')" <?php echo $cookieStatus !== 'configured' ? 'disabled' : ''; ?>>
                            <i class="fas fa-id-card mr-2"></i>4. Nomor Peserta (Kartu)
                        </button>
                    </div>
                </div>

                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i> Auto-import akan download Excel dari server utama dan
                    langsung import ke database.
                </small>
            </div>
        </div>
    </div>

    <!-- Manual Upload Section (Existing) -->
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Import Data Peserta</h3>
            </div>
            <form action="/admin/import" method="POST" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="form-group">
                        <label>Pilih Semester</label>
                        <select name="semester_id" class="form-control">
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo $sem['id']; ?>" <?php echo $sem['is_active'] ? 'selected' : ''; ?>>
                                    <?php echo $sem['kode'] . ' - ' . $sem['nama']; ?>
                                    <?php echo $sem['is_active'] ? '(Aktif)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jenis File Import (Sesuai Export Sistem Utama)</label>

                        <!-- 1. Formulir Masuk -->
                        <div class="custom-control custom-radio mb-2">
                            <input class="custom-control-input" type="radio" id="typeFormulir" name="import_type"
                                value="formulir_masuk" checked>
                            <label for="typeFormulir" class="custom-control-label">
                                <strong>1. Formulir Masuk</strong><br>
                                <span class="text-muted text-sm font-weight-normal">Import biodata baru. Status Berkas
                                    diset <strong>Pending</strong>. Cek data pembayaran.</span>
                            </label>
                        </div>

                        <!-- 2. Lulus Berkas -->
                        <div class="custom-control custom-radio mb-2">
                            <input class="custom-control-input" type="radio" id="typeLulus" name="import_type"
                                value="lulus_berkas">
                            <label for="typeLulus" class="custom-control-label">
                                <strong>2. Data Lulus Administrasi Berkas</strong><br>
                                <span class="text-muted text-sm font-weight-normal">Update status menjadi
                                    <strong>Lulus</strong>. Mengambil data pembayaran (Bank/Kanal) dari file ini.</span>
                            </label>
                        </div>

                        <!-- 3. Gagal Berkas -->
                        <div class="custom-control custom-radio mb-2">
                            <input class="custom-control-input" type="radio" id="typeGagal" name="import_type"
                                value="gagal_berkas">
                            <label for="typeGagal" class="custom-control-label">
                                <strong>3. Data Gagal Administrasi Berkas</strong><br>
                                <span class="text-muted text-sm font-weight-normal">Update status menjadi
                                    <strong>Gagal</strong>.</span>
                            </label>
                        </div>

                        <!-- 4. Nomor Peserta -->
                        <div class="custom-control custom-radio mb-2">
                            <input class="custom-control-input" type="radio" id="typeNoPeserta" name="import_type"
                                value="nomor_peserta">
                            <label for="typeNoPeserta" class="custom-control-label">
                                <strong>4. Data Nomor Peserta</strong><br>
                                <span class="text-muted text-sm font-weight-normal">Hanya update <strong>Nomor
                                        Peserta</strong>. Mengabaikan kolom pembayaran (karena sering tidak lengkap di
                                    file ini).</span>
                            </label>
                        </div>

                        <hr>

                        <!-- Import Behavior Selector (NEW) -->
                        <div class="form-group mt-3">
                            <label><strong>Perilaku Import Data</strong></label>
                            <select name="import_behavior" class="form-control">
                                <option value="insert_new_only" selected>
                                    ✅ Hanya Import Data Baru (Skip yang sudah ada)
                                </option>
                                <option value="update_existing">
                                    🔄 Update Data yang Sudah Ada (Skip data baru)
                                </option>
                                <option value="insert_and_update">
                                    ↔️ Import Baru + Update yang Ada (Mode Lama)
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Insert New Only (Recommended):</strong> Data yang sudah ada di database akan
                                dilewati, hanya data baru yang di-import.<br>
                                <strong>Update Existing:</strong> Hanya update data yang sudah ada, skip data baru.<br>
                                <strong>Insert and Update:</strong> Import data baru DAN update data yang sudah ada
                                (behavior lama).
                            </small>
                        </div>

                        <hr>

                        <!-- Data Lama Checkbox -->
                        <div class="custom-control custom-checkbox mt-3">
                            <input class="custom-control-input" type="checkbox" id="isLegacy" name="is_legacy"
                                value="1">
                            <label for="isLegacy" class="custom-control-label text-danger">
                                <strong>Data Lama?</strong>
                            </label>
                            <br>
                            <small class="text-muted">Centang jika file menggunakan format lama (Kolom <strong>"Tempat,
                                    Tanggal Lahir"</strong> digabung contoh: "Banjarmasin, 10 Maret 1991").</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>File Excel (.xls, .xlsx)</label>
                        <div class="custom-file">
                            <input type="file" name="file" class="custom-file-input" id="customFile" accept=".xls,.xlsx"
                                required onchange="updateFileName(this)">
                            <label class="custom-file-label" for="customFile">Pilih file...</label>
                        </div>
                        <small class="text-muted">Mendukung format dari bank BNI (HTML/XLS) dan standar Excel.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Import Data</button>
                    <a href="/admin/download-template" class="btn btn-link">Download Template</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        var fileName = input.files[0].name;
        var label = input.nextElementSibling;
        label.innerText = fileName;
    }

    function autoImport(type) {
        const semesterId = document.getElementById('autoSemester').value;
        const importBehavior = document.getElementById('autoBehavior').value;

        const typeLabels = {
            'dikirim': 'Formulir Masuk',
            'lulus': 'Lulus Administrasi',
            'gagal': 'Gagal Administrasi',
            'kartu': 'Nomor Peserta'
        };

        const behaviorLabels = {
            'insert_new_only': 'Hanya Import Data Baru',
            'update_existing': 'Update Data yang Ada',
            'insert_and_update': 'Import Baru + Update'
        };

        if (!confirm(`Auto-import data ${typeLabels[type]}?\n\nPerilaku: ${behaviorLabels[importBehavior]}\n\nProses ini akan:\n1. Download Excel dari server utama\n2. Auto-import ke database`)) return;

        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Downloading...';

        fetch(`/admin/import/auto-download?type=${type}&semester_id=${semesterId}&import_behavior=${importBehavior}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let message = '✓ Berhasil!\n\n';
                    message += `📊 Ringkasan Import:\n`;
                    message += `✅ Data Baru: ${data.imported} record\n`;
                    if (data.skipped > 0) {
                        message += `⏭️ Data Dilewati: ${data.skipped} record\n`;
                    }
                    if (data.updated > 0) {
                        message += `🔄 Data Diupdate: ${data.updated} record\n`;
                    }
                    if (data.failed > 0) {
                        message += `❌ Data Gagal: ${data.failed} record\n`;
                    }
                    message += `\nType: ${data.type}\nMode: ${behaviorLabels[data.behavior]}`;

                    alert(message);
                    location.reload();
                } else {
                    alert('✗ Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                alert('✗ Error: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    $(document).ready(function () {
        // Filename update is handled by onchange inline
    });
</script>

<?php
$content = ob_get_clean();
// We don't need to append script outside, we put it inline or better yet, using a hook if layout supports it.
// AdminLTE wrapper usually just echos content.
include __DIR__ . '/../layouts/admin.php';
?>