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
                                <?php foreach ($participants as $p): ?>
                                    <?php
                                    $hasSchedule = !empty($p['ruang_ujian']);
                                    $scheduleText = '-';
                                    if ($hasSchedule) {
                                        $scheduleText = "<b>" . date('d/m', strtotime($p['tanggal_ujian'])) . "</b><br>" . $p['waktu_ujian'] . "<br>" . $p['ruang_ujian'];
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="participant_ids[]" value="<?php echo $p['id']; ?>"
                                                class="p-check">
                                        </td>
                                        <td>
                                            <?php echo $p['nomor_peserta']; ?>
                                        </td>
                                        <td>
                                            <?php echo $p['nama_lengkap']; ?>
                                        </td>
                                        <td>
                                            <?php echo $p['nama_prodi']; ?>
                                        </td>
                                        <td>
                                            <?php if ($hasSchedule): ?>
                                                <span class="text-success" style="font-size: 12px; line-height: 1.2">
                                                    <?php echo $scheduleText; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum Ada Jadwal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Simple Checkbox Logic
        document.addEventListener("DOMContentLoaded", function () {
            const checkAll = document.getElementById("checkAll");
            const checks = document.querySelectorAll(".p-check");
            const countBadge = document.getElementById("selectedCount");

            function updateCount() {
                let count = document.querySelectorAll(".p-check:checked").length;
                countBadge.innerText = count + " Peserta Dipilih";
            }

            checkAll.addEventListener("change", function () {
                checks.forEach(c => c.checked = checkAll.checked);
                updateCount();
            });

            checks.forEach(c => {
                c.addEventListener("change", updateCount);
            });
        });
    </script>

    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../../layouts/admin.php';
    ?>