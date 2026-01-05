<?php
$title = 'Input Nilai Assessment';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Input Nilai Assessment</h3>
                <p class="text-subtitle text-muted">Input nilai TPA dan Tes Bidang</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nilai</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <!-- Schedule Configuration for Admin -->
    <div class="card card-outline card-warning shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title text-warning mb-0"><i class="fas fa-calendar-alt me-2"></i> Jadwal Penilaian Tes
                Bidang</h5>
            <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="collapse"
                data-target="#scheduleForm">
                <i class="fas fa-cog"></i> Atur Jadwal
            </button>
        </div>
        <div class="card-body collapse" id="scheduleForm">
            <?php
            // Global schedule from settings
            $globalStart = \App\Models\Setting::get('bidang_schedule_start_date', '');
            $globalEnd = \App\Models\Setting::get('bidang_schedule_end_date', '');
            $globalTimeStart = \App\Models\Setting::get('bidang_schedule_start_time', '00:00');
            $globalTimeEnd = \App\Models\Setting::get('bidang_schedule_end_time', '23:59');
            ?>
            <form action="/admin/assessment/schedule/save" method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="schedule_prodi"><i class="fas fa-building"></i> Terapkan Untuk</label>
                        <select name="schedule_prodi" id="schedule_prodi" class="form-control">
                            <option value="global">🌐 Semua Prodi (Global)</option>
                            <?php foreach ($prodiList as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['kode_prodi'] ?? $p['nama_prodi']); ?>">
                                    📁 <?php echo htmlspecialchars($p['nama_prodi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih "Semua Prodi" untuk jadwal global, atau pilih prodi
                            tertentu.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="start_date"><i class="fas fa-calendar"></i> Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control"
                            value="<?php echo $globalStart; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date"><i class="fas fa-calendar-check"></i> Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control"
                            value="<?php echo $globalEnd; ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="start_time"><i class="fas fa-clock"></i> Jam Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control"
                            value="<?php echo $globalTimeStart; ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="end_time"><i class="fas fa-clock"></i> Jam Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control"
                            value="<?php echo $globalTimeEnd; ?>">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </div>
                <div class="alert alert-info py-2 mb-0">
                    <small>
                        <i class="fas fa-info-circle"></i> <strong>Prioritas:</strong> Jadwal prodi spesifik > Jadwal
                        global.<br>
                        <i class="fas fa-lightbulb"></i> Kosongkan tanggal untuk menonaktifkan pembatasan.
                    </small>
                </div>
            </form>
        </div>
        <?php if ($globalStart && $globalEnd): ?>
            <div class="card-footer bg-light">
                <small class="text-dark">
                    <i class="fas fa-globe"></i> <strong>Jadwal Global:</strong>
                    <?php echo date('d M Y', strtotime($globalStart)); ?> (<?php echo $globalTimeStart; ?>) -
                    <?php echo date('d M Y', strtotime($globalEnd)); ?> (<?php echo $globalTimeEnd; ?>)
                </small>
            </div>
        <?php endif; ?>
    </div>

    <!-- TPA Threshold Settings Card -->
    <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title text-info mb-0"><i class="fas fa-chart-line me-2"></i> Threshold TPA per Jenjang</h5>
            <button type="button" class="btn btn-sm btn-outline-info" data-toggle="collapse"
                data-target="#thresholdForm">
                <i class="fas fa-cog"></i> Atur Threshold
            </button>
        </div>
        <div class="card-body collapse" id="thresholdForm">
            <?php
            $thresholdS2 = \App\Models\Setting::get('tpa_threshold_s2', '450');
            $thresholdS3 = \App\Models\Setting::get('tpa_threshold_s3', '500');
            ?>
            <form action="/admin/assessment/threshold/save-tpa" method="POST" class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="threshold_s2"><i class="fas fa-graduation-cap"></i> Threshold S2 (Magister)</label>
                    <div class="input-group">
                        <span class="input-group-text">≥</span>
                        <input type="number" name="threshold_s2" id="threshold_s2" class="form-control"
                            value="<?php echo $thresholdS2; ?>" min="0" step="1" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="threshold_s3"><i class="fas fa-user-graduate"></i> Threshold S3 (Doktor)</label>
                    <div class="input-group">
                        <span class="input-group-text">≥</span>
                        <input type="number" name="threshold_s3" id="threshold_s3" class="form-control"
                            value="<?php echo $thresholdS3; ?>" min="0" step="1" required>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-info w-100"><i class="fas fa-save"></i> Simpan
                        Threshold</button>
                </div>
            </form>
            <div class="alert alert-info py-2 mb-0">
                <small>
                    <i class="fas fa-info-circle"></i> Nilai TPA peserta akan dibandingkan dengan threshold jenjangnya
                    untuk menentukan <strong>Rekomendasi TPA</strong>.
                </small>
            </div>
        </div>
        <div class="card-footer bg-light">
            <small class="text-dark">
                <i class="fas fa-info"></i> <strong>Threshold Aktif:</strong> S2: ≥<?php echo $thresholdS2; ?> | S3:
                ≥<?php echo $thresholdS3; ?>
            </small>
        </div>
    </div>
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
            <h4 class="card-title">Filter Peserta</h4>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row align-items-end">
                <!-- Hidden semester_id - always use active semester -->
                <input type="hidden" name="semester_id" id="semester_id" value="<?php echo $currentSemester; ?>">

                <!-- Prodi Filter -->
                <div class="col-md-8 mb-3">
                    <label for="prodi">Program Studi</label>
                    <select name="prodi" id="prodi" class="custom-select shadow-sm" onchange="this.form.submit()">
                        <option value="all">-- Semua Prodi --</option>
                        <?php foreach ($prodiList as $p): ?>
                            <option value="<?php echo htmlspecialchars($p['nama_prodi']); ?>" <?php echo isset($_GET['prodi']) && $_GET['prodi'] === $p['nama_prodi'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_prodi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Tampilkan</button>
                </div>
            </form>

            <?php if ($prodiFilter !== 'all'):
                // Get daya tampung for selected prodi
                $db = \App\Utils\Database::connection();
                $quotaRes = $db->query("SELECT daya_tampung FROM prodi_quotas WHERE (kode_prodi = ? OR kode_prodi = ?) AND semester_id = ?")
                    ->bind($prodiFilter, $prodiFilter, $currentSemester)->fetchAssoc();
                $dayaTampung = $quotaRes['daya_tampung'] ?? 0;

                // Count current participants for this prodi
                $countRes = $db->query("SELECT COUNT(*) as total FROM participants WHERE nama_prodi = ? AND semester_id = ? AND status_berkas = 'lulus' AND status_pembayaran = 1")
                    ->bind($prodiFilter, $currentSemester)->fetchAssoc();
                $totalPeserta = $countRes['total'] ?? 0;
                ?>
                <div class="alert alert-info d-flex align-items-center mt-3 mb-0">
                    <i class="fas fa-info-circle me-2 fa-lg"></i>
                    <div>
                        <strong>Daya Tampung <?php echo htmlspecialchars($prodiFilter); ?>:</strong>
                        <span class="badge bg-primary ms-2"><?php echo $dayaTampung; ?> kursi</span>
                        <span class="badge bg-secondary ms-2"><?php echo $totalPeserta; ?> pendaftar</span>
                        <?php if ($dayaTampung > 0): ?>
                            <span class="badge <?php echo $totalPeserta > $dayaTampung ? 'bg-danger' : 'bg-success'; ?> ms-2">
                                <?php echo $totalPeserta > $dayaTampung ? 'Melebihi kuota' : 'Tersedia'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light d-flex justify-content-end gap-2">
            <a href="/admin/assessment/scores/export-final?semester_id=<?php echo $currentSemester; ?>&prodi=all"
                class="btn btn-warning shadow-sm">
                <i class="bi bi-file-earmark-arrow-down"></i> Export Hasil Akhir
            </a>
            <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#importFinalModal">
                <i class="bi bi-upload"></i> Import Hasil Akhir
            </button>
        </div>
    </div>

    <?php if ($prodiFilter === 'all'): ?>
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Pilih Program Studi</h5>
                <p class="text-muted mb-0">Silakan pilih program studi di filter di atas untuk menampilkan data nilai
                    peserta.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card card-outline card-primary shadow-sm">
            <div
                class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0 pt-4 px-4">
                <h4 class="card-title fw-bold text-primary"><i class="bi bi-table me-2"></i> Daftar Nilai Peserta -
                    <?php echo htmlspecialchars($prodiFilter); ?>
                </h4>
                <div>
                    <a href="/admin/assessment/scores/export-final?semester_id=<?php echo $currentSemester; ?>&prodi=<?php echo $prodiFilter; ?>"
                        class="btn btn-sm btn-warning shadow-sm me-2">
                        <i class="bi bi-file-earmark-arrow-down"></i> Export Hasil Akhir
                    </a>
                    <a href="/admin/assessment/scores/export?semester_id=<?php echo $currentSemester; ?>&prodi=<?php echo $prodiFilter; ?>"
                        class="btn btn-sm btn-success shadow-sm me-2">
                        <i class="bi bi-file-earmark-excel"></i> Template Bidang
                    </a>
                    <button type="button" class="btn btn-sm btn-info shadow-sm me-2" data-toggle="modal"
                        data-target="#importTPAModal">
                        <i class="bi bi-upload"></i> Import TPA (CAT)
                    </button>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                        data-target="#importModal">
                        <i class="bi bi-upload"></i> Import Bidang
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Data berhasil disimpan.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="/admin/assessment/scores/save-final" method="POST">
                    <!-- Preserve Filters -->
                    <input type="hidden" name="semester_id" value="<?php echo $currentSemester; ?>">
                    <input type="hidden" name="prodi_filter" value="<?php echo $prodiFilter; ?>">

                    <div class="d-flex justify-content-end mb-3">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="bi bi-save me-2"></i> Simpan Keputusan Akhir
                        </button>
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" class="custom-control-input" id="checkAll" onchange="toggleAll(this)">
                            <label class="custom-control-label fw-bold" for="checkAll">Pilih Semua</label>
                        </div>
                        <span class="mr-2 text-muted">Aksi Massal:</span>
                        <button type="button" onclick="bulkSet('L')" class="btn btn-sm btn-success mr-2 shadow-sm"><i
                                class="bi bi-check-circle"></i> Set Lulus</button>
                        <button type="button" onclick="bulkSet('TL')"
                            class="btn btn-sm btn-danger mr-2 shadow-sm"><i class="bi bi-x-circle"></i> Set Tidak
                            Lulus</button>
                        <button type="button" onclick="bulkSet('T')" class="btn btn-sm btn-warning shadow-sm"><i
                                class="bi bi-clock"></i> Set Tunda</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="scoresTable">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="px-3 py-3" style="width: 40px;">#</th>
                                    <th class="px-3 py-3">Nomor Peserta</th>
                                    <th class="px-3 py-3">Nama</th>
                                    <th class="px-3 py-3">Prodi</th>
                                    <th class="px-3 py-3 text-center">Total TPA</th>
                                    <th class="px-3 py-3 text-center">Total Bidang</th>
                                    <th class="px-3 py-3 text-center">Rekomendasi</th>
                                    <th class="px-3 py-3 text-center">Keputusan Akhir (L/TL/T)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- AJAX Populated -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Score Modal -->
    <div class="modal fade" id="scoreModal" tabindex="-1" role="dialog" aria-labelledby="scoreModalTitle"
        aria-hidden="true">
        <!-- ... (Modal content remains same) ... -->
    </div>

    <!-- ... (Other modals remain same) ... -->

    <script>
        $(document).ready(function () {
            const table = $('#scoresTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": APP_URL + "/api/assessment/scores",
                    "data": function (d) {
                        d.semester_id = $('#semester_id').val();
                        d.prodi = $('#prodi').val();
                    }
                },
                "columns": [
                    {
                        "data": "id",
                        "render": function (data) {
                            return `
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input row-checkbox" id="chk_${data}" value="${data}">
                                <label class="custom-control-label" for="chk_${data}"></label>
                            </div>
                            <input type="hidden" name="all_ids[]" value="${data}">
                        `;
                        }
                    },
                    { "data": "nomor_peserta" },
                    {
                        "data": "nama_lengkap",
                        "render": function (data, type, row) {
                            return `<div class="fw-bold text-dark" style="cursor:pointer" onclick="openScoreModal('${row.id}', '${data.replace(/'/g, "\\'")}', '${row.nama_prodi.replace(/'/g, "\\'")}')">${data}</div>`;
                        }
                    },
                    {
                        "data": "nama_prodi",
                        "render": function (data) {
                            return `<span class="badge bg-light text-dark border">${data}</span>`;
                        }
                    },
                    {
                        "data": "nilai_tpa_total",
                        "className": "text-center",
                        "render": function (data) { return data > 0 ? `<b>${data}</b>` : '-'; }
                    },
                    {
                        "data": "nilai_bidang_total",
                        "className": "text-center",
                        "render": function (data) { return data > 0 ? `<b>${data}</b>` : '-'; }
                    },
                    {
                        "data": "rekomendasi_tpa",
                        "className": "text-center",
                        "render": function (data, type, row) {
                            // TPA Recommendation
                            let tpaLabel = '-';
                            let tpaBadge = 'bg-secondary';
                            if (data === 'L') {
                                tpaLabel = `TPA: L (≥${row.tpa_threshold})`;
                                tpaBadge = 'bg-success';
                            } else if (data === 'TL') {
                                tpaLabel = `TPA: TL (<${row.tpa_threshold})`;
                                tpaBadge = 'bg-danger';
                            }

                            // Bidang Recommendation
                            let bidangLabel = '';
                            if (row.status_tes_bidang === 'lulus') {
                                bidangLabel = '<br><span class="badge bg-success">Bidang: L</span>';
                            } else if (row.status_tes_bidang === 'tidak_lulus') {
                                bidangLabel = '<br><span class="badge bg-danger">Bidang: TL</span>';
                            }

                            return `<span class="badge ${tpaBadge}">${tpaLabel}</span>${bidangLabel}`;
                        }
                    },
                    {
                        "data": "keputusan_akhir",
                        "className": "text-center",
                        "render": function (data, type, row) {
                            const status = data || '';
                            let selectClass = 'bg-secondary';
                            if (status === 'L') selectClass = 'bg-success';
                            else if (status === 'TL') selectClass = 'bg-danger';
                            else if (status === 'T') selectClass = 'bg-warning text-dark';
                            return `
                                    <select name="decision[${row.id}]" id="sel_${row.id}" onchange="updateDecisionColor(this)" class="custom-select custom-select-sm shadow-sm font-weight-bold text-white ${selectClass}" style="min-width:80px">
                                        <option value="" class="bg-secondary text-white" ${!status ? 'selected' : ''}>-</option>
                                        <option value="L" class="bg-success text-white" ${status === 'L' ? 'selected' : ''}>L (Lulus)</option>
                                        <option value="TL" class="bg-danger text-white" ${status === 'TL' ? 'selected' : ''}>TL (Tidak Lulus)</option>
                                        <option value="T" class="bg-warning text-dark" ${status === 'T' ? 'selected' : ''}>T (Tertunda)</option>
                                    </select>
                                `;
                        }
                    }
                ]
            });

            // Refilter on change
            $('#semester_id, #prodi').change(function () {
                table.draw();
            });
        });

        var bidangComponents = <?php echo json_encode($bidangComponents); ?>;
        // ... (rest of the JS functions like openScoreModal, bulkSet, etc remain same) ...
    </script>
    </div>
    </form>
    </div>
    </div>
<?php endif; ?>
</section>

<!-- Score Modal -->
<div class="modal fade" id="scoreModal" tabindex="-1" role="dialog" aria-labelledby="scoreModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="scoreForm" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scoreModalTitle">Input Nilai</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Peserta: <strong id="modalParticipantName"></strong><br>Prodi: <span
                            id="modalParticipantProdi"></span></p>

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="tpa-tab" data-toggle="tab" href="#tpa" role="tab"
                                aria-controls="tpa" aria-selected="true">Nilai TPA</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="bidang-tab" data-toggle="tab" href="#bidang" role="tab"
                                aria-controls="bidang" aria-selected="false">Nilai Bidang</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <!-- TPA Tab -->
                        <div class="tab-pane fade show active p-3" id="tpa" role="tabpanel" aria-labelledby="tpa-tab">
                            <?php if (\App\Utils\RoleHelper::isAdminProdi()): ?>
                                <div class="alert alert-warning">Admin Prodi tidak dapat mengubah nilai TPA.</div>
                            <?php else: ?>
                                <div id="tpaInputs">
                                    <?php if (empty($tpaComponents)): ?>
                                        <p class="text-muted">Belum ada komponen TPA. Silakan tambahkan di menu Komponen.</p>
                                    <?php else: ?>
                                        <?php foreach ($tpaComponents as $c): ?>
                                            <div class="mb-3 row">
                                                <label class="col-sm-4 col-form-label">
                                                    <?php echo htmlspecialchars($c['nama_komponen']); ?>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="comp_<?php echo $c['id']; ?>" placeholder="Nilai (0-100)">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bidang Tab -->
                        <div class="tab-pane fade p-3" id="bidang" role="tabpanel" aria-labelledby="bidang-tab">
                            <div class="mb-4 p-3 bg-light rounded border">
                                <label class="form-label fw-bold">Rekomendasi Akhir Tes Bidang</label>
                                <p class="text-small text-muted mb-2">Wajib diisi oleh Prodi. Jika "Tidak Lulus",
                                    peserta otomatis gugur.</p>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_tes_bidang"
                                        id="status_lulus" value="lulus">
                                    <label class="form-check-label text-success fw-bold"
                                        for="status_lulus">LULUS</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_tes_bidang"
                                        id="status_tidak" value="tidak_lulus">
                                    <label class="form-check-label text-danger fw-bold" for="status_tidak">TIDAK
                                        LULUS</label>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">Rincian Nilai (Opsional jika tidak ada komponen)</h6>
                            <div id="bidangInputs">
                                <!-- Populated by JS based on Prodi -->
                                <p class="text-muted" id="noBidangMsg">Tidak ada komponen Bidang untuk prodi ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data for JS -->
<!-- Import Modal (System/Bidang) -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/assessment/scores/import" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Nilai (Template System)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Gunakan file hasil <b>Download Template</b>.
                    </div>
                    <div class="form-group">
                        <label for="file">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" id="file" class="form-control" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload & Proses</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal (TPA External) -->
<div class="modal fade" id="importTPAModal" tabindex="-1" role="dialog" aria-labelledby="importTPAModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/assessment/scores/import-tpa" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importTPAModalLabel">Import Nilai TPA (Sumber External/CAT)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle"></i> Pastikan format kolom Excel sesuai:
                        <br><b>Nomor Peserta, Nama, Prodi, Skor Verbal, Skor Kualitatif, Skor Abstrak</b>.
                    </div>
                    <div class="form-group">
                        <label for="filetpa">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" id="filetpa" class="form-control" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload TPA</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal (Hasil Akhir) -->
<div class="modal fade" id="importFinalModal" tabindex="-1" role="dialog" aria-labelledby="importFinalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="/admin/assessment/scores/import-final" method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importFinalModalLabel">Import Keputusan Akhir dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Gunakan file hasil <b>Export Hasil Akhir</b>.<br>
                        Kolom yang diproses: <b>NO PESERTA</b> dan <b>KEPUTUSAN AKHIR</b> (L/TL/T).
                    </div>
                    <div class="form-group">
                        <label for="filefinal">Pilih File Excel (.xlsx)</label>
                        <input type="file" name="file" id="filefinal" class="form-control" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Import Keputusan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var bidangComponents = <?php echo json_encode($bidangComponents); ?>;

    function openScoreModal(id, name, prodiName) {
        // Show Modal (BS4)
        $('#scoreModal').modal('show');

        // Set Header
        document.getElementById('modalParticipantName').textContent = name;
        document.getElementById('modalParticipantProdi').textContent = prodiName;

        // Set Action URL
        document.getElementById('scoreForm').action = APP_URL + '/admin/assessment/scores/save/' + id;

        // Reset Inputs
        document.querySelectorAll('input[type="number"]').forEach(i => i.value = '');
        document.querySelectorAll('input[name="status_tes_bidang"]').forEach(i => i.checked = false);

        // Populate Bidang Inputs based on Prodi
        var container = document.getElementById('bidangInputs');
        container.innerHTML = '';
        var found = false;

        bidangComponents.forEach(function (c) {
            // Logic: Check if c.prodi_id matches prodiName (Name matching)
            // or if c.prodi_id is null (Global Bidang? Unlikely but possible)
            // Clean both strings for comparison
            var cProdi = (c.prodi_id || '').trim().toLowerCase();
            var pProdi = (prodiName || '').trim().toLowerCase();

            if (cProdi === pProdi) {
                found = true;
                var div = document.createElement('div');
                div.className = 'mb-3 row';
                div.innerHTML = `
                    <label class="col-sm-4 col-form-label">${c.nama_komponen}</label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" class="form-control" name="comp_${c.id}" placeholder="Nilai (0-100)">
                    </div>
                 `;
                container.appendChild(div);
            }
        });

        if (!found) {
            container.innerHTML = '<p class="text-muted">Tidak ada komponen rincian nilai. Silakan isi Status Rekomendasi di atas.</p>';
        }

        // Fetch Existing Scores via AJAX
        fetch(APP_URL + '/admin/assessment/scores/get/' + id)
            .then(response => response.json())
            .then(data => {
                var scores = data.scores || []; // Handle defined structure
                var status = data.status_tes_bidang;

                // Set Status
                if (status === 'lulus') document.getElementById('status_lulus').checked = true;
                if (status === 'tidak_lulus') document.getElementById('status_tidak').checked = true;

                scores.forEach(item => {
                    // Try to find input with name comp_{id} inside modal
                    // Check TPA inputs
                    var inputTPA = document.querySelector('#tpaInputs input[name="comp_' + item.component_id + '"]');
                    if (inputTPA) {
                        inputTPA.value = item.score;
                    }
                    // Check Bidang inputs (dynamically created)
                    var inputBidang = document.querySelector('#bidangInputs input[name="comp_' + item.component_id + '"]');
                    if (inputBidang) {
                        inputBidang.value = item.score;
                    }
                });
            })
            .catch(error => console.error('Error fetching scores:', error));
    }
    function toggleAll(source) {
        var checkboxes = document.querySelectorAll('.row-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    function bulkSet(status) {
        var checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Pilih minimal satu peserta.');
            return;
        }

        checkboxes.forEach(function (chk) {
            var id = chk.value;
            var select = document.getElementById('sel_' + id);
            if (select) {
                select.value = status;
                updateDecisionColor(select);
            }
        });
    }

    function updateDecisionColor(select) {
        // Remove old classes
        select.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-secondary', 'text-white', 'text-dark');

        if (select.value === 'L') {
            select.classList.add('bg-success', 'text-white');
        } else if (select.value === 'TL') {
            select.classList.add('bg-danger', 'text-white');
        } else if (select.value === 'T') {
            select.classList.add('bg-warning', 'text-dark');
        } else {
            select.classList.add('bg-secondary', 'text-white');
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>