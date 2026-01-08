<?php ob_start(); ?>
<section class="content-header">
    <div class="container-fluid">
        <h1>Payment Export Tools</h1>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-data-tab" data-toggle="pill" href="#tab-data"
                                    role="tab">Data Reference</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-generate-tab" data-toggle="pill" href="#tab-generate"
                                    role="tab">Generate Payment File</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-three-tabContent">

                            <!-- REFERENCE DATA TAB -->
                            <div class="tab-pane fade show active" id="tab-data" role="tabpanel">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div
                                                class="card-header bg-info d-flex justify-content-between align-items-center">
                                                <h3 class="card-title">1. Import Data Mentah (Sirema)</h3>
                                                <a href="/admin/payment-export/template-sirema"
                                                    class="btn btn-tool btn-xs border-white text-white" target="_blank"
                                                    title="Download Template">
                                                    <i class="fas fa-download mr-1"></i> Template
                                                </a>
                                            </div>
                                            <form action="/admin/payment-export/import-sirema" method="post"
                                                enctype="multipart/form-data">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>File Excel (SIREMA)</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                name="file_sirema" required accept=".xlsx,.xls">
                                                            <label class="custom-file-label">Choose file...</label>
                                                        </div>
                                                        <small class="text-muted">Must contain 'Nomor Ujian' and 'NIM'
                                                            columns.</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Periode</label>
                                                        <input type="text" name="periode" class="form-control"
                                                            value="20251">
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <button type="submit" class="btn btn-info">Import / Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div
                                                class="card-header bg-warning d-flex justify-content-between align-items-center">
                                                <h3 class="card-title">2. Import Status Akademik (SIA)</h3>
                                                <a href="/admin/payment-export/template-sia"
                                                    class="btn btn-tool btn-xs border-dark text-dark" target="_blank"
                                                    title="Download Template">
                                                    <i class="fas fa-download mr-1"></i> Template
                                                </a>
                                            </div>
                                            <form action="/admin/payment-export/import-sia" method="post"
                                                enctype="multipart/form-data">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>File Excel (SIA)</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="file_sia"
                                                                required accept=".xlsx,.xls">
                                                            <label class="custom-file-label">Choose file...</label>
                                                        </div>
                                                        <small class="text-muted">Must contain 'NIM' and 'Status'
                                                            columns.</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Periode</label>
                                                        <input type="text" name="periode" class="form-control"
                                                            value="20251">
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <button type="submit" class="btn btn-warning">Validate
                                                        Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Reference Data (All)</h3>
                                        <div class="card-tools">
                                            <span class="badge badge-primary">Total: <?= $stats['total'] ?></span>
                                            <span class="badge badge-success">Verified: <?= $stats['verified'] ?></span>
                                            <span class="badge badge-danger">Unverified:
                                                <?= $stats['unverified'] ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table id="table-payment" class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%;">No</th> <!-- Numbering Column -->
                                                    <th>Nomor Peserta</th>
                                                    <th>NIM (Sirema)</th>
                                                    <th>Nama</th>
                                                    <th>Prodi</th>
                                                    <th>Nominal</th>
                                                    <th>Status SIA</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- PROCESS TAB -->
                            <div class="tab-pane fade" id="tab-generate" role="tabpanel">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i>
                                                    Generate Payment File</h3>
                                            </div>
                                            <form action="/admin/payment-export/generate" method="post"
                                                enctype="multipart/form-data">
                                                <div class="card-body">
                                                    <div class="callout callout-info">
                                                        <h5>How it works:</h5>
                                                        <p>Upload a Payment Template (Excel). The system will search for
                                                            <code>No Ujian</code> column and replace it with
                                                            <code>NIM</code> IF a matching record exists in available
                                                            data.
                                                        </p>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Payment Template File</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                name="template_payment" required accept=".xlsx,.xls">
                                                            <label class="custom-file-label">Choose template...</label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input class="custom-control-input" type="checkbox"
                                                                id="setZero" name="set_zero" value="1">
                                                            <label for="setZero" class="custom-control-label">Set
                                                                Nominal to 0 (Clear Debt)</label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Periode</label>
                                                        <input type="text" name="periode" class="form-control"
                                                            value="20251">
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <button type="submit" class="btn btn-success btn-lg btn-block">
                                                        <i class="fas fa-download mr-1"></i> Process & Download
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DataTables Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script>
    $(document).ready(function () {
        $('#table-payment').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "/admin/payment-export/api-data",
            "columns": [
                {
                    "data": null,
                    "render": function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    "orderable": false
                },
                { "data": "nomor_peserta" },
                {
                    "data": "nim",
                    "render": function (data, type, row) {
                        return '<span class="text-bold text-primary">' + data + '</span>';
                    }
                },
                { "data": "nama" },
                { "data": "kode_prodi" },
                { "data": "nominal_formatted" },
                { "data": "status_html" }
            ],
            "responsive": true,
            "autoWidth": false,
            "ordering": true,
            "info": true,
            "paging": true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "pagingType": "simple_numbers", // Shows Prev, Page Nums, Next
            "language": {
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "order": [[1, "desc"]] // Sort by No Peserta by default
        });

        // Custom File Input
        bsCustomFileInput.init();
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Payment Export Tools (Sirema)';
$menu = 'payment_export';
include __DIR__ . '/../../layouts/admin.php';
?>