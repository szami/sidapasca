<?php
$title = 'Daya Tampung Prodi';
ob_start();
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Daya Tampung Prodi</h3>
                <p class="text-subtitle text-muted">Input kuota penerimaan untuk semester aktif</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelulusan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Filter Semester</h4>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label for="semester_id">Semester</label>
                    <select name="semester_id" id="semester_id" class="custom-select shadow-sm"
                        onchange="this.form.submit()">
                        <?php foreach ($semesters as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $s['id'] == $currentSemester ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['kode']); ?> -
                                <?php echo htmlspecialchars($s['nama']); ?>     <?php echo $s['is_active'] ? ' (Aktif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Input Daya Tampung</h4>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Data berhasil disimpan.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <form action="/admin/graduation/quotas/save" method="POST" id="quotaForm">
                <input type="hidden" name="semester_id" value="<?php echo $currentSemester; ?>">

                <div class="table-responsive">
                    <table class="table table-striped" id="quotasTable">
                        <thead>
                            <tr>
                                <th>Kode Prodi</th>
                                <th>Nama Program Studi</th>
                                <th width="200">Daya Tampung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX Populated -->
                        </tbody>
                    </table>
                </div>

                <div id="hiddenInputs"></div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        const changedQuotas = {};

        const table = $('#quotasTable').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "ajax": {
                "url": APP_URL + "/api/master/quotas",
                "data": function (d) {
                    d.semester_id = $('#semester_id').val();
                }
            },
            "columns": [
                {
                    "data": "kode_prodi",
                    "render": function (data) { return data || '-'; }
                },
                { "data": "nama_prodi" },
                {
                    "data": "daya_tampung",
                    "orderable": false,
                    "render": function (data, type, row) {
                        const code = row.kode_prodi || row.nama_prodi;
                        // Check if we have a locally changed value
                        const val = changedQuotas[code] !== undefined ? changedQuotas[code] : (data || 0);
                        return `<input type="number" min="0" class="form-control quota-input" 
                                       data-code="${code}" value="${val}" 
                                       onchange="updateQuotaState(this)">`;
                    }
                }
            ],
            "drawCallback": function () {
                // Ensure inputs on the current page reflect the stored state
                $('.quota-input').each(function () {
                    const code = $(this).data('code');
                    if (changedQuotas[code] !== undefined) {
                        $(this).val(changedQuotas[code]);
                    }
                });
            }
        });

        window.updateQuotaState = function (input) {
            const code = $(input).data('code');
            const val = $(input).val();
            changedQuotas[code] = val;
        };

        $('#quotaForm').on('submit', function (e) {
            // Prevent default just to be sure we append then submit? No, default submit is fine if we append synchronously.
            // But let's log to be sure.
            const container = $('#hiddenInputs');
            container.empty();
            // Add all changed quotas as hidden inputs using array notation
            for (const code in changedQuotas) {
                // Use quotas[code]
                container.append(`<input type="hidden" name="quotas[${code}]" value="${changedQuotas[code]}">`);
            }
            return true;
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>