<?php ob_start(); ?>
<div class="row">
    <div class="col-12">

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body p-2">
                <span class="mr-2 font-weight-bold">Filter Status:</span>
                <div class="btn-group btn-group-toggle">
                    <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/scheduler?status=unscheduled"
                        class="btn btn-sm btn-outline-primary <?php echo $filterStatus == 'unscheduled' ? 'active' : ''; ?>">
                        Belum Terjadwal
                    </a>
                    <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/scheduler?status=scheduled"
                        class="btn btn-sm btn-outline-success <?php echo $filterStatus == 'scheduled' ? 'active' : ''; ?>">
                        Sudah Terjadwal
                    </a>
                    <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/scheduler?status=all"
                        class="btn btn-sm btn-outline-secondary <?php echo $filterStatus == 'all' ? 'active' : ''; ?>">
                        Semua
                    </a>
                    <div class="row align-items-center mt-2">
                        <div class="col-md-6">
                            <form method="GET" action="/admin/scheduler" class="form-inline">
                                <input type="hidden" name="status" value="<?php echo $filterStatus; ?>">
                                <label class="mr-2">Filter Prodi:</label>
                                <select name="prodi" class="form-control form-control-sm mr-2" style="max-width: 300px;"
                                    onchange="this.form.submit()">
                                    <option value="">-- Semua Prodi --</option>
                                    <?php foreach ($prodis as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['nama_prodi']); ?>" <?php echo $filterProdi == $p['nama_prodi'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['nama_prodi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Form -->
            <form id="schedulerForm" method="POST" action="/admin/scheduler/assign">

                <!-- Sticky Action Bar -->
                <div class="card card-warning sticky-top shadow-sm" style="top: 10px; z-index: 1040">
                    <div class="card-header py-2">
                        <h3 class="card-title mt-1"><i class="fas fa-calendar-check mr-1"></i> Penjadwalan Massal</h3>
                        <div class="card-tools">
                            <a href="/admin/scheduler/export-cat" class="btn btn-sm btn-success mr-1">
                                <i class="fas fa-file-excel"></i> Export Jadwal CAT
                            </a>
                            <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/scheduler/rooms"
                                class="btn btn-sm btn-info">
                                <i class="fas fa-desktop"></i> Monitor Ruangan
                            </a>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <select name="session_id" class="form-control form-control-sm select2" required>
                                    <option value="">-- Pilih Sesi Ujian (Tanggal / Jam / Ruang) --</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?php echo $s['id']; ?>">
                                            <?php echo date('d-m-Y', strtotime($s['tanggal'])) . ' | ' . $s['waktu_mulai'] . '-' . $s['waktu_selesai'] . ' | ' . $s['nama_ruang'] . ' | ' . $s['nama_sesi']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-7 text-right">
                                <span id="selectedCount" class="badge badge-info mr-2" style="font-size: 14px">0 Peserta
                                    Dipilih</span>
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold" name="action"
                                    value="assign">
                                    <i class="fas fa-save mr-1"></i> Simpan Jadwal
                                </button>
                                <button type="submit" class="btn btn-danger btn-sm font-weight-bold ml-1" name="action"
                                    value="unassign"
                                    formaction="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/scheduler/unassign"
                                    onclick="return confirm('Hapus jadwal peserta terpilih?')">
                                    <i class="fas fa-times mr-1"></i> Hapus Jadwal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped table-hover datatable-check">
                            <thead>
                                <tr>
                                    <th width="10px" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>No Peserta</th>
                                    <th>Nama Lengkap</th>
                                    <th>Prodi</th>
                                    <th>Status Jadwal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Init DataTable
            const table = $('.datatable-check').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/admin/api/scheduler-data",
                    "data": function (d) {
                        d.status = '<?php echo $filterStatus; ?>';
                        d.prodi = '<?php echo $filterProdi; ?>';
                    }
                },
                "columns": [
                    {
                        "data": "id",
                        "orderable": false,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            return '<input type="checkbox" name="participant_ids[]" value="' + data + '" class="p-check">';
                        }
                    },
                    { "data": "nomor_peserta" },
                    { "data": "nama_lengkap" },
                    { "data": "nama_prodi" },
                    {
                        "data": "ruang_ujian",
                        "render": function (data, type, row) {
                            if (data) {
                                // Simple date formatting if possible, or just raw
                                return '<span class="text-success" style="font-size: 12px; line-height: 1.2"><b>' +
                                    (row.tanggal_ujian || '-') + '</b><br>' +
                                    (row.waktu_ujian || '-') + '<br>' +
                                    data + '</span>';
                            } else {
                                return '<span class="badge badge-warning">Belum Ada Jadwal</span>';
                            }
                        }
                    }
                ],
                "order": [[3, "asc"]], // Order by Prodi
                "drawCallback": function () {
                    updateCount(); // Update selected count on redraw
                    // Re-bind events? No, using delegated event below
                }
            });

            // Checkbox Logic
            const checkAll = document.getElementById("checkAll");
            const countBadge = document.getElementById("selectedCount");

            function updateCount() {
                let count = document.querySelectorAll(".p-check:checked").length;
                countBadge.innerText = count + " Peserta Dipilih";
            }

            // Handle "Check All" click
            $('#checkAll').on('click', function () {
                var rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
                updateCount();
            });

            // Handle individual checkbox click (delegated)
            $('.datatable-check tbody').on('change', '.p-check', function () {
                if (!this.checked) {
                    var el = $('#checkAll').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
                updateCount();
            });
        });
    </script>

    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../../layouts/admin.php';
    ?>