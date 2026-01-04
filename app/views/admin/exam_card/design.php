<?php ob_start(); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paint-brush mr-1"></i> Desain Kartu Ujian</h3>
            </div>
            <form action="/admin/exam-card/design/save" method="POST">
                <div class="card-body">
                    <?php if (request()->get('msg') === 'success'): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                            Desain kartu ujian telah diperbarui.
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-heading mr-1"></i> Kepala Surat (Kop Surat)</label>
                                <textarea id="summernote_letterhead" name="letterhead" class="form-control"
                                    rows="10"><?php echo htmlspecialchars($letterhead); ?></textarea>
                                <small class="text-muted">Gunakan editor di atas untuk mengatur logo dan identitas
                                    instansi.</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-id-card mr-1"></i> Layout Kartu Ujian</label>
                                <textarea id="summernote_layout" name="layout" class="form-control"
                                    rows="20"><?php echo htmlspecialchars($layout); ?></textarea>
                                <small class="text-muted">Gunakan tag variabel di bawah untuk memasukkan data dinamis ke
                                    dalam kartu.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info card-outline mt-3">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-code mr-1"></i> Referensi
                                Variabel (Variable Tags)</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped mb-0" style="font-size: 12.5px;">
                                <thead>
                                    <tr>
                                        <th width="25%">Tag</th>
                                        <th>Keterangan</th>
                                        <th width="35%">Contoh Output</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>[kop_surat]</code></td>
                                        <td>Input dari editor Kop Surat di atas</td>
                                        <td><i>(Tabel Kop Surat)</i></td>
                                    </tr>
                                    <tr>
                                        <td><code>[nomor_peserta]</code></td>
                                        <td>Nomor urut pendaftaran peserta</td>
                                        <td>20251121107</td>
                                    </tr>
                                    <tr>
                                        <td><code>[nama_peserta]</code></td>
                                        <td>Nama lengkap (Sesuai ijazah)</td>
                                        <td>AHMAD KHOIRUL UMAM</td>
                                    </tr>
                                    <tr>
                                        <td><code>[tgl_lahir]</code></td>
                                        <td>Tempat, Tanggal Lahir</td>
                                        <td>Banjarmasin, 15 Januari 2000</td>
                                    </tr>
                                    <tr>
                                        <td><code>[prodi]</code></td>
                                        <td>Program Studi Pilihan</td>
                                        <td>S2 Manajemen</td>
                                    </tr>
                                    <tr>
                                        <td><code>[tanggal_ujian]</code></td>
                                        <td>Hari/Tanggal Tes</td>
                                        <td>Senin, 10 Maret 2025</td>
                                    </tr>
                                    <tr>
                                        <td><code>[waktu_ujian]</code></td>
                                        <td>Sesi Jam Ujian</td>
                                        <td>08:00 - 10:00 WITA</td>
                                    </tr>
                                    <tr>
                                        <td><code>[ruang_ujian]</code></td>
                                        <td>Nama Ruangan</td>
                                        <td>LAB-01</td>
                                    </tr>
                                    <tr>
                                        <td><code>[gedung]</code></td>
                                        <td>Lokasi Gedung</td>
                                        <td>Gedung Pascasarjana Lt. 2</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><code>[foto_peserta]</code></td>
                                        <td><strong>Foto Peserta (3x4cm)</strong></td>
                                        <td><i class="fas fa-image"></i> <i>(Gambar 3x4 atau Placeholder)</i></td>
                                    </tr>
                                    <tr>
                                        <td><code>[barcode]</code></td>
                                        <td>QR/Barcode Image</td>
                                        <td><i class="fas fa-barcode"></i> <i>(Generated Image)</i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer py-2">
                            <small class="text-info"><i class="fas fa-info-circle mr-1"></i> <b>Tips:</b> Gunakan
                                styling CSS inline untuk hasil terbaik pada PDF rendering (Dompdf).</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary px-5 shadow">
                        <i class="fas fa-save mr-1"></i> Simpan Desain Kartu Ujian
                    </button>
                    <a href="/dummy-card" target="_blank" class="btn btn-outline-info ml-2 px-4 shadow-sm">
                        <i class="fas fa-eye mr-1"></i> Preview Contoh Kartu
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const summernoteConfig = {
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ]
        };

        // Initialize Summernote directly on textareas
        $('#summernote_letterhead').summernote(summernoteConfig);
        $('#summernote_layout').summernote($.extend({}, summernoteConfig, { height: 500 }));
    });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>