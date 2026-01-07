<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-sync-alt mr-1 text-primary"></i> Panduan Sinkronisasi Eksternal
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <h5><span class="badge badge-primary mr-2">1</span> Persiapan Awal</h5>
                        <p class="text-muted">Untuk mensinkronkan data berkas fisik dan kelulusan dari SIDA Pasca ke
                            system Admisi ULM, kita menggunakan bantuan browser extension <strong>Tampermonkey</strong>
                            atau <strong>Greasemonkey</strong>.</p>

                        <div class="alert alert-info shadow-sm border-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Metode ini memungkinkan kita memproses ribuan data secara otomatis seolah-olah kita
                            melakukan klik verifikasi satu per satu di server utama.
                        </div>

                        <h5 class="mt-4"><span class="badge badge-primary mr-2">2</span> Langkah-Langkah</h5>
                        <ol class="pl-3 mt-3">
                            <li class="mb-2">Install extension <strong>Tampermonkey</strong> di browser Anda (Chrome,
                                Edge, atau Firefox).</li>
                            <li class="mb-2">Klik icon Tampermonkey > <strong>Dashboard</strong> > Tab <strong>"+"
                                    (Create a new script)</strong>.</li>
                            <li class="mb-2">Hapus seluruh isi editor default, lalu <strong>Copy & Paste</strong> kode
                                yang ada di samping kanan halaman ini.</li>
                            <li class="mb-2">Klik menu <strong>File > Save</strong> (atau Ctrl+S).</li>
                            <li class="mb-2">Sekarang, buka halaman admin Admisi ULM di: <br>
                                <a href="https://admisipasca.ulm.ac.id/administrator/kartu" target="_blank"
                                    class="font-weight-bold">
                                    admisipasca.ulm.ac.id/administrator/kartu <i
                                        class="fas fa-external-link-alt fa-xs ml-1"></i>
                                </a>
                            </li>
                            <li class="mb-2">Anda akan melihat dua tombol baru: <span
                                    class="badge badge-warning text-dark">Sync Berkas</span> dan <span
                                    class="badge badge-success">Sync Kelulusan</span> di bagian atas tabel.</li>
                            <li>Klik tombol yang sesuai dan tunggu prosesnya hingga selesai.</li>
                        </ol>

                        <div class="callout callout-warning mt-4">
                            <h5><i class="fas fa-exclamation-triangle mr-1 text-warning"></i> Penting:</h5>
                            <ul class="pl-3 mb-0">
                                <li>Pastikan Anda sudah login di halaman Admisi ULM sebelum memulai sinkronisasi.</li>
                                <li>Jangan menutup tab browser selama proses transfer data berlangsung.</li>
                                <li>Data yang dikirimkan adalah data peserta yang di SIDA Pasca berstatus <strong>"Lulus
                                        Berkas"</strong> dan sudah memiliki <strong>Nomor Peserta</strong>.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="card shadow-none border bg-light">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-bold text-muted small uppercase">Script Tampermonkey
                                    (Copy-Paste)</h6>
                                <button class="btn btn-xs btn-outline-primary" onclick="copyScript()">
                                    <i class="fas fa-copy mr-1"></i> Copy Kode
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <pre id="scriptCode" class="mb-0 p-3 small"
                                    style="max-height: 500px; overflow-y: auto; background-color: #f8f9fa;"><code><?php echo htmlspecialchars(file_get_contents(__DIR__ . '/../../../../research/sync_script.user.js')); ?></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyScript() {
        const code = document.getElementById('scriptCode').innerText;
        navigator.clipboard.writeText(code).then(() => {
            toastr.success('Script berhasil disalin ke clipboard!');
        }).catch(err => {
            alert('Gagal menyalin script: ' + err);
        });
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>