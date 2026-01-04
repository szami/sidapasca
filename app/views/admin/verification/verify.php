<?php ob_start(); ?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-2"></i>
                    Checklist Verifikasi Fisik
                </h3>
            </div>
            <form action="/admin/verification/physical/<?php echo $participant['id']; ?>/save" method="POST">
                <div class="card-body">

                    <?php if (isset($_GET['success'])): ?>
                        <?php if ($_GET['success'] == 'reset'): ?>
                            <div class="alert alert-warning alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Reset Berhasil!</h5>
                                Data verifikasi peserta telah dikembalikan ke status belum diverifikasi.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                                Data verifikasi berhasil disimpan.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Document List Table -->
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="50%">Nama Dokumen</th>
                                <th width="20%" class="text-center">Ketersediaan</th>
                                <th width="30%">Jumlah Lembar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- WAJIB UNTUK SEMUA -->
                            <tr>
                                <td colspan="3" class="bg-secondary p-1 pl-2"><small><strong>DOKUMEN WAJIB (SEMUA
                                            JENJANG)</strong></small></td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Formulir Pendaftaran</strong><br>
                                    <small class="text-muted">Hasil cetak sistem (2 rangkap)</small>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="check_formulir"
                                            name="formulir_pendaftaran" value="1" <?php echo (!empty($verification) ? ($verification['formulir_pendaftaran'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="check_formulir">Ada</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="formulir_pendaftaran_jumlah"
                                            value="<?php echo $verification['formulir_pendaftaran_jumlah'] ?? 2; ?>"
                                            min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">lbr</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Ijazah S1 Legalisir</strong><br>
                                    <small class="text-muted">Fotokopi legalisir basah terbaru (3 rangkap)</small>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="check_ijazah_s1"
                                            name="ijazah_s1_legalisir" value="1" <?php echo (!empty($verification) ? ($verification['ijazah_s1_legalisir'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="check_ijazah_s1">Ada</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="ijazah_s1_jumlah"
                                            value="<?php echo $verification['ijazah_s1_jumlah'] ?? 3; ?>" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">lbr</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Transkrip S1 Legalisir</strong><br>
                                    <small class="text-muted">Fotokopi legalisir basah terbaru (3 rangkap)</small>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="check_transkrip_s1"
                                            name="transkrip_s1_legalisir" value="1" <?php echo (!empty($verification) ? ($verification['transkrip_s1_legalisir'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="check_transkrip_s1">Ada</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="transkrip_s1_jumlah"
                                            value="<?php echo $verification['transkrip_s1_jumlah'] ?? 3; ?>" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">lbr</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Bukti Setor / Slip Pembayaran</strong><br>
                                    <small class="text-muted">Asli atau fotokopi (1 rangkap)</small>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="check_bukti_bayar"
                                            name="bukti_pembayaran" value="1" <?php echo (!empty($verification) ? ($verification['bukti_pembayaran'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="check_bukti_bayar">Ada</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="bukti_pembayaran_jumlah"
                                            value="<?php echo $verification['bukti_pembayaran_jumlah'] ?? 1; ?>"
                                            min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">lbr</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <strong>Surat Rekomendasi</strong> <span
                                        class="badge badge-secondary">Opsional</span><br>
                                    <small class="text-muted">Dari atasan atau akademisi</small>
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="check_rekomendasi"
                                            name="surat_rekomendasi" value="1" <?php echo (!empty($verification) ? ($verification['surat_rekomendasi'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="check_rekomendasi">Ada</label>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" name="surat_rekomendasi_jumlah"
                                            value="<?php echo $verification['surat_rekomendasi_jumlah'] ?? 0; ?>"
                                            min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">lbr</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- KHUSUS S3 -->
                            <?php if ($isS3): ?>
                                <tr>
                                    <td colspan="3" class="bg-secondary p-1 pl-2"><small><strong>DOKUMEN TAMBAHAN KHUSUS S3
                                                / DOKTOR</strong></small></td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>Ijazah S2 Legalisir</strong><br>
                                        <small class="text-muted">Fotokopi legalisir basah terbaru (3 rangkap)</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="check_ijazah_s2"
                                                name="ijazah_s2_legalisir" value="1" <?php echo (!empty($verification) ? ($verification['ijazah_s2_legalisir'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="check_ijazah_s2">Ada</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" name="ijazah_s2_jumlah"
                                                value="<?php echo $verification['ijazah_s2_jumlah'] ?? 3; ?>" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">lbr</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <strong>Transkrip S2 Legalisir</strong><br>
                                        <small class="text-muted">Fotokopi legalisir basah terbaru (3 rangkap)</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="check_transkrip_s2"
                                                name="transkrip_s2_legalisir" value="1" <?php echo (!empty($verification) ? ($verification['transkrip_s2_legalisir'] ?? 0) : 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="check_transkrip_s2">Ada</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" name="transkrip_s2_jumlah"
                                                value="<?php echo $verification['transkrip_s2_jumlah'] ?? 3; ?>" min="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">lbr</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr class="bg-light text-muted text-center" style="display:none;">
                                    <td colspan="3"><em>Peserta Program Magister (S2), tidak memerlukan dokumen S2.</em>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>

                    <div class="form-group mt-3">
                        <label>Catatan Admin:</label>
                        <textarea class="form-control" name="catatan_admin" rows="3"
                            placeholder="Tambahkan catatan jika ada dokumen yang kurang jelas atau hal lain..."><?php echo htmlspecialchars($verification['catatan_admin'] ?? ''); ?></textarea>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan
                        Verifikasi</button>

                    <div class="float-right">
                        <form action="/admin/verification/physical/<?php echo $participant['id']; ?>/reset"
                            method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin mereset verifikasi? Data verifikasi akan dihapus dan kembali ke status awal.');"
                            class="d-inline mr-2">
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-restore mr-1"></i>
                                Reset</button>
                        </form>
                        <a href="/admin/verification/physical" class="btn btn-default">Kembali</a>
                    </div>
                </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-4">
        <!-- Participant Info -->
        <div class="card card-info card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <?php if (!empty($participant['photo_filename'])): ?>
                        <img class="profile-user-img img-fluid img-circle"
                            src="/storage/photos/<?php echo $participant['photo_filename']; ?>" alt="User profile picture"
                            style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <img class="profile-user-img img-fluid img-circle"
                            src="https://ui-avatars.com/api/?name=<?php echo urlencode($participant['nama_lengkap']); ?>"
                            alt="User profile picture">
                    <?php endif; ?>
                </div>

                <h3 class="profile-username text-center">
                    <?php echo $participant['nama_lengkap']; ?>
                </h3>
                <p class="text-muted text-center">
                    <?php echo $participant['nomor_peserta'] ?? 'Belum ada nomor'; ?>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Program Studi</b> <a class="float-right">
                            <?php echo $participant['nama_prodi']; ?>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Jenjang</b> <a class="float-right">
                            <?php echo $isS3 ? 'S3 (Doktor)' : 'S2 (Magister)'; ?>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Status Online</b>
                        <a class="float-right">
                            <?php echo $participant['status_berkas'] == 'lulus' ? '<span class="badge badge-success">Lulus</span>' : '<span class="badge badge-warning">' . $participant['status_berkas'] . '</span>'; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Verification Status Card -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Status Verifikasi Fisik</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php
                    $status = $verification['status_verifikasi_fisik'] ?? 'pending';
                    if ($status == 'lengkap'):
                        ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                            <strong>LENGKAP</strong><br>
                            Peserta dapat mencetak kartu ujian.
                        </div>
                    <?php elseif ($status == 'tidak_lengkap'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle fa-2x mb-2"></i><br>
                            <strong>TIDAK LENGKAP</strong><br>
                            Harap lengkapi dokumen yang kurang.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-hourglass-half fa-2x mb-2"></i><br>
                            <strong>BELUM DIVERIFIKASI</strong>
                        </div>
                    <?php endif; ?>
                </div>

                <hr>

                <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
                    <!-- Bypass Option -->
                    <div class="form-group border border-warning rounded p-3 bg-light">
                        <label class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Area Berbahaya</label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check_bypass" name="bypass_verification"
                                value="1" <?php echo ($verification['bypass_verification'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="custom-control-label text-danger font-weight-bold" for="check_bypass">
                                Force Allow / Bypass Verifikasi
                            </label>
                            <small class="form-text text-muted mt-1">
                                Jika dicentang, peserta dapat men-download kartu ujian <strong>meskipun dokumen fisik belum
                                    lengkap</strong>. Gunakan dengan bijak.
                                </label>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <!-- Closing the form tag that started in the left column -->
            </form>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>