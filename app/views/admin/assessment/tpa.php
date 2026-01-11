<?php
$title = 'Input Nilai TPA';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Input Nilai TPA</h3>
                <p class="text-subtitle text-muted">Input skor Tes Potensi Akademik (TPA)</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nilai TPA</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <!-- TPA Threshold Settings Card -->
    <?php if (!$isAdminProdi): ?>
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div
                class="card-header bg-gradient-to-r from-cyan-500 to-blue-500 p-4 d-flex justify-content-between align-items-center text-white">
                <h5 class="card-title mb-0 text-white"><i class="fas fa-sliders-h me-2"></i> Konfigurasi Threshold TPA</h5>
                <button type="button" class="btn btn-sm btn-light text-primary rounded-pill px-3 shadow-none"
                    data-card-widget="collapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="card-body bg-light-50 p-4" style="display: none;">
                <?php
                $thresholdS2 = \App\Models\Setting::get('tpa_threshold_s2', '450');
                $thresholdS3 = \App\Models\Setting::get('tpa_threshold_s3', '500');
                ?>
                <form action="/admin/assessment/threshold/save-tpa" method="POST">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold small text-uppercase">Threshold Magister
                                (S2)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-bar-chart text-primary"></i></span>
                                <input type="number" name="threshold_s2" class="form-control border-start-0 ps-0"
                                    value="<?php echo $thresholdS2; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold small text-uppercase">Threshold Doktor
                                (S3)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-graph-up text-danger"></i></span>
                                <input type="number" name="threshold_s3" class="form-control border-start-0 ps-0"
                                    value="<?php echo $thresholdS3; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill"><i
                                    class="fas fa-save me-2"></i> Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-bottom border-light">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="card-title fw-bold text-gray-800 mb-1">Daftar Hasil Tes Potensi Akademik</h4>
                    <p class="text-muted small mb-0">Kelola dan pantau skor TPA peserta ujian.</p>
                </div>
                <?php if (!$isAdminProdi): ?>
                    <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" data-toggle="modal"
                        data-target="#importTPAModal">
                        <i class="fas fa-file-excel me-2"></i> Import TPA (Excel)
                    </button>
                <?php endif; ?>
            </div>

            <!-- Modern Filters -->
            <div class="bg-gray-50 p-4 rounded-3 border border-gray-100">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-xs fw-bold text-uppercase text-gray-500 mb-1">Semester</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="bi bi-calendar-event text-primary"></i></span>
                            <select class="form-select border-start-0 bg-white ps-0" id="semesterSelect">
                                <option value="<?= $currentSemester ?>"><?= $semesterName ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-xs fw-bold text-uppercase text-gray-500 mb-1">Program
                            Studi</label>
                        <?php if ($isAdminProdi): ?>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-gray-100 border-end-0"><i
                                        class="bi bi-lock-fill text-muted"></i></span>
                                <input type="text"
                                    class="form-control form-control-sm bg-gray-100 border-start-0 ps-0 text-muted"
                                    value="Prodi Anda (Terkunci)" readonly disabled>
                            </div>
                            <input type="hidden" id="prodiFilter" value="all">
                        <?php else: ?>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-book text-info"></i></span>
                                <select class="form-select border-start-0 bg-white ps-0" id="prodiFilter">
                                    <option value="all">Semua Program Studi</option>
                                    <?php foreach ($prodiList as $prodi): ?>
                                        <option value="<?= htmlspecialchars($prodi['nama_prodi']) ?>">
                                            <?= htmlspecialchars($prodi['nama_prodi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label
                            class="form-label text-xs fw-bold text-uppercase text-gray-500 mb-1">Penyelenggara</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="bi bi-building text-warning"></i></span>
                            <select class="form-select border-start-0 bg-white ps-0" id="providerFilter">
                                <option value="all">Semua Penyelenggara</option>
                                <option value="PPKPP ULM">PPKPP ULM</option>
                                <option value="Bappenas">Bappenas</option>
                                <option value="PLTI">PLTI</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-xs fw-bold text-uppercase text-gray-500 mb-1">Status Nilai</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="bi bi-filter-circle text-success"></i></span>
                            <select class="form-select border-start-0 bg-white ps-0" id="tpaFilter">
                                <option value="all">Semua Status</option>
                                <option value="empty">Belum Ada Nilai (Kosong)</option>
                                <option value="below_min">Di Bawah Threshold (TL)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-4 rounded-3 shadow-sm border-0 bg-success-subtle text-success-emphasis"
                    role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Data berhasil disimpan.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table id="tpaTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-nowrap">
                                No Peserta</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-nowrap">
                                Nama Lengkap</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-nowrap">
                                Program Studi</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-nowrap">
                                Peny.</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-center">
                                Nilai</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-center">
                                Rekomendasi</th>
                            <th
                                class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-center">
                                Berkas</th>
                            <?php if (!$isAdminProdi): ?>
                                <th class="px-4 py-3 text-secondary text-xs uppercase font-bold tracking-wider border-bottom text-end"
                                    width="10%">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Score -->
<div class="modal fade" id="scoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="scoreForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Input Nilai TPA</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="participant_id" name="participant_id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Nama Peserta</label>
                            <input type="text" class="form-control" id="modal_nama" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Prodi</label>
                            <input type="text" class="form-control" id="modal_prodi" readonly>
                        </div>
                    </div>

                    <!-- TPA Provider Toggle -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Penyelenggara TPA</label>
                        <select class="form-select" name="tpa_provider" id="tpa_provider" onchange="toggleTPAInputs()">
                            <option value="PPKPP ULM">PPKPP ULM (Internal - Skor Komponen)</option>
                            <option value="Bappenas">Bappenas (External)</option>
                            <option value="PLTI">PLTI (External)</option>
                            <option value="Lainnya">Lainnya (External)</option>
                        </select>
                    </div>

                    <!-- Internal TPA Inputs -->
                    <div id="internal-tpa-inputs">
                        <h6 class="text-primary"><i class="bi bi-calculator"></i> Komponen Nilai (PPKPP ULM)</h6>
                        <div class="row" id="tpa-components-container">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <!-- External TPA Inputs -->
                    <div id="external-tpa-inputs" style="display:none;">
                        <h6 class="text-success"><i class="bi bi-award"></i> Nilai Akhir & Sertifikat (External)</h6>
                        <div class="mb-3">
                            <label>Nilai Total TPA</label>
                            <input type="number" class="form-control" name="manual_tpa_score" id="manual_tpa_score"
                                placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label>Upload Sertifikat TPA (PDF/JPG, Max 2MB)</label>
                            <input type="file" class="form-control" name="tpa_certificate">
                            <div id="current_certificate_link" class="mt-2"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import TPA -->
<div class="modal fade" id="importTPAModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/assessment/scores/import-tpa" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Nilai TPA (Excel)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i> Pastikan format kolom Excel sesuai:
                        <br><b>Nomor Peserta, Nama, Prodi, [Nama Komponen TPA...]</b>.
                        <br>
                        <a href="/admin/assessment/tpa/template" class="btn btn-sm btn-outline-dark mt-2">
                            <i class="fas fa-download"></i> Download Template Excel
                        </a>
                    </div>
                    <div class="form-group">
                        <label for="filetpa">Pilih File Excel (.xlsx)</label>
                        <input type="file" class="form-control" name="file" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var scoreModal;
    var importTPAModal;

    document.addEventListener('DOMContentLoaded', function () {
        // Bootstrap 4 Modals do not need manual initialization with 'new'
    });

    // Data TPA Components from PHP
    var tpaComponents = <?php echo json_encode($tpaComponents); ?>;

    $(document).ready(function () {
        var table = $('#tpaTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: APP_URL + "/api/assessment/scores",
                type: "GET",
                data: function (d) {
                    d.semester_id = $('#semesterSelect').val();
                    d.prodi = $('#prodiFilter').val();
                    d.tpa_filter = $('#tpaFilter').val(); // Send Filter Param
                    d.provider_filter = $('#providerFilter').val(); // New Param
                },
                error: function (xhr, error, thrown) {
                    console.log("Ajax Error:", xhr.responseText);
                }
            },
            columns: [
                { data: 'nomor_peserta', className: "align-middle font-monospace" },
                { data: 'nama_lengkap', className: "align-middle fw-bold" },
                { data: 'nama_prodi', className: "align-middle text-secondary small" },
                {
                    data: 'tpa_provider',
                    className: "align-middle",
                    render: function (data, type, row) {
                        if (!data || data === 'PPKPP ULM') return '<span class="badge bg-light text-dark border border-gray-300 rounded-pill px-3">PPKPP ULM</span>';
                        return '<span class="badge bg-purple-100 text-purple-700 border border-purple-200 rounded-pill px-3">' + data + '</span>';
                    }
                },
                {
                    data: 'nilai_tpa_total',
                    className: "align-middle text-center",
                    render: function (data, type, row) {
                        return data ? '<span class="fs-6 fw-bold text-dark">' + data + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'rekomendasi_tpa',
                    className: "align-middle text-center",
                    render: function (data, type, row) {
                        if (data === 'L') return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3"><i class="bi bi-check-circle me-1"></i> Lulus</span>';
                        if (data === 'TL') return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3"><i class="bi bi-x-circle me-1"></i> Tidak Lulus</span>';
                        return '<span class="text-muted small">-</span>';
                    }
                },
                {
                    data: 'tpa_certificate_url',
                    className: "align-middle text-center",
                    render: function (data, type, row) {
                        // Use ID for secure route
                        if (data) return '<a href="/admin/assessment/tpa/certificate/' + row.id + '" target="_blank" class="btn btn-sm btn-outline-info rounded-circle p-2" title="Lihat Sertifikat"><i class="fas fa-file-pdf"></i></a>';
                        return '<span class="text-muted opacity-50"><i class="fas fa-minus"></i></span>';
                    }
                }
                <?php if (!$isAdminProdi): ?>
                    , {
                        data: 'id',
                        orderable: false,
                        className: "align-middle text-end",
                        render: function (data, type, row) {
                            // Escape quotes for JS function call
                            var safeName = (row.nama_lengkap || '').replace(/'/g, "\\'");
                            var safeProdi = (row.nama_prodi || '').replace(/'/g, "\\'");
                            return `
                                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm hover-shadow" onclick="openScoreModal(${data}, '${safeName}', '${safeProdi}')">
                                    <i class="bi bi-pencil-square me-1"></i> Input Nilai
                                </button>
                            `;
                        }
                    }
                <?php endif; ?>
            ]
        });

        $('#semesterSelect, #prodiFilter, #tpaFilter, #providerFilter').change(function () {
            table.ajax.reload();
        });

        // Populate TPA Components in Modal (Internal)
        var compContainer = document.getElementById('tpa-components-container');
        if (tpaComponents.length === 0) {
            compContainer.innerHTML = '<p class="text-muted">Belum ada komponen TPA diset.</p>';
        } else {
            // Clear first just in case
            compContainer.innerHTML = '';
            tpaComponents.forEach(function (c) {
                var div = document.createElement('div');
                div.className = 'col-md-4 mb-2';
                div.innerHTML = `
                    <label class="form-label small">${c.nama_komponen}</label>
                    <input type="number" step="0.01" class="form-control tpa-comp-input" name="comp_${c.id}" placeholder="0-100">
                `;
                compContainer.appendChild(div);
            });
        }
    });

    function toggleTPAInputs() {
        var provider = document.getElementById('tpa_provider').value;
        if (provider === 'PPKPP ULM') {
            document.getElementById('internal-tpa-inputs').style.display = 'block';
            document.getElementById('external-tpa-inputs').style.display = 'none';
        } else {
            document.getElementById('internal-tpa-inputs').style.display = 'none';
            document.getElementById('external-tpa-inputs').style.display = 'block';
        }
    }

    function openScoreModal(id, name, prodiName) {
        // Show Modal (BS4)
        $('#scoreModal').modal('show');

        // Set Header
        document.getElementById('modal_nama').value = name;
        document.getElementById('modal_prodi').value = prodiName;
        document.getElementById('participant_id').value = id;

        // Set Action URL with 'from=tpa' check
        document.getElementById('scoreForm').action = APP_URL + '/admin/assessment/scores/save/' + id + '?from=tpa';

        // Reset Inputs
        document.querySelectorAll('.tpa-comp-input').forEach(i => i.value = '');
        document.getElementById('tpa_provider').value = 'PPKPP ULM';
        document.getElementById('manual_tpa_score').value = '';
        var linkDir = document.getElementById('current_certificate_link');
        linkDir.innerHTML = ''; // Clear previous link
        toggleTPAInputs();

        // Fetch Existing Scores via AJAX
        fetch(APP_URL + '/admin/assessment/scores/get/' + id)
            .then(response => response.json())
            .then(data => {
                var scores = data.scores || []; // Handle defined structure

                // Set Provider & TPA Details
                var provider = data.tpa_provider || 'PPKPP ULM';
                document.getElementById('tpa_provider').value = provider;

                if (provider !== 'PPKPP ULM') {
                    document.getElementById('manual_tpa_score').value = data.nilai_tpa_total;
                    if (data.tpa_certificate_url) {
                        // Use secure route
                        linkDir.innerHTML = `<a href="/admin/assessment/tpa/certificate/${id}" target="_blank" class="btn btn-sm btn-info mt-1"><i class="fas fa-file"></i> Lihat Sertifikat</a>`;
                    }
                }

                toggleTPAInputs();

                // Populate Component Scores
                scores.forEach(item => {
                    var inputTPA = document.querySelector('input[name="comp_' + item.component_id + '"]');
                    if (inputTPA) {
                        inputTPA.value = item.score;
                    }
                });
            })
            .catch(err => console.error(err));
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>