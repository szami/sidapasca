<?php ob_start(); ?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-download mr-2"></i>Import Berkas Peserta</h2>

    <!-- Session Cookie Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-key mr-2"></i>Konfigurasi Session Cookie</h5>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="cookieMethodTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="extension-tab" data-toggle="tab" href="#extension" role="tab">
                        <i class="fab fa-chrome mr-1"></i> Cara Mudah (Extension)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="manual-tab" data-toggle="tab" href="#manual" role="tab">
                        <i class="fas fa-tools mr-1"></i> Cara Manual (Dev Tools)
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="cookieMethodTabContent">
                <!-- Extension Method -->
                <div class="tab-pane fade show active" id="extension" role="tabpanel">
                    <div class="alert alert-success">
                        <strong><i class="fas fa-rocket mr-2"></i>Cara Tercepat - Menggunakan Chrome Extension:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Install extension <strong>"EditThisCookie"</strong> dari <a
                                    href="https://chrome.google.com/webstore/detail/editthiscookie/fngmhnnpilhplaeedifhccceomclgfbg"
                                    target="_blank" class="text-white"><u>Chrome Web Store</u></a></li>
                            <li>Login ke <a href="https://admisipasca.ulm.ac.id/administrator" target="_blank"
                                    class="text-white"><u>sistem utama</u></a></li>
                            <li>Klik icon <strong>EditThisCookie</strong> di toolbar Chrome (icon kue)</li>
                            <li>Klik tombol <strong>"Export"</strong> (icon download)</li>
                            <li>Pilih format <strong>"Header String"</strong></li>
                            <li>Cookie otomatis ter-copy! Paste di form di bawah ⬇️</li>
                        </ol>
                        <div class="mt-2 p-2 bg-white text-dark rounded">
                            <small><strong>Format hasil:</strong>
                                <code>ci_session=abc123; PHPSESSID=xyz789; ...</code></small>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <strong><i class="fas fa-magic mr-1"></i>Auto-Detection:</strong>
                                Sistem otomatis mendeteksi format! Anda bisa paste format <strong>JSON</strong> atau
                                <strong>Header String</strong>, keduanya akan bekerja.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Manual Method -->
                <div class="tab-pane fade" id="manual" role="tabpanel">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle mr-2"></i>Cara Manual (Tanpa Extension):</strong>
                        <ol class="mb-0 mt-2">
                            <li>Login ke <a href="https://admisipasca.ulm.ac.id/administrator" target="_blank">sistem
                                    utama</a></li>
                            <li>Tekan <kbd>F12</kbd> untuk buka Developer Tools</li>
                            <li>Buka tab <strong>"Application"</strong> atau <strong>"Storage"</strong></li>
                            <li>Klik <strong>"Cookies"</strong> → pilih domain <code>admisipasca.ulm.ac.id</code></li>
                            <li>Copy semua cookie dalam format: <code>name1=value1; name2=value2</code></li>
                            <li>Paste di form di bawah</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="alert alert-<?php echo $cookieStatus === 'configured' ? 'success' : 'warning'; ?>">
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
                    <textarea class="form-control" id="sessionCookie" rows="4"
                        placeholder="ci_session=abc123; PHPSESSID=xyz789; ..."></textarea>
                    <small class="form-text text-muted">Cookie akan kedaluwarsa. Perbarui secara berkala jika
                        auto-download error.</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Simpan Session Cookie
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo $stats['total']; ?></h3>
                    <p class="mb-0">Total Peserta</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?php echo $stats['with_photo']; ?></h3>
                    <p class="mb-0">Sudah Ada Berkas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo $stats['without_photo']; ?></h3>
                    <p class="mb-0">Belum Ada Berkas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info"><?php echo $stats['scheduled_without_photo']; ?></h3>
                    <p class="mb-0">Terjadwal Tanpa Berkas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Modes -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-cloud-download-alt mr-2"></i>Mode Download</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-user-slash mr-2"></i>Download Peserta Tanpa Berkas</h5>
                            <p class="card-text">Download berkas untuk semua peserta yang belum memiliki dokumen lengkap</p>
                            <p class="mb-2"><strong>Target:</strong> <?php echo $stats['without_photo']; ?> peserta</p>
                            <button class="btn btn-primary" onclick="startDownload('without_photo')">
                                <i class="fas fa-download mr-2"></i>Mulai Download
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Download Peserta Terjadwal Tanpa Berkas</h5>
                            <p class="card-text">Download berkas untuk peserta yang sudah dijadwalkan tapi belum ada dokumen lengkap</p>
                            <p class="mb-2"><strong>Target:</strong> <?php echo $stats['scheduled_without_photo']; ?>
                                peserta</p>
                            <button class="btn btn-info" onclick="startDownload('scheduled_without_photo')">
                                <i class="fas fa-download mr-2"></i>Mulai Download
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users mr-2"></i>Download Semua Peserta Terjadwal
                            </h5>
                            <p class="card-text">Download berkas untuk semua peserta yang sudah dijadwalkan (termasuk yang sudah punya)</p>
                            <p class="mb-2"><strong>Target:</strong> <?php echo $stats['scheduled']; ?> peserta</p>
                            <button class="btn btn-success" onclick="startDownload('scheduled')">
                                <i class="fas fa-download mr-2"></i>Mulai Download
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-globe mr-2"></i>Download Semua Peserta</h5>
                            <p class="card-text">Download berkas untuk SEMUA peserta (termasuk yang sudah punya)</p>
                            <p class="mb-2"><strong>Target:</strong> <?php echo $stats['total']; ?> peserta</p>
                            <button class="btn btn-warning" onclick="startDownload('all')">
                                <i class="fas fa-download mr-2"></i>Mulai Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="card mt-4" id="progressCard" style="display: none;">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-spinner fa-spin mr-2"></i>Progress Download</h5>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 30px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span id="progressText">0%</span>
                </div>
            </div>
            <p class="mb-2"><strong>Status:</strong> <span id="statusText">Memulai...</span></p>
            <p class="mb-2"><strong>Progress:</strong> <span id="progressCount">0</span> / <span
                    id="totalCount">0</span></p>

            <div class="mt-3"
                style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                <div id="logContainer"></div>
            </div>

            <div class="mt-3" id="summarySection" style="display: none;">
                <hr>
                <h6><strong>Ringkasan:</strong></h6>
                <ul id="summaryList"></ul>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync mr-2"></i>Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Save session cookie
    document.getElementById('cookieForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const cookie = document.getElementById('sessionCookie').value.trim();

        if (!cookie) {
            alert('Session cookie tidak boleh kosong');
            return;
        }

        fetch('/admin/document-import/save-cookie', {
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
                }
            });
    });

    // Start download process
    function startDownload(mode) {
        if (!confirm('Mulai download berkas untuk mode "' + mode + '"?')) return;

        // Show progress card
        document.getElementById('progressCard').style.display = 'block';
        document.getElementById('summarySection').style.display = 'none';
        document.getElementById('logContainer').innerHTML = '';

        // Get participant list
        fetch('/admin/document-import/bulk?mode=' + mode)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Error: ' + data.message);
                    return;
                }

                processDownloads(data.participants, 0, {
                    success: 0,
                    failed: 0,
                    skipped: 0,
                    failedList: []
                });
            });
    }

    // Process downloads sequentially
    function processDownloads(participants, index, results) {
        const total = participants.length;
        document.getElementById('totalCount').textContent = total;

        if (index >= total) {
            // Complete
            showSummary(results);
            return;
        }

        const participant = participants[index];
        const progress = Math.round(((index + 1) / total) * 100);

        // Update progress
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressBar').setAttribute('aria-valuenow', progress);
        document.getElementById('progressText').textContent = progress + '%';
        document.getElementById('progressCount').textContent = index + 1;
        document.getElementById('statusText').textContent = 'Downloading: ' + participant.nama;

        // Download all documents (foto, KTP, ijazah, transkrip)
        fetch('/admin/participants/' + participant.id + '/auto-download-docs', {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    results.success++;
                    addLog('success', participant.nama + ' - Berhasil');
                } else {
                    results.failed++;
                    results.failedList.push(participant.nama + ': ' + data.message);
                    addLog('error', participant.nama + ' - ' + data.message);
                }
            })
            .catch(err => {
                results.failed++;
                results.failedList.push(participant.nama + ': ' + err.message);
                addLog('error', participant.nama + ' - Error: ' + err.message);
            })
            .finally(() => {
                // Process next
                setTimeout(() => processDownloads(participants, index + 1, results), 500);
            });
    }

    function addLog(type, message) {
        const log = document.getElementById('logContainer');
        const color = type === 'success' ? 'text-success' : 'text-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        log.innerHTML += `<div class="${color}"><i class="fas ${icon} mr-2"></i>${message}</div>`;
        log.scrollTop = log.scrollHeight;
    }

    function showSummary(results) {
        document.getElementById('statusText').textContent = 'Selesai!';
        document.getElementById('progressBar').classList.remove('bg-success');
        document.getElementById('progressBar').classList.add('bg-primary');

        const summaryList = document.getElementById('summaryList');
        summaryList.innerHTML = `
        <li class="text-success"><strong>Berhasil:</strong> ${results.success}</li>
        <li class="text-danger"><strong>Gagal:</strong> ${results.failed}</li>
    `;

        if (results.failedList.length > 0) {
            summaryList.innerHTML += '<li><strong>Detail Gagal:</strong><ul>';
            results.failedList.forEach(item => {
                summaryList.innerHTML += '<li class="text-danger">' + item + '</li>';
            });
            summaryList.innerHTML += '</ul></li>';
        }

        document.getElementById('summarySection').style.display = 'block';
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>