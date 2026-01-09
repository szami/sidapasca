<?php ob_start(); ?>
<div class="row">
    <div class="col-12">

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body p-2">
                <?php
                $db = \App\Utils\Database::connection();
                $semesters = $db->query("SELECT * FROM semesters ORDER BY id DESC")->fetchAll();
                $selectedSemesterId = \Leaf\Http\Request::get('semester_id') ?: (\App\Models\Semester::getActive()['id'] ?? null);
                ?>
                <div class="row align-items-center mb-2">
                    <div class="col-md-4">
                        <form method="GET" action="" id="semesterFilterForm" class="form-inline">
                            <label class="mr-2 font-weight-bold">Semester:</label>
                            <select name="semester_id" class="form-control form-control-sm shadow-sm" style="min-width: 200px;" onchange="this.form.submit()">
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?= $sem['id'] ?>" <?= $selectedSemesterId == $sem['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sem['nama']) ?> (<?= htmlspecialchars($sem['kode']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

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
                                <input type="hidden" name="semester_id" value="<?php echo $selectedSemesterId; ?>">
                                <input type="hidden" name="status" value="<?php echo $filterStatus; ?>">
                                <label class="mr-2">Filter Prodi:</label>
                                <select name="prodi" class="form-control form-control-sm mr-2" style="max-width: 300px;"
                                    <?php echo ($isReadOnly && !empty($adminProdiCode)) ? 'disabled' : 'onchange="this.form.submit()"'; ?>>
                                    <option value="">-- Semua Prodi --</option>
                                    <?php foreach ($prodis as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['nama_prodi']); ?>" 
                                            <?php 
                                                // Verify matching logic. 
                                                // If admin_prodi (code), we found its name via Controller logic or pass name?
                                                // Current logic: filterProdi has the 'value' (Name or Code).
                                                // Standard logic uses Name in 'value'.
                                                // If adminProdiCode is set, the controller sets filterProdi to code, which might NOT match Name in Option value.
                                                // Wait, controller blindly sets filterProdi = adminProdiCode.
                                                // Droppdown values are Names.
                                                // This is a mismatch risk.
                                                // Fix: Controller sets filterProdi to NAME if code matches?
                                                // Or we handle matching here: Match if name==filterProdi OR code matches logic?
                                                // Users table: prodi_id = CODE. Participants: kode_prodi = CODE, nama_prodi = NAME.
                                                // Dropdown uses nama_prodi.
                                                // Let's rely on filterProdi matching the dropdown value. 
                                                echo ($filterProdi == $p['nama_prodi']) ? 'selected' : ''; 
                                            ?>>
                                            <?php echo htmlspecialchars($p['nama_prodi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($isReadOnly && !empty($adminProdiCode)): ?>
                                    <!-- Hidden input to maintain filter when disabled -->
                                    <input type="hidden" name="prodi" value="<?php echo htmlspecialchars($filterProdi); ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if ($isReadOnly && !empty($adminProdiCode)): ?>
                    <!-- Locked Prodi Filter Display -->
                    <div class="alert alert-info py-2 mt-2 mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Menampilkan jadwal untuk Program Studi: 
                        <strong><?php echo htmlspecialchars($prodis[array_search($filterProdi, array_column($prodis, 'nama_prodi'))]['nama_prodi'] ?? $filterProdi); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Assignment Form -->
            <form id="schedulerForm" method="POST" action="/admin/scheduler/assign">

                <!-- Sticky Action Bar -->
                <?php if (!$isReadOnly): ?>
                <div class="card card-warning sticky-top shadow-sm" style="top: 10px; z-index: 1040">
                    <div class="card-header py-2">
                        <h3 class="card-title mt-1"><i class="fas fa-calendar-check mr-1"></i> Penjadwalan Massal</h3>
                        <div class="card-tools">
                             <a href="/admin/scheduler/export-cat?semester_id=<?php echo $selectedSemesterId; ?>" class="btn btn-sm btn-success mr-1">
                                <i class="fas fa-file-excel"></i> Export Jadwal
                            </a>
                             <a href="/admin/participants/export?semester_id=<?php echo $selectedSemesterId; ?>" class="btn btn-sm btn-danger mr-1">
                                <i class="fas fa-user-shield"></i> Export Detail IT
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
                <?php else: ?>
                    <!-- Read Only Header -->
                    <div class="card card-outline card-primary mb-2">
                        <div class="card-header py-2">
                            <h3 class="card-title mt-1"><i class="fas fa-calendar-check mr-1"></i> Jadwal Tes Potensi Akademik</h3>
                            <div class="card-tools">
                                <a href="/admin/scheduler/export-cat?semester_id=<?php echo $selectedSemesterId; ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel mr-1"></i> Export Jadwal
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered table-striped table-hover datatable-check">
                            <thead>
                                <tr>
                                    <?php if (!$isReadOnly): ?>
                                    <th width="10px" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <?php endif; ?>
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
                        d.semester_id = '<?php echo $selectedSemesterId; ?>';
                        d.status = '<?php echo $filterStatus; ?>';
                        d.prodi = '<?php echo $filterProdi; ?>';
                    }
                },
                "columns": [
                    <?php if (!$isReadOnly): ?>
                    {
                        "data": "id",
                        "orderable": false,
                        "className": "text-center",
                        "render": function (data, type, row) {
                            return '<input type="checkbox" name="participant_ids[]" value="' + data + '" class="p-check">';
                        }
                    },
                    <?php endif; ?>
                    { "data": "nomor_peserta" },
                    { "data": "nama_lengkap" },
                    { "data": "nama_prodi" },
                    {
                        "data": "ruang_ujian",
                        "render": function (data, type, row) {
                            if (data) {
                                // Simple date formatting if possible, or just raw
                                // Format Date to dd-mm-yyyy
                                let dateStr = row.tanggal_ujian || '-';
                                if (dateStr !== '-') {
                                    let parts = dateStr.split('-');
                                    if (parts.length === 3) {
                                        dateStr = parts[2] + '-' + parts[1] + '-' + parts[0];
                                    }
                                }

                                return '<span class="text-success" style="font-size: 12px; line-height: 1.2"><b>' +
                                    dateStr + '</b><br>' +
                                    (row.waktu_ujian || '-') + '<br>' +
                                    data + '</span>';
                            } else {
                                return '<span class="badge badge-warning">Belum Ada Jadwal</span>';
                            }
                        }
                    }
                ],
                "order": [[<?php echo $isReadOnly ? 2 : 3; ?>, "asc"]], // Order by Prodi (Index changes if check removed)
                "drawCallback": function () {
                    updateCount(); // Update selected count on redraw
                    // Re-bind events? No, using delegated event below
                }
            });

            // Checkbox Logic
            const checkAll = document.getElementById("checkAll");
            const countBadge = document.getElementById("selectedCount");

            function updateCount() {
                if (!countBadge) return; // Exit if elements don't exist (Read Only mode)
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