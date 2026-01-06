<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>
                    <?php echo $pageTitle ?? 'Data Peserta'; ?>
                    <?php if (isset($activeSemester['nama'])): ?>
                        <span class="badge badge-info ml-2" style="font-size: 0.8rem; vertical-align: middle;">
                            Semester:
                            <?php echo $activeSemester['nama']; ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <div class="card-tools">
                    <?php if (($_SESSION['admin_role'] ?? 'superadmin') === 'superadmin'): ?>
                        <a href="/admin/import" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Import Baru</a>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            <!--/.card-header -->
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-4">
                    <?php if (!($isAdminProdi ?? false)): ?>
                    <div class="col-md-6">
                        <label class="font-weight-bold"><i class="fas fa-graduation-cap mr-1"></i> Program Studi</label>
                        <select id="prodiFilter" class="form-control form-control-sm" onchange="filterByProdi()">
                            <option value="all" <?php echo $prodiFilter === 'all' ? 'selected' : ''; ?>>
                                📚 Semua Program
                            </option>
                            <?php foreach ($prodiList as $prodi): ?>
                                <option value="<?php echo htmlspecialchars($prodi['nama_prodi']); ?>" 
                                        <?php echo $prodiFilter === $prodi['nama_prodi'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prodi['nama_prodi']); ?> 
                                    (<?php echo $prodi['total']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <label class="font-weight-bold"><i class="fas fa-graduation-cap mr-1"></i> Program Studi Anda</label>
                        <div class="alert alert-info mb-0 py-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong><?php echo htmlspecialchars($prodiName ?? 'Prodi Anda'); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Status Filter - Only show for "lulus" filter -->
                <?php if ($filter === 'lulus'): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold"><i class="fas fa-money-bill-wave mr-1"></i> Filter Status Pembayaran</label>
                        <div class="btn-group d-block" role="group">
                            <a href="?filter=<?php echo $filter; ?>&prodi=<?php echo $prodiFilter; ?>&payment=all" 
                               class="btn btn-sm <?php echo ($paymentFilter ?? 'all') === 'all' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                                📊 Semua
                            </a>
                            <a href="?filter=<?php echo $filter; ?>&prodi=<?php echo $prodiFilter; ?>&payment=paid" 
                               class="btn btn-sm <?php echo ($paymentFilter ?? 'all') === 'paid' ? 'btn-success' : 'btn-outline-success'; ?>">
                                ✅ Lunas
                            </a>
                            <a href="?filter=<?php echo $filter; ?>&prodi=<?php echo $prodiFilter; ?>&payment=unpaid" 
                               class="btn btn-sm <?php echo ($paymentFilter ?? 'all') === 'unpaid' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                ⏳ Belum Bayar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Active Filters Display -->
                <?php if ($filter !== 'all' || $prodiFilter !== 'all' || ($paymentFilter ?? 'all') !== 'all'): ?>
                    <div class="alert alert-info alert-dismissible mb-3 py-2">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong><i class="fas fa-filter mr-1"></i> Filter Aktif:</strong>
                        <?php if ($filter !== 'all'): ?>
                            <span class="badge badge-dark">Status: <?php echo ucfirst(str_replace('_', ' ', $filter)); ?></span>
                        <?php endif; ?>
                        <?php if ($prodiFilter !== 'all'): ?>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($prodiFilter); ?></span>
                        <?php endif; ?>
                        <?php if (($paymentFilter ?? 'all') !== 'all'): ?>
                            <span class="badge badge-<?php echo $paymentFilter === 'paid' ? 'success' : 'warning'; ?>">
                                Pembayaran: <?php echo $paymentFilter === 'paid' ? 'Lunas' : 'Belum Bayar'; ?>
                            </span>
                        <?php endif; ?>
                        <a href="?filter=all&prodi=all&payment=all" class="badge badge-secondary ml-2">
                            <i class="fas fa-times"></i> Reset Semua Filter
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Data Table -->
                <table class="table table-bordered table-striped" id="participantsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <?php if (!($hideExamNumber ?? false)): ?>
                                <th>No Peserta</th>
                            <?php endif; ?>
                            <th>Nama Peserta & Email</th>
                            <th>JK</th>
                            <th>Prodi</th>
                            <?php if (!($hideBilling ?? false)): ?>
                                <th>No Billing</th>
                            <?php endif; ?>
                            <th>Status Berkas Fisik</th>
                            <?php if (!($hidePaymentStatus ?? false)): ?>
                                <th>Status Bayar</th>
                            <?php endif; ?>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by DataTables Server-Side -->
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>

<script>
$(function () {
    const table = $('#participantsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": APP_URL + "/api/participants",
            "data": function (d) {
                d.filter = '<?php echo $filter; ?>';
                d.prodi = '<?php echo $prodiFilter; ?>';
                d.payment = '<?php echo $paymentFilter; ?>';
            }
        },
        "order": [[0, "desc"]],
        "columns": [
            { "data": "id" },
            { 
                "data": "photo_filename",
                "orderable": false,
                "render": function(data, type, row) {
                    if (data) {
                        // Check if data already contains 'photos/' (new structure)
                        let url = '';
                        if (data.indexOf('photos/') !== -1) {
                             url = '/storage/' + data;
                        } else {
                             // Legacy
                             url = '/storage/photos/' + data;
                        }
                        return `<img src="${url}" alt="Foto" class="img-thumbnail" style="width: 40px; height: 50px; object-fit: cover;">`;
                    }
                    return '<i class="fas fa-user-circle fa-2x text-muted"></i>';
                }
            },
            <?php if (!($hideExamNumber ?? false)): ?>
            { 
                "data": "nomor_peserta",
                "render": function(data) {
                    return `<span class="badge badge-light p-2 border">${data || '-'}</span>`;
                }
            },
            <?php endif; ?>
            { 
                "data": "nama_lengkap",
                "render": function(data, type, row) {
                    return `<strong>${data}</strong><br><small class="text-muted"><i class="fas fa-envelope mr-1"></i>${row.email}</small>`;
                }
            },
            { 
                "data": "jenis_kelamin",
                "className": "text-center",
                "render": function(data) {
                    const jk = (data || '').toUpperCase();
                    if (jk === 'L' || jk === 'LAKI-LAKI') return '<span class="badge badge-primary-soft">Laki-laki</span>';
                    if (jk === 'P' || jk === 'PEREMPUAN') return '<span class="badge badge-danger-soft">Perempuan</span>';
                    return data || '-';
                }
            },
            { "data": "nama_prodi" },
            <?php if (!($hideBilling ?? false)): ?>
            { 
                "data": "no_billing",
                "render": function(data) {
                    return `<code>${data || '-'}</code>`;
                }
            },
            <?php endif; ?>
            { 
                "data": "dv_status_fisik",
                "render": function(data) {
                    if (data === 'lengkap') return '<span class="badge badge-success">Lengkap</span>';
                    if (data === 'tidak_lengkap') return '<span class="badge badge-danger">Tidak Lengkap</span>';
                    return '<span class="badge badge-warning">Belum Dicek</span>';
                }
            },
            <?php if (!($hidePaymentStatus ?? false)): ?>
            { 
                "data": "status_pembayaran",
                "render": function(data) {
                    return data ? '<span class="badge badge-success">Lunas</span>' : '<span class="badge badge-secondary">Belum</span>';
                }
            },
            <?php endif; ?>
            { 
                "data": "id",
                "orderable": false,
                "render": function(data, type, row) {
                    let actions = '<div class="btn-group">';
                    const filter = '<?php echo $filter; ?>';
                    
                    if (filter === 'exam_ready') {
                        if (row.nomor_peserta) {
                            actions += `<a href="/admin/participants/card/${data}" target="_blank" class="btn btn-xs btn-info" title="Kartu Ujian"><i class="fas fa-id-card"></i></a>`;
                        } else {
                            actions += `<button class="btn btn-xs btn-secondary" disabled title="Belum ada nomor peserta"><i class="fas fa-id-card"></i></button>`;
                        }
                    } else {
                        actions += `<a href="/admin/participants/form/${data}" target="_blank" class="btn btn-xs btn-primary" title="Formulir"><i class="fas fa-file-alt"></i></a>`;
                    }
                    
                    actions += `<a href="/admin/participants/view/${data}" class="btn btn-xs btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>`;
                    
                    <?php if (($_SESSION['admin_role'] ?? 'superadmin') === 'superadmin'): ?>
                    actions += `<a href="/admin/participants/edit/${data}" class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-edit"></i></a>`;
                    <?php if (\App\Models\Setting::get('allow_delete', '1') == '1'): ?>
                    actions += `<a href="/admin/participants/delete/${data}" class="btn btn-xs btn-danger" onclick="return confirm('Hapus data ini?')" title="Hapus"><i class="fas fa-trash"></i></a>`;
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ]
    });

    window.filterByProdi = function() {
        const prodi = document.getElementById('prodiFilter').value;
        const currentParams = new URLSearchParams(window.location.search);
        
        if (prodi === 'all') {
            currentParams.delete('prodi');
        } else {
            currentParams.set('prodi', prodi);
        }
        
        // We still reload page for prodi filter because the counts in dropdown/active filter alerts 
        // are PHP-rendered. To make it pure AJAX, we'd need to update those via API too.
        // For now, page reload is fine as the table itself will load via AJAX anyway.
        window.location.href = '?' + currentParams.toString();
    };
});
</script>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>