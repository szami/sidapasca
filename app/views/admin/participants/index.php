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
                <table class="table table-bordered table-striped datatable">
                    <thead>
                        <tr>
                            <th>No</th>
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
                            <th>Status Berkas</th>
                            <?php if (!($hidePaymentStatus ?? false)): ?>
                                <th>Status Bayar</th>
                            <?php endif; ?>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($participants as $p): ?>
                            <tr>
                                <td>
                                    <?php echo $no++; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($p['photo_filename'])): ?>
                                        <img src="/storage/photos/<?php echo $p['photo_filename']; ?>" alt="Foto"
                                            class="img-thumbnail" style="width: 40px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <?php if (!($hideExamNumber ?? false)): ?>
                                    <td>
                                        <span class="badge badge-light p-2 border">
                                            <?php echo $p['nomor_peserta'] ?: '-'; ?>
                                        </span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <strong>
                                        <?php echo $p['nama_lengkap']; ?>
                                    </strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <?php echo $p['email']; ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $jk = strtoupper($p['jenis_kelamin'] ?? '');
                                    if ($jk == 'L' || $jk == 'LAKI-LAKI')
                                        echo '<span class="badge badge-primary-soft">Laki-laki</span>';
                                    elseif ($jk == 'P' || $jk == 'PEREMPUAN')
                                        echo '<span class="badge badge-danger-soft">Perempuan</span>';
                                    else
                                        echo $jk ?: '-';
                                    ?>
                                </td>
                                <td>
                                    <?php echo $p['nama_prodi']; ?>
                                </td>
                                <?php if (!($hideBilling ?? false)): ?>
                                    <td>
                                        <code><?php echo $p['no_billing']; ?></code>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($p['status_berkas'] == 'lulus'): ?>
                                        <span class="badge badge-success">Lulus</span>
                                    <?php elseif ($p['status_berkas'] == 'gagal'): ?>
                                        <span class="badge badge-danger">Gagal</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (!($hidePaymentStatus ?? false)): ?>
                                    <td>
                                        <?php if ($p['status_pembayaran']): ?>
                                            <span class="badge badge-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($filter == 'exam_ready'): ?>
                                            <?php if (!empty($p['nomor_peserta'])): ?>
                                                <a href="/admin/participants/card/<?php echo $p['id']; ?>" target="_blank"
                                                    class="btn btn-xs btn-info" title="Kartu Ujian"><i
                                                        class="fas fa-id-card"></i></a>
                                            <?php else: ?>
                                                <button class="btn btn-xs btn-secondary" disabled title="Belum ada nomor peserta"><i
                                                        class="fas fa-id-card"></i></button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="/admin/participants/form/<?php echo $p['id']; ?>" target="_blank"
                                                class="btn btn-xs btn-primary" title="Formulir"><i
                                                    class="fas fa-file-alt"></i></a>
                                        <?php endif; ?>
                                        <a href="/admin/participants/view/<?php echo $p['id']; ?>"
                                            class="btn btn-xs btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                        <?php if (($_SESSION['admin_role'] ?? 'superadmin') === 'superadmin'): ?>
                                            <a href="/admin/participants/edit/<?php echo $p['id']; ?>"
                                                class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                            <?php if (\App\Models\Setting::get('allow_delete', '1') == '1'): ?>
                                                <a href="/admin/participants/delete/<?php echo $p['id']; ?>"
                                                    class="btn btn-xs btn-danger" onclick="return confirm('Hapus data ini?')"
                                                    title="Hapus"><i class="fas fa-trash"></i></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>

<script>
function filterByProdi() {
    const prodi = document.getElementById('prodiFilter').value;
    const currentParams = new URLSearchParams(window.location.search);
    
    if (prodi === 'all') {
        currentParams.delete('prodi');
    } else {
        currentParams.set('prodi', prodi);
    }
    
    window.location.href = '?' + currentParams.toString();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>