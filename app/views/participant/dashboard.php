<?php ob_start(); ?>
<div class="bg-white rounded-lg shadow-xl overflow-hidden p-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Peserta</h2>
        <a href="/logout" class="text-red-600 hover:text-red-800 font-medium">Logout</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Biodata -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Biodata Anda</h3>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium w-32 inline-block">Nomor Peserta:</span>
                    <?php echo $participant['nomor_peserta'] ?? '-'; ?>
                </p>
                <p><span class="font-medium w-32 inline-block">Nama:</span>
                    <?php echo $participant['nama_lengkap']; ?>
                </p>
                <p><span class="font-medium w-32 inline-block">Prodi Pilihan:</span>
                    <?php echo $participant['nama_prodi']; ?>
                </p>
                <p><span class="font-medium w-32 inline-block">Email:</span>
                    <?php echo $participant['email']; ?>
                </p>
            </div>
        </div>

        <!-- Status -->
        <div>
            <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Status Seleksi</h3>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">Status Berkas:</p>
                <?php if ($participant['status_berkas'] == 'lulus'): ?>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Lulus
                        Administrasi</span>
                <?php elseif ($participant['status_berkas'] == 'gagal'): ?>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">Gagal / Belum
                        Lulus</span>
                <?php else: ?>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">Menunggu
                        Verifikasi</span>
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-1">Status Pembayaran / TPA:</p>
                <?php if ($participant['status_pembayaran']): ?>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Sudah Bayar /
                        Terdaftar TPA</span>
                <?php else: ?>
                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">Belum
                        Terverifikasi</span>
                <?php endif; ?>
            </div>

            <!-- Action -->
            <?php
            $hasSchedule = !empty($participant['ruang_ujian']) && !empty($participant['tanggal_ujian']) && !empty($participant['waktu_ujian']);
            $canDownload = $participant['status_berkas'] == 'lulus' && $participant['status_pembayaran'] == 1 && !empty($participant['nomor_peserta']) && $hasSchedule;
            ?>

            <!-- Formulir always available if logged in? Or only if paid? Usually after registration. -->
            <a href="/participant/formulir" target="_blank"
                class="bg-gray-600 text-white px-6 py-3 rounded-lg shadow hover:bg-gray-700 transition block text-center font-bold mb-3">
                <i class="fas fa-file-alt mr-2"></i> Download Formulir Pendaftaran
            </a>

            <?php if ($canDownload): ?>
                <a href="/participant/exam-card" target="_blank"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition block text-center font-bold">
                    <i class="fas fa-print mr-2"></i> Download Kartu Ujian
                </a>
            <?php else: ?>
                <div class="bg-gray-300 text-gray-600 px-6 py-3 rounded-lg text-center font-bold cursor-not-allowed">
                    Kartu Ujian Belum Tersedia
                </div>
                <?php if (!$hasSchedule && $participant['status_berkas'] == 'lulus'): ?>
                    <p class="text-sm text-gray-500 text-center mt-2">
                        Kartu ujian dapat didownload setelah <strong>Jadwal & Ruang Ujian</strong> ditentukan oleh Admin.
                    </p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 text-center mt-2">
                        Kartu ujian bisa didownload kalau ruang ujian dan waktu ujian TPA sudah ditentukan untuk peserta
                        tersebut.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>