<?php ob_start(); ?>

<!-- Statistics Section (Premium Layout) -->
<div class="row mb-3">
    <!-- Total Participants -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary elevation-2">
            <div class="inner">
                <h3><?php echo $stats['total_all']; ?></h3>
                <p>Total Verifikasi Online</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <!-- Eligible (Punya Nomor) -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info elevation-2">
            <div class="inner">
                <h3><?php echo $stats['total_eligible']; ?></h3>
                <p>Eligible (Punya Nomor)</p>
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
            <a href="#" class="small-box-footer"
                onclick="$('#eligibilityFilter').val('eligible').trigger('change'); return false;">
                Filter Eligible <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <!-- Verified (Lengkap) -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success elevation-2">
            <div class="inner">
                <h3><?php echo $stats['lengkap']; ?></h3>
                <p>Sudah Verifikasi (Lengkap)</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer"
                onclick="$('#statusFilter').val('lengkap').trigger('change'); return false;">
                Filter Lengkap <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <!-- Pending / Incomplete -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning elevation-2">
            <div class="inner">
                <h3><?php echo $stats['total_eligible'] - $stats['lengkap']; ?></h3>
                <p>Belum Selesai (Pending/Gagal)</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <a href="#" class="small-box-footer"
                onclick="$('#statusFilter').val('pending').trigger('change'); return false;">
                Filter Pending <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">

        <!-- Filter Card -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                            class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">-- Pilih Semester --</option>
                                    <?php foreach ($semesters as $sem): ?>
                                        <option value="<?php echo $sem['id']; ?>" <?php echo $currentSemester == $sem['id'] ? 'selected' : ''; ?>>
                                            [<?php echo $sem['kode']; ?>] <?php echo $sem['nama']; ?>
                                            <?php echo $sem['is_active'] ? '(Aktif)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Program Studi</label>
                                <select name="prodi" id="prodiFilter" class="form-control select2">
                                    <option value="all">Semua Program Studi</option>
                                    <?php foreach ($prodis as $prodiName): ?>
                                        <option value="<?php echo htmlspecialchars($prodiName); ?>" <?php echo $prodiFilter == $prodiName ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prodiName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Eligibility</label>
                                <select name="eligibility" id="eligibilityFilter" class="form-control">
                                    <option value="all">Semua</option>
                                    <option value="eligible" selected>Punya Nomor Peserta (Eligible)</option>
                                    <option value="not_eligible">Tidak Punya Nomor Peserta</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status Verifikasi</label>
                                <select name="status" id="statusFilter" class="form-control select2">
                                    <option value="all" <?php echo $statusFilter == 'all' ? 'selected' : ''; ?>>Semua
                                        Status</option>
                                    <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>
                                        Pending/Belum</option>
                                    <option value="lengkap" <?php echo $statusFilter == 'lengkap' ? 'selected' : ''; ?>>
                                        Lengkap</option>
                                    <option value="tidak_lengkap" <?php echo $statusFilter == 'tidak_lengkap' ? 'selected' : ''; ?>>Tidak Lengkap</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <button type="button" id="btnFilter" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-search mr-1"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-default btn-sm float-right" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Reset
                </button>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-list-alt text-primary mr-2"></i> Daftar Peserta
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal"
                            data-target="#importModal">
                            <i class="fas fa-file-excel mr-1"></i> Import/Sync Excel
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if (isset($_GET['success']) && $_GET['success'] == 'import'): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <strong><i class="icon fas fa-check"></i> Berhasil!</strong> Data verifikasi berhasil diimport.
                        (<?php echo $_GET['count'] ?? 0; ?> baris)
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong><i class="icon fas fa-ban"></i> Error!</strong> <?php echo urldecode($_GET['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="verificationTable" class="table table-hover table-striped text-nowrap"
                        style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Nomor Peserta</th>
                                <th>Nama Peserta</th>
                                <th>Program Studi</th>
                                <th class="text-center">Status Berkas</th>
                                <th class="text-center">Verifikasi Fisik</th>
                                <th class="text-center">Catatan</th>
                                <th class="text-center">Update Terakhir</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Ajax Data -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importModalLabel"><i class="fas fa-file-excel mr-2"></i> Import Data
                    Verifikasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/admin/verification/physical/import" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-light border shadow-sm">
                        <i class="fas fa-info-circle text-info mr-1"></i> <strong>Panduan Import:</strong>
                        <ol class="pl-3 mb-0 mt-2">
                            <li>Download template Excel terbaru.</li>
                            <li>Isi kolom verifikasi dengan angka <strong>1</strong> (Lengkap) atau <strong>0</strong>
                                (Tidak).</li>
                            <li>Upload file Excel yang telah diperbarui.</li>
                        </ol>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 border-right">
                            <div class="form-group text-center">
                                <label class="d-block text-muted mb-3">Belum punya format?</label>
                                <a href="/admin/verification/physical/import/template"
                                    class="btn btn-outline-primary shadow-sm">
                                    <i class="fas fa-download mr-1"></i> Download Template
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block text-muted mb-2">Upload File Excel</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="verification_file"
                                        name="verification_file" required accept=".xlsx, .xls">
                                    <label class="custom-file-label" for="verification_file">Pilih file...</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success shadow-sm"><i class="fas fa-upload mr-1"></i> Proses
                        Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Custom File Input Label
    $(".custom-file-input").on("change", function () {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    $(document).ready(function () {
        // Determine Semester ID from URL or Dropdown
        var urlParams = new URLSearchParams(window.location.search);
        var semesterId = $('select[name="semester_id"]').val() || urlParams.get('semester_id') || '';

        // Set default eligibility filter
        $('#eligibilityFilter').val('eligible').trigger('change');

        // Initialize DataTable
        var table = $('#verificationTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            language: {
                "sEmptyTable": "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing": "Sedang memproses...",
                "sLengthMenu": "Tampilkan _MENU_ entri",
                "sZeroRecords": "Tidak ditemukan data yang sesuai",
                "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix": "",
                "sSearch": "Cari:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext": "Selanjutnya",
                    "sLast": "Terakhir"
                }
            },
            ajax: {
                url: "/admin/verification/physical/api-data",
                data: function (d) {
                    d.semester_id = $('select[name="semester_id"]').val();
                    d.status = $('#statusFilter').val();
                    d.prodi = $('#prodiFilter').val();
                    d.eligibility = document.getElementById('eligibilityFilter').value;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: "text-center align-middle",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'nomor_peserta',
                    className: "align-middle font-weight-bold",
                    render: function (data) {
                        return data ? `<span class="text-primary">${data}</span>` : '<span class="text-muted font-italic">Pending</span>';
                    }
                },
                {
                    data: 'nama_lengkap',
                    className: "align-middle",
                    render: function (data, type, row) {
                        return `<div class="d-flex flex-column">
                                    <strong class="text-dark">${data}</strong>
                                    <small class="text-muted"><i class="fas fa-envelope mr-1"></i> ${row.email || '-'}</small>
                                </div>`;
                    }
                },
                { data: 'nama_prodi', className: "align-middle" },
                {
                    data: 'status_berkas',
                    className: "text-center align-middle",
                    render: function (data) {
                        if (data == 'lulus') return '<span class="badge badge-pill badge-success px-3 py-2 shadow-sm">Lulus</span>';
                        if (data == 'gagal') return '<span class="badge badge-pill badge-danger px-3 py-2 shadow-sm">Gagal</span>';
                        return '<span class="badge badge-pill badge-warning px-3 py-2 shadow-sm">Pending</span>';
                    }
                },
                {
                    data: 'status_verifikasi_fisik',
                    className: "text-center align-middle",
                    render: function (data, type, row) {
                        var status = data || 'pending';
                        var badge = '';

                        if (status == 'lengkap') {
                            badge = '<span class="badge badge-success shadow-sm"><i class="fas fa-check-circle mr-1"></i> Lengkap</span>';
                        } else if (status == 'tidak_lengkap') {
                            badge = '<span class="badge badge-danger shadow-sm"><i class="fas fa-times-circle mr-1"></i> Kurang</span>';
                        } else {
                            badge = '<span class="badge badge-secondary shadow-sm"><i class="fas fa-clock mr-1"></i> Belum</span>';
                        }

                        if (row.bypass_verification == 1) {
                            badge += '<div class="mt-1"><span class="badge badge-warning text-dark"><i class="fas fa-lock-open mr-1"></i> Bypass</span></div>';
                        }
                        return badge;
                    }
                },
                {
                    data: 'catatan_admin',
                    className: "text-center align-middle",
                    render: function (data) {
                        if (data && data.trim() !== '') {
                            return '<i class="fas fa-check-square text-success" title="' + data.replace(/"/g, '&quot;') + '"></i>';
                        }
                        return '<i class="far fa-square text-muted"></i>';
                    }
                },
                {
                    data: 'updated_at',
                    className: "text-center align-middle text-muted small",
                    render: function (data) {
                        if (!data) return '-';
                        try {
                            return new Date(data).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric' // Fixed typo 'numeri c'
                            });
                        } catch (e) {
                            return data; // Fallback
                        }
                    }
                },
                {
                    data: 'participant_id',
                    className: "text-center align-middle",
                    orderable: false,
                    render: function (data) {
                        return `<div class="btn-group">
                                    <a href="/admin/verification/physical/${data}" class="btn btn-sm btn-info shadow-sm" title="Verifikasi">
                                        <i class="fas fa-edit"></i> Verifikasi
                                    </a>
                                </div>`;
                    }
                }
            ]
        });

        // Handle Filter Button Logic
        $('#btnFilter').click(function () {
            table.ajax.reload();
        });

        // Auto-reload on filter change
        $('#eligibilityFilter, #statusFilter').change(function () {
            table.ajax.reload();
        });

        // Enter key support for filters
        $('#filterForm input').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                table.ajax.reload();
            }
        });
    });

</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>