<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-archive mr-2"></i>
                    Download Berkas Peserta
                </h3>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            <i class="fas fa-filter mr-1"></i> Status Berkas
                        </label>
                        <select id="statusFilter" class="form-control">
                            <option value="all">📋 Semua Status</option>
                            <option value="lulus">✅ Lulus Berkas</option>
                            <option value="gagal">❌ Gagal Berkas</option>
                            <option value="pending">📝 Pending</option>
                            <option value="peserta_ujian">🎓 Peserta Ujian (Punya Nomor Peserta)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            <i class="fas fa-graduation-cap mr-1"></i> Program Studi
                        </label>
                        <select id="prodiFilter" class="form-control">
                            <option value="all">📚 Semua Prodi</option>
                            <?php foreach ($prodiList as $prodi): ?>
                                <option value="<?php echo $prodi['kode_prodi']; ?>">
                                    <?php echo htmlspecialchars($prodi['nama_prodi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar-alt mr-1"></i> Semester & Periode
                        </label>
                        <select id="semesterFilter" class="form-control">
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo $sem['id']; ?>" <?php echo ($sem['id'] == $activeSemester['id']) ? 'selected' : ''; ?>>
                                    <?php
                                    echo htmlspecialchars($sem['nama']);
                                    if (isset($sem['periode'])) {
                                        echo ' - Periode ' . $sem['periode'];
                                    }
                                    if ($sem['id'] == $activeSemester['id']) {
                                        echo ' (Active)';
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-12">
                        <button type="button" class="btn btn-info btn-lg" onclick="previewDownload()">
                            <i class="fas fa-eye mr-2"></i> Preview
                        </button>
                        <button type="button" class="btn btn-success btn-lg ml-2" id="downloadBtn"
                            onclick="downloadZip()" disabled>
                            <i class="fas fa-download mr-2"></i> Download ZIP
                        </button>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="previewSection" style="display: none;">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i> Preview Download</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Peserta:</strong> <span id="totalParticipants">0</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Estimasi Ukuran:</strong> <span id="estimatedSize">-</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong> <span id="previewStatus" class="badge badge-secondary">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm" id="previewTable">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>No</th>
                                    <th>No Peserta</th>
                                    <th>Nama</th>
                                    <th>Prodi</th>
                                    <th>Status</th>
                                    <th class="text-center">Form</th>
                                    <th class="text-center">KTP</th>
                                    <th class="text-center">Foto</th>
                                    <th class="text-center">Ijz S1</th>
                                    <th class="text-center">Trn S1</th>
                                    <th class="text-center">Kartu</th>
                                    <th class="text-center">S2</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Filled by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Progress Section -->
                <div id="progressSection" style="display: none;">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Memproses Download...
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="progress mb-3" style="height: 25px;">
                                <div id="progressBar"
                                    class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                    role="progressbar" style="width: 0%">0%</div>
                            </div>

                            <div class="alert alert-info">
                                <strong>Status:</strong> <span id="currentStatus">Memulai proses...</span>
                            </div>

                            <!-- Progress Log -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong><i class="fas fa-list-ul mr-2"></i>Progress Log</strong>
                                </div>
                                <div class="card-body p-2"
                                    style="max-height: 250px; overflow-y: auto; font-family: monospace; font-size: 12px; background: #f8f9fa;">
                                    <div id="progressLog"></div>
                                </div>
                            </div>

                            <p class="text-muted mt-3 mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                <small>Mohon tunggu, proses ini mungkin memakan waktu beberapa menit untuk data besar.
                                    Jangan tutup halaman ini.</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let previewData = null;

    function previewDownload() {
        const status = document.getElementById('statusFilter').value;
        const prodiId = document.getElementById('prodiFilter').value;
        const semesterId = document.getElementById('semesterFilter').value;

        // Show loading
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('downloadBtn').disabled = true;

        fetch('/admin/documents/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: status,
                prodi_id: prodiId,
                semester_id: semesterId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    previewData = data;
                    displayPreview(data);
                    document.getElementById('downloadBtn').disabled = false;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat preview');
            });
    }

    function displayPreview(data) {
        document.getElementById('previewSection').style.display = 'block';
        document.getElementById('totalParticipants').textContent = data.total;
        document.getElementById('estimatedSize').textContent = data.estimated_size;

        const statusFilter = document.getElementById('statusFilter');
        document.getElementById('previewStatus').textContent = statusFilter.options[statusFilter.selectedIndex].text;

        const thead = document.querySelector('#previewTable thead tr');
        thead.innerHTML = `
            <th>No</th>
            <th>No Peserta</th>
            <th>Nama</th>
            <th>Prodi</th>
            <th>Status</th>
            <th class="text-center">Form</th>
            <th class="text-center">KTP</th>
            <th class="text-center">Foto</th>
            <th class="text-center">Ijz S1</th>
            <th class="text-center">Trn S1</th>
            <th class="text-center">Kartu</th>
            <th class="text-center bg-light">Ijz S2</th>
            <th class="text-center bg-light">Trn S2</th>
            <th class="text-center">Aksi</th>
        `;

        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        data.participants.forEach((p, index) => {
            // S3 Logic
            const isS3 = p.is_s3;

            // Link to external system (using email/1 per user request)
            const externalLink = `https://admisipasca.ulm.ac.id/administrator/formulir/view/${p.email}/1`;

            const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${p.nomor_peserta}</td>
                <td>${p.nama_lengkap}</td>
                <td>${p.nama_prodi}</td>
                <td><span class="badge badge-${getStatusBadge(p.status_berkas)}">${p.status_berkas}</span></td>
                <td class="text-center">${p.docs.formulir ? '✅' : '❌'}</td>
                <td class="text-center">${p.docs.ktp ? '✅' : '❌'}</td>
                <td class="text-center">${p.docs.foto ? '✅' : '❌'}</td>
                <td class="text-center">${p.docs.ijazah_s1 ? '✅' : '❌'}</td>
                <td class="text-center">${p.docs.transkrip_s1 ? '✅' : '❌'}</td>
                <td class="text-center">${p.docs.kartu ? '✅' : '❌'}</td>
                
                <!-- S3 Columns -->
                <td class="text-center bg-light">${isS3 ? (p.docs.ijazah_s2 ? '✅' : '❌') : '-'}</td>
                <td class="text-center bg-light">${isS3 ? (p.docs.transkrip_s2 ? '✅' : '❌') : '-'}</td>
                
                <!-- Action Column -->
                <td class="text-center">
                    <a href="${externalLink}" target="_blank" class="btn btn-xs btn-primary" title="Cek di Server Utama (Administrator)">
                        <i class="fas fa-search"></i> View
                    </a>
                </td>
            </tr>
        `;
            tbody.innerHTML += row;
        });
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'lulus': return 'success';
            case 'gagal': return 'danger';
            default: return 'warning';
        }
    }

    function downloadZip() {
        if (!previewData || previewData.total === 0) {
            alert('Tidak ada data untuk di-download');
            return;
        }

        if (!confirm(`Anda akan mendownload berkas ${previewData.total} peserta. Lanjutkan?`)) {
            return;
        }

        const status = document.getElementById('statusFilter').value;
        const prodiId = document.getElementById('prodiFilter').value;
        const semesterId = document.getElementById('semesterFilter').value;

        // Show progress
        document.getElementById('progressSection').style.display = 'block';
        document.getElementById('downloadBtn').disabled = true;

        // Simulate progress
        simulateProgress(previewData.total);

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/documents/generate-zip';
        form.innerHTML = `
        <input type="hidden" name="status" value="${status}">
        <input type="hidden" name="prodi_id" value="${prodiId}">
        <input type="hidden" name="semester_id" value="${semesterId}">
    `;
        document.body.appendChild(form);
        form.submit();

        // Hide progress after download starts
        setTimeout(() => {
            addLog('✅ Download dimulai!', 'success');
            setTimeout(() => {
                document.getElementById('progressSection').style.display = 'none';
                document.getElementById('downloadBtn').disabled = false;
                document.getElementById('progressLog').innerHTML = '';
                document.getElementById('progressBar').style.width = '0%';
            }, 2000);
        }, 5000);
    }

    function simulateProgress(totalParticipants) {
        const progressBar = document.getElementById('progressBar');
        const currentStatus = document.getElementById('currentStatus');
        const progressLog = document.getElementById('progressLog');

        progressLog.innerHTML = '';
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';

        const steps = [
            { percent: 10, status: 'Memvalidasi data...', log: `📋 Memvalidasi ${totalParticipants} peserta`, delay: 200 },
            { percent: 20, status: 'Membuat Excel rekapitulasi...', log: '📊 Generating Excel summary...', delay: 800 },
            { percent: 30, status: 'Mengumpulkan file dokumen...', log: '📁 Collecting documents...', delay: 600 },
            { percent: 45, status: 'Membuat formulir PDF...', log: `📄 Generating ${totalParticipants} formulir PDF...`, delay: 1000 },
            { percent: 60, status: 'Mengumpulkan foto peserta...', log: '📷 Processing photos...', delay: 500 },
            { percent: 75, status: 'Mengumpulkan dokumen KTP, Ijazah, Transkrip...', log: '🗂️ Processing KTP, Ijazah, Transkrip...', delay: 800 },
            { percent: 85, status: 'Membuat ZIP archive...', log: '🗜️ Creating ZIP file...', delay: 700 },
            { percent: 95, status: 'Finalisasi...', log: '⚙️ Finalizing...', delay: 400 },
            { percent: 100, status: 'Siap download!', log: '✨ ZIP file ready!', delay: 300 }
        ];

        let currentStep = 0;

        function runStep() {
            if (currentStep < steps.length) {
                const step = steps[currentStep];
                progressBar.style.width = step.percent + '%';
                progressBar.textContent = step.percent + '%';
                currentStatus.textContent = step.status;
                addLog(step.log);

                currentStep++;
                setTimeout(runStep, step.delay);
            }
        }

        runStep();
    }

    function addLog(message, type = 'info') {
        const progressLog = document.getElementById('progressLog');
        const timestamp = new Date().toLocaleTimeString('id-ID');
        const color = type === 'success' ? 'text-success' : 'text-info';

        const logEntry = document.createElement('div');
        logEntry.className = `${color} mb-1`;
        logEntry.textContent = `[${timestamp}] ${message}`;

        progressLog.appendChild(logEntry);
        progressLog.scrollTop = progressLog.scrollHeight;
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>