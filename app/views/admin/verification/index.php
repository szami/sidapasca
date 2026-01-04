<?php ob_start(); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-double mr-2"></i>
                    Verifikasi Berkas Fisik
                </h3>
            </div>
            
            <div class="card-body">
                <?php if (isset($_GET['success']) && $_GET['success'] == 'import'): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Import Berhasil!</h5>
                    Data verifikasi berhasil diimport. Jumlah baris diproses: <?php echo $_GET['count'] ?? 0; ?>
                </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <?php echo urldecode($_GET['error']); ?>
                </div>
                <?php endif; ?>

                <!-- Statistics Logic -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Eligible</span>
                                <span class="info-box-number"><?php echo $stats['total_eligible']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lengkap</span>
                                <span class="info-box-number"><?php echo $stats['lengkap']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tidak Lengkap</span>
                                <span class="info-box-number"><?php echo $stats['tidak_lengkap']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Belum Verifikasi</span>
                                <span class="info-box-number"><?php echo $stats['belum_verifikasi']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-4 clearfix">
                    <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-file-excel mr-1"></i> Import/Download Template
                    </button>
                    
                    <label class="mr-2">Filter Status:</label>
                    <div class="btn-group">
                        <a href="?status=all" class="btn btn-sm <?php echo $statusFilter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">Semua</a>
                        <a href="?status=pending" class="btn btn-sm <?php echo $statusFilter == 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending/Belum</a>
                        <a href="?status=lengkap" class="btn btn-sm <?php echo $statusFilter == 'lengkap' ? 'btn-success' : 'btn-outline-success'; ?>">Lengkap</a>
                        <a href="?status=tidak_lengkap" class="btn btn-sm <?php echo $statusFilter == 'tidak_lengkap' ? 'btn-danger' : 'btn-outline-danger'; ?>">Tidak Lengkap</a>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nomor Peserta</th>
                                <th>Nama Peserta</th>
                                <th>Program Studi</th>
                                <th>Status Online</th>
                                <th>Status Fisik</th>
                                <th>Terakhir Update</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($verifications as $i => $row): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <?php echo $row['nomor_peserta'] ? '<span class="badge badge-info">' . $row['nomor_peserta'] . '</span>' : '<span class="badge badge-secondary">Belum ada</span>'; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
                                
                                <td class="text-center">
                                    <?php if ($row['status_berkas'] == 'lulus'): ?>
                                        <span class="badge badge-success">Lulus</span>
                                    <?php elseif ($row['status_berkas'] == 'gagal'): ?>
                                        <span class="badge badge-danger">Gagal</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php 
                                    $status = $row['status_verifikasi_fisik'] ?? 'pending';
                                    $allStatus = ['pending', 'lengkap', 'tidak_lengkap'];
                                    // if null or empty, treat as pending/belum
                                    if (empty($status)) $status = 'pending';
                                    
                                    if ($status == 'lengkap'): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>
                                    <?php elseif ($status == 'tidak_lengkap'): ?>
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Tidak Lengkap</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fas fa-minus"></i> Belum</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($row['bypass_verification'])): ?>
                                        <br><span class="badge badge-warning mt-1"><i class="fas fa-lock-open"></i> Bypassed</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php echo $row['updated_at'] ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-'; ?>
                                </td>
                                
                                <td class="text-center">
                                    <a href="/admin/verification/physical/<?php echo $row['participant_id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Verifikasi
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Verifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/admin/verification/physical/import" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Petunjuk:</strong>
                        <ol class="pl-3 mb-0">
                            <li>Download template Excel (data peserta sudah terisi).</li>
                            <li>Isi kolom checklist dengan angka <strong>1</strong> (Ada) atau <strong>0</strong> (Tidak).</li>
                            <li>Upload file yang sudah diisi.</li>
                        </ol>
                    </div>
                    
                    <div class="form-group text-center mb-4">
                         <label class="d-block text-muted mb-2">Langkah 1: Download Template</label>
                        <a href="/admin/verification/physical/import/template" class="btn btn-primary">
                            <i class="fas fa-download mr-1"></i> Download Template Peserta
                        </a>
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block text-muted mb-2">Langkah 2: Upload File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="verification_file" name="verification_file" required accept=".xlsx, .xls">
                            <label class="custom-file-label" for="verification_file">Pilih file Excel...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload mr-1"></i> Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Custom File Input Label
$(".custom-file-input").on("change", function() {
  var fileName = $(this).val().split("\\").pop();
  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
});
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php'; 
?>
