<?php ob_start(); ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.7/css/responsive.bootstrap4.min.css">

<style>
    .doc-status-badge {
        font-size: 0.9em;
        margin-right: 5px;
        opacity: 0.4;
        filter: grayscale(100%);
        transition: all 0.3s;
    }

    .doc-status-badge.active {
        opacity: 1;
        filter: grayscale(0%);
        color: #28a745;
    }

    .doc-status-badge.missing {
        opacity: 1;
        color: #dc3545;
    }

    .preview-container {
        text-align: center;
        background: #343a40;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .preview-img {
        max-width: 100%;
        max-height: calc(90vh - 150px);
        transition: transform 0.3s ease;
        object-fit: contain;
    }

    .preview-iframe {
        width: 100%;
        height: calc(90vh - 150px);
        border: none;
        background: white;
    }

    /* Ensure tab-pane for PDF takes full width */
    .tab-pane {
        width: 100% !important;
        height: 100% !important;
        position: absolute;
        top: 0;
        left: 0;
    }

    #docContent {
        width: 100%;
        height: calc(85vh - 220px);
        position: relative;
    }

    #docContent .tab-pane.active {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    /* Ensure tabs are clickable and above content */
    #docTabs {
        position: relative;
        z-index: 10;
    }

    .nav-tabs-premium .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: #6c757d;
        cursor: pointer;
        padding: 0.5rem 1rem;
    }

    .nav-tabs-premium .nav-link.active {
        border-bottom-color: #007bff;
        color: #007bff;
        background: transparent;
    }

    .nav-tabs-premium .nav-link:hover {
        color: #0056b3;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Document Download Helper</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                    <li class="breadcrumb-item active">Document Helper</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-archive mr-2"></i> Helper Verifikasi & Download</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                            class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Semester</label>
                            <select class="form-control" id="filterSemester">
                                <option value="all">Semua Semester</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?= $sem['id'] ?>" <?= (isset($activeSemester['id']) && $activeSemester['id'] == $sem['id']) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($sem['kode'] ?? '') ?>] <?= htmlspecialchars($sem['nama']) ?>
                                        <?= $sem['is_active'] ? '(Aktif)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Program Studi</label>
                            <select class="form-control" id="filterProdi">
                                <option value="all">Semua Prodi</option>
                                <?php foreach ($prodis as $prodiName): ?>
                                    <option value="<?= htmlspecialchars($prodiName) ?>">
                                        <?= htmlspecialchars($prodiName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center pt-3">
                        <button class="btn btn-secondary mr-2" onclick="reloadTable()" title="Terapkan Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button class="btn btn-primary" onclick="massSyncAll()"
                            title="Mass Sync (semua peserta di filter ini)">
                            <i class="fas fa-cloud-download-alt"></i> Mass Sync
                        </button>
                    </div>
                </div>

                <table id="helperTable" class="table table-bordered table-striped dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th width="50">Foto</th>
                            <th>Email</th>
                            <th>Nama Peserta</th>
                            <th>Prodi</th>
                            <th>Semester</th>
                            <th>Kelengkapan Berkas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--  Loading via AJAX -->
                    </tbody>
                </table>

                <!-- Mass Sync Log Area -->
                <div class="mt-3 d-none" id="massLogArea">
                    <div class="bg-dark text-white small">
                        <div
                            class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom border-secondary">
                            <span><i class="fas fa-terminal mr-1"></i> Mass Sync Log</span>
                            <button type="button" class="btn btn-sm btn-outline-light py-0 px-1"
                                onclick="closeMassLog()" title="Tutup Log">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <!-- Progress Bar -->
                        <div class="px-2 py-1 d-none" id="massProgressContainer">
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                        id="massProgressBar" role="progressbar" style="width: 0%;">0%</div>
                                </div>
                                <span class="ml-2" id="massProgressText">0/0</span>
                            </div>
                        </div>
                        <div id="massLog" class="p-2"
                            style="max-height: 150px; overflow-y: auto; font-family: monospace;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- IMPORT MODAL -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import ZIP: <span id="importName"></span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Upload file ZIP berisi dokumen (Foto, KTP, Ijazah, Transkrip) untuk peserta
                    ini.</p>
                <div class="form-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="zipFile" accept=".zip">
                        <label class="custom-file-label" for="zipFile">Pilih file ZIP...</label>
                    </div>
                </div>
                <!-- Progress -->
                <div id="importProgress" class="d-none">
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width: 100%"></div>
                    </div>
                    <small class="text-muted">Sedang memproses...</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="processImport()">Upload & Proses</button>
            </div>
        </div>
    </div>
</div>

<!-- PREVIEW MODAL -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="margin-top: 2vh;">
        <div class="modal-content" style="height: 85vh;">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-search mr-2"></i> Preview Dokumen: <span
                        id="previewName"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 d-flex flex-column">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-tabs-premium bg-light px-3 pt-2" id="docTabs" role="tablist">
                    <!-- Dynamic Tabs -->
                </ul>

                <!-- Content -->
                <div class="tab-content flex-grow-1 bg-dark position-relative" id="docContent"
                    style="overflow: hidden;">
                    <!-- Dynamic Content -->
                </div>

                <!-- Inline Log Area -->
                <div class="bg-secondary text-white small" id="previewLogArea">
                    <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom border-dark">
                        <span><i class="fas fa-terminal mr-1"></i> Log</span>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-1" onclick="clearLog()"
                            title="Clear Log">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="previewLog" class="p-2"
                        style="max-height: 80px; overflow-y: auto; font-family: monospace;">&gt; Ready</div>
                </div>
            </div>
            <div class="modal-footer justify-content-between bg-white flex-wrap">
                <!-- Image Controls (Left) -->
                <div class="btn-group mr-2 mb-1" role="group" id="imageControls">
                    <button type="button" class="btn btn-sm btn-info" title="Putar Kiri"
                        onclick="rotateCurrentDoc(-90)">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-info" title="Putar Kanan"
                        onclick="rotateCurrentDoc(90)">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" title="Zoom In" onclick="zoomCurrentDoc(1)">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" title="Zoom Out" onclick="zoomCurrentDoc(-1)">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" title="Reset" onclick="resetTransform()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <!-- Action Buttons (Right) -->
                <div class="d-flex flex-wrap mb-1">
                    <a href="#" id="btnDownloadLegacy" target="_blank" class="btn btn-sm btn-info mr-1 mb-1"
                        title="Download ZIP dari server lama">
                        <i class="fas fa-download"></i> Unduh
                    </a>
                    <label class="btn btn-sm btn-warning mr-1 mb-1 mb-0" title="Import ZIP">
                        <i class="fas fa-file-archive"></i> Import
                        <input type="file" id="importFileInline" accept=".zip" style="display:none;"
                            onchange="importFromPreview()">
                    </label>
                    <button type="button" class="btn btn-sm btn-success mr-1 mb-1" title="Sync dari server lama"
                        onclick="syncFromPreview()">
                        <i class="fas fa-cloud-download-alt"></i> Sync
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary mb-1" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // --- Dynamic Filter Logic ---
        $('#filterSemester').on('change', function () {
            const semesterId = $(this).val();
            const prodiSelect = $('#filterProdi');

            // Show loading state
            prodiSelect.html('<option value="all">Memuat...</option>');
            prodiSelect.prop('disabled', true);

            fetch(`/api/document-helper/prodis?semester_id=${semesterId}`)
                .then(r => r.json())
                .then(data => {
                    prodiSelect.empty();
                    prodiSelect.append('<option value="all">Semua Prodi</option>');

                    if (Array.isArray(data)) {
                        data.forEach(prodi => {
                            prodiSelect.append(`<option value="${prodi}">${prodi}</option>`);
                        });
                    }
                    prodiSelect.prop('disabled', false);

                    // Auto-reload table when semester changes (optional, but good UX)
                    // $('#helperTable').DataTable().ajax.reload();
                })
                .catch(e => {
                    console.error('Failed to load prodis', e);
                    prodiSelect.html('<option value="all">Gagal memuat prodi</option>');
                    prodiSelect.prop('disabled', false);
                });
        });

        $('#helperTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "pageLength": 10,
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data yang ditampilkan",
                "infoFiltered": ("(disaring dari _MAX_ total data)"),
                "search": "Cari:",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "ajax": {
                "url": "/api/document-helper/participants",
                "data": function (d) {
                    d.semester_id = $('#filterSemester').val();
                    d.prodi = $('#filterProdi').val();
                }
            },
            "columns": [
                {
                    "data": "photo_url",
                    "render": function (data, type, row) {
                        return `<div class="text-center"><img src="${data}" class="img-circle img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></div>`;
                    }
                },
                { "data": "email" },
                {
                    "data": "nama_lengkap",
                    "render": function (data, type, row) {
                        return `<b>${data}</b><br><small class="text-muted">${row.nomor_peserta || 'No. Peserta Pending'}</small>`;
                    }
                },
                { "data": "nama_prodi" },
                {
                    "data": "nama_semester",
                    "defaultContent": "-"
                },
                {
                    "data": "docs",
                    "render": function (data, type, row) {
                        const badge = (isActive, title, icon) =>
                            `<i class="fas fa-${icon} doc-status-badge ${isActive ? 'active' : 'missing'}" title="${title}"></i>`;

                        let html = badge(data.photo, 'Foto', 'camera') +
                            badge(data.ktp, 'KTP', 'id-card') +
                            badge(data.ijazah, 'Ijazah S1', 'graduation-cap') +
                            badge(data.transkrip, 'Transkrip S1', 'file-alt');

                        if (data.is_s3) {
                            html += `<span class="border-left ml-2 pl-2">` +
                                badge(data.ijazah_s2, 'Ijazah S2', 'graduation-cap') +
                                badge(data.transkrip_s2, 'Transkrip S2', 'file-alt') +
                                `</span>`;
                        }
                        return `<div class="text-nowrap">${html}</div>`;
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return `
                            <button type="button" class="btn btn-sm btn-primary" onclick="openPreviewModal(${row.id})" title="Preview & Kelola Dokumen">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                       `;
                    }
                }
            ]
        });
        bsCustomFileInput.init();
    });

    let currentId = null;
    let currentRotation = 0;
    let currentZoom = 1;
    let currentPreviewId = null;

    // --- IMPORT LOGIC ---
    function openImportModal(id, name) {
        currentId = id;
        $('#importName').text(name);
        $('#zipFile').val('');
        $('#importProgress').addClass('d-none');
        $('#importModal').modal('show');
    }

    function processImport() {
        const file = $('#zipFile')[0].files[0];
        if (!file) {
            Swal.fire('Error', 'Pilih file ZIP dulu', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        $('#importProgress').removeClass('d-none');

        fetch(`/admin/document-helper/import-zip/${currentId}`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                $('#importProgress').addClass('d-none');
                if (data.success) {
                    $('#importModal').modal('hide');

                    let msg = `Berhasil memproses ${data.processed} dokumen!`;
                    if (data.log && data.log.length > 0) {
                        msg += '<br><br><div style="text-align:left; max-height:200px; overflow-y:auto; background:#f8f9fa; padding:10px; font-size:0.8em; border:1px solid #ddd;">' +
                            '<strong>Log Detail:</strong><br>' +
                            data.log.join('<br>') +
                            '</div>';
                    }

                    Swal.fire({
                        title: 'Sukses',
                        html: msg,
                        icon: 'success'
                    }).then(() => $('#helperTable').DataTable().ajax.reload()); // Auto reload DataTables
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(e => {
                $('#importProgress').addClass('d-none');
                Swal.fire('Error', 'Server Error', 'error');
            });
    }

    // --- SYNC LOGIC (Inline Logging) ---
    function syncParticipant(id) {
        clearLog();
        appendLog('Memulai sync...');

        fetch(`/api/document-helper/sync/${id}`, {
            method: 'POST'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.log && data.log.length > 0) {
                        data.log.forEach(msg => appendLog(msg));
                    }
                    appendLog(`✅ Selesai! ${data.processed} dokumen diproses.`);
                    refreshPreviewDocs(); // Reload preview
                    $('#helperTable').DataTable().ajax.reload(null, false);
                } else {
                    appendLog(`❌ Gagal: ${data.message}`);
                }
            })
            .catch(e => {
                appendLog('❌ Error: Server tidak merespon');
            });
    }

    // --- Log Helper Functions ---
    function appendLog(msg) {
        const logDiv = $('#previewLog');
        logDiv.append(`<div>&gt; ${msg}</div>`);
        // Scroll the log div itself (not the container)
        logDiv.scrollTop(logDiv[0].scrollHeight);
    }

    function clearLog() {
        $('#previewLog').html('&gt; Ready');
    }

    // --- Import from Preview (Inline) ---
    function importFromPreview() {
        const file = $('#importFileInline')[0].files[0];
        if (!file) return;

        clearLog();
        appendLog('Mengupload dan memproses ZIP...');

        const formData = new FormData();
        formData.append('file', file);

        fetch(`/admin/document-helper/import-zip/${currentPreviewId}`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.log && data.log.length > 0) {
                        data.log.forEach(msg => appendLog(msg));
                    }
                    appendLog(`✅ Selesai! ${data.processed} dokumen diproses.`);
                    refreshPreviewDocs();
                    $('#helperTable').DataTable().ajax.reload(null, false);
                } else {
                    appendLog(`❌ Gagal: ${data.message}`);
                }
                $('#importFileInline').val(''); // Reset file input
            })
            .catch(e => {
                appendLog('❌ Error: Server tidak merespon');
                $('#importFileInline').val('');
            });
    }

    // --- Refresh Preview Docs after Sync/Import ---
    function refreshPreviewDocs() {
        if (!currentPreviewId) return;
        fetch(`/admin/document-helper/get-docs/${currentPreviewId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    setupPreview(data);
                }
            });
    }

    // --- PREVIEW LOGIC ---
    function openPreviewModal(id) {
        // Show Loading
        Swal.fire({ title: 'Memuat data...', didOpen: () => Swal.showLoading() });

        fetch(`/admin/document-helper/get-docs/${id}`)
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (!data.success) {
                    Swal.fire('Error', data.message, 'error');
                    return;
                }

                setupPreview(data);
                $('#previewModal').modal('show');
            });
    }

    function setupPreview(data) {
        const p = data.participant;
        const docs = data.docs;

        currentPreviewId = p.id; // Store for sync button
        $('#previewName').text(p.nama);

        // Set Download link
        $('#btnDownloadLegacy').attr('href', `https://admisipasca.ulm.ac.id/administrator/formulir/download_zip/${p.email}/1`);

        // Don't auto-clear log - user can clear manually

        const tabsContainer = $('#docTabs');
        const contentContainer = $('#docContent');
        tabsContainer.empty();
        contentContainer.empty();

        // Define Docs ordered
        const definitions = [
            { key: 'photo', label: 'Foto', icon: 'camera' },
            { key: 'ktp', label: 'KTP', icon: 'id-card' },
            { key: 'ijazah', label: 'Ijazah S1', icon: 'graduation-cap' },
            { key: 'transkrip', label: 'Transkrip S1', icon: 'file-alt' }
        ];

        if (p.is_s3) {
            definitions.push({ key: 'ijazah_s2', label: 'Ijazah S2', icon: 'graduation-cap' });
            definitions.push({ key: 'transkrip_s2', label: 'Transkrip S2', icon: 'file-alt' });
        }

        let firstActive = true;

        definitions.forEach(def => {
            const url = docs[def.key];
            const isActive = firstActive ? 'active' : '';
            const isShow = firstActive ? 'show active' : '';
            if (firstActive) firstActive = false;

            // Tab
            const statusColor = url ? 'text-success' : 'text-danger';
            tabsContainer.append(`
            <li class="nav-item">
                <a class="nav-link ${isActive}" id="tab-${def.key}" data-toggle="tab" href="#content-${def.key}" role="tab" onclick="resetRotation()">
                    <i class="fas fa-${def.icon} ${statusColor} mr-1"></i> ${def.label}
                </a>
            </li>
        `);

            // Content
            let htmlContent = '';
            if (!url) {
                htmlContent = `
                <div class="text-white text-center">
                    <i class="fas fa-times-circle fa-4x mb-3 text-secondary"></i>
                    <h4>Dokumen Tidak Tersedia</h4>
                </div>`;
            } else {
                // Check Extension for Image vs PDF
                const ext = url.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    htmlContent = `<img src="${url}" class="preview-img" id="img-${def.key}" alt="${def.label}">`;
                } else {
                    htmlContent = `<iframe src="${url}" class="preview-iframe"></iframe>`;
                }
            }

            contentContainer.append(`
            <div class="tab-pane fade ${isShow}" id="content-${def.key}" role="tabpanel" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                ${htmlContent}
            </div>
        `);
        });
    }

    function resetRotation() {
        currentRotation = 0;
        currentZoom = 1;
        $('.preview-img').css('transform', 'rotate(0deg) scale(1)');
    }

    function resetTransform() {
        currentRotation = 0;
        currentZoom = 1;
        updateImageTransform();
    }

    function rotateCurrentDoc(degree) {
        const activeTabId = $('#docTabs .nav-link.active').attr('href');
        const img = $(activeTabId).find('img');

        if (img.length > 0) {
            currentRotation = (currentRotation + degree + 360) % 360;
            updateImageTransform();
        }
    }

    function zoomCurrentDoc(direction) {
        const activeTabId = $('#docTabs .nav-link.active').attr('href');
        const img = $(activeTabId).find('img');

        if (img.length > 0) {
            if (direction > 0) {
                currentZoom = Math.min(currentZoom + 0.25, 3); // Max 3x
            } else {
                currentZoom = Math.max(currentZoom - 0.25, 0.5); // Min 0.5x
            }
            updateImageTransform();
        }
    }

    // Pan (drag) variables
    let currentPanX = 0;
    let currentPanY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;

    function updateImageTransform() {
        const activeTabId = $('#docTabs .nav-link.active').attr('href');
        const img = $(activeTabId).find('img');
        if (img.length > 0) {
            img.css('transform', `translate(${currentPanX}px, ${currentPanY}px) rotate(${currentRotation}deg) scale(${currentZoom})`);
        }
    }

    function resetTransform() {
        currentRotation = 0;
        currentZoom = 1;
        currentPanX = 0;
        currentPanY = 0;
        updateImageTransform();
    }

    // Mouse scroll zoom
    $(document).on('wheel', '#docContent .preview-img', function (e) {
        e.preventDefault();
        if (e.originalEvent.deltaY < 0) {
            // Scroll up = zoom in
            currentZoom = Math.min(currentZoom + 0.1, 3);
        } else {
            // Scroll down = zoom out
            currentZoom = Math.max(currentZoom - 0.1, 0.5);
        }
        updateImageTransform();
    });

    // Mouse drag pan
    $(document).on('mousedown', '#docContent .preview-img', function (e) {
        if (currentZoom > 1) {
            isDragging = true;
            dragStartX = e.clientX - currentPanX;
            dragStartY = e.clientY - currentPanY;
            $(this).css('cursor', 'grabbing');
            e.preventDefault();
        }
    });

    $(document).on('mousemove', function (e) {
        if (isDragging) {
            currentPanX = e.clientX - dragStartX;
            currentPanY = e.clientY - dragStartY;
            updateImageTransform();
        }
    });

    $(document).on('mouseup', function () {
        if (isDragging) {
            isDragging = false;
            $('#docContent .preview-img').css('cursor', currentZoom > 1 ? 'grab' : 'default');
        }
    });

    // Set grab cursor when zoomed
    $(document).on('mouseenter', '#docContent .preview-img', function () {
        if (currentZoom > 1) {
            $(this).css('cursor', 'grab');
        }
    });

    function syncFromPreview() {
        if (!currentPreviewId) return;
        // Don't close modal, just run sync with inline logging
        syncParticipant(currentPreviewId);
    }

    // --- MASS SYNC ---
    let massSyncRunning = false;

    function appendMassLog(msg) {
        const logDiv = $('#massLog');
        logDiv.append(`<div>&gt; ${msg}</div>`);
        logDiv.scrollTop(logDiv[0].scrollHeight);
    }

    function closeMassLog() {
        $('#massLogArea').addClass('d-none');
        $('#massLog').html('');
    }

    async function massSyncAll() {
        if (massSyncRunning) {
            Swal.fire('Perhatian', 'Mass sync sedang berjalan, harap tunggu.', 'warning');
            return;
        }

        const semesterId = $('#filterSemester').val();
        const prodi = $('#filterProdi').val();

        // First, fetch count to show confirmation
        Swal.fire({ title: 'Mengambil data...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

        try {
            // Use length=-1 to get ALL participants (no pagination)
            const response = await fetch(`/api/document-helper/participants?semester_id=${semesterId}&prodi=${encodeURIComponent(prodi)}&length=-1`);
            const result = await response.json();
            Swal.close();

            if (!result.data || result.data.length === 0) {
                Swal.fire('Info', 'Tidak ada peserta ditemukan untuk filter ini.', 'info');
                return;
            }

            const participants = result.data;

            // Get display names from dropdowns
            const semesterText = $('#filterSemester option:selected').text().trim();
            const prodiText = prodi === 'all' ? 'Semua Prodi' : prodi;

            // Show confirmation
            const confirmResult = await Swal.fire({
                title: 'Konfirmasi Mass Sync',
                html: `<p>Akan memproses <strong>${participants.length}</strong> peserta.</p>
                       <p class="text-muted small">Semester: ${semesterText}<br>Prodi: ${prodiText}</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-play"></i> Proses',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745'
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            // Start processing
            massSyncRunning = true;

            // Show log area and progress bar
            $('#massLogArea').removeClass('d-none');
            $('#massProgressContainer').removeClass('d-none');
            $('#massLog').html('');

            appendMassLog(`Memulai mass sync untuk ${participants.length} peserta...`);
            updateMassProgress(0, participants.length);

            let successCount = 0;
            let failCount = 0;

            for (let i = 0; i < participants.length; i++) {
                const p = participants[i];
                appendMassLog(`[${i + 1}/${participants.length}] Syncing: ${p.nama_lengkap}...`);
                updateMassProgress(i + 1, participants.length);

                try {
                    const syncRes = await fetch(`/api/document-helper/sync/${p.id}`, { method: 'POST' });
                    const syncData = await syncRes.json();

                    if (syncData.success) {
                        appendMassLog(`   ✅ ${syncData.processed} dokumen diproses`);
                        successCount++;
                    } else {
                        appendMassLog(`   ❌ Gagal: ${syncData.message}`);
                        failCount++;
                    }
                } catch (e) {
                    appendMassLog(`   ❌ Error: ${e.message}`);
                    failCount++;
                }
            }

            appendMassLog('---');
            appendMassLog(`🏁 Selesai! ✅ Sukses: ${successCount}, ❌ Gagal: ${failCount}`);
            $('#helperTable').DataTable().ajax.reload(null, false);

        } catch (e) {
            Swal.close();
            Swal.fire('Error', e.message, 'error');
        }

        massSyncRunning = false;
    }

    function updateMassProgress(current, total) {
        const percent = Math.round((current / total) * 100);
        $('#massProgressBar').css('width', percent + '%').text(percent + '%');
        $('#massProgressText').text(`${current}/${total}`);
    }

    function reloadTable() {
        $('#helperTable').DataTable().ajax.reload();
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>