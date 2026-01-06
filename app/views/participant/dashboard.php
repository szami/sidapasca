<?php
// Helper to format date in Indonesian
function formatDateIndo($dateStr)
{
    if (empty($dateStr))
        return '-';
    $months = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $date = new DateTime($dateStr);
    $day = $date->format('d');
    $month = $months[(int) $date->format('n')];
    $year = $date->format('Y');
    return "$day $month $year";
}

function getDayName($dateStr)
{
    if (empty($dateStr))
        return '-';
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $dayEng = date('l', strtotime($dateStr));
    return $days[$dayEng] ?? $dayEng;
}

$hasSchedule = !empty($participant['ruang_ujian']) && !empty($participant['tanggal_ujian']) && !empty($participant['waktu_ujian']);
$canDownload = $participant['status_berkas'] == 'lulus'
    && $participant['status_pembayaran'] == 1
    && !empty($participant['nomor_peserta'])
    && $hasSchedule
    && ($participant['status_verifikasi_fisik'] == 'lengkap');

// Photo URL
$photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($participant['nama_lengkap']) . '&background=random';
if (!empty($participant['photo_filename'])) {
    if (strpos($participant['photo_filename'], 'photos/') !== false) {
        $photoUrl = '/storage/' . $participant['photo_filename'];
    } else {
        $photoUrl = '/storage/photos/' . $participant['photo_filename'];
    }
}

ob_start();
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Peserta</h1>
            <p class="text-gray-500 mt-1">Selamat datang, <?php echo htmlspecialchars($participant['nama_lengkap']); ?>
            </p>
        </div>
        <a href="/logout"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-8">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 h-32"></div>
                <div class="px-6 pb-6 relative">
                    <div class="relative -mt-16 mb-4 flex justify-center">
                        <div class="h-32 w-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white">
                            <img src="<?php echo $photoUrl; ?>" alt="Foto Peserta" class="h-full w-full object-cover">
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900">
                            <?php echo htmlspecialchars($participant['nama_lengkap']); ?>
                        </h2>
                        <span
                            class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mt-2">
                            <?php echo htmlspecialchars($participant['nomor_peserta'] ?? 'Belum ada nomor'); ?>
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-university text-gray-400 w-5"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Program Studi</p>
                                <p class="text-sm text-gray-900">
                                    <?php echo htmlspecialchars($participant['nama_prodi']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-envelope text-gray-400 w-5"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="text-sm text-gray-900 break-all">
                                    <?php echo htmlspecialchars($participant['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Biodata & Schedule & Downloads -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Biodata Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center mb-6">
                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 mr-4">
                        <i class="fas fa-user-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Data Peserta</h3>
                        <p class="text-sm text-gray-500">Informasi diri dan akademik Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Peserta</label>
                        <p
                            class="text-base font-semibold text-gray-900 tracking-wide font-mono bg-gray-50 inline-block px-2 py-0.5 rounded border border-gray-200">
                            <?php echo htmlspecialchars($participant['nomor_peserta'] ?? '-'); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</label>
                        <p class="text-base font-semibold text-gray-900">
                            <?php echo htmlspecialchars($participant['nama_lengkap']); ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tempat, Tanggal Lahir</label>
                        <p class="text-base font-medium text-gray-900">
                            <?php
                            $ttl = $participant['tempat_lahir'] ?? '';
                            if (!empty($participant['tgl_lahir'])) {
                                $ttl .= ($ttl ? ', ' : '') . formatDateIndo($participant['tgl_lahir']);
                            }
                            echo $ttl ?: '-';
                            ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Program Studi</label>
                        <p class="text-base font-medium text-gray-900">
                            <?php echo htmlspecialchars($participant['nama_prodi']); ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-base font-medium text-gray-900">
                            <?php echo htmlspecialchars($participant['email']); ?>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">No. Handphone</label>
                        <p class="text-base font-medium text-gray-900">
                            <?php echo htmlspecialchars($participant['no_hp'] ?? '-'); ?>
                        </p>
                    </div>


                </div>
            </div>

            <!-- Schedule Card (Only if Scheduled) -->
            <?php if ($hasSchedule): ?>
                <div class="bg-white rounded-2xl shadow-lg border border-indigo-100 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center">
                        <i class="fas fa-calendar-alt text-indigo-600 text-xl mr-3"></i>
                        <h3 class="text-lg font-bold text-gray-900">Jadwal Ujian TPA</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Hari, Tanggal</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo getDayName($participant['tanggal_ujian']); ?>,
                                    <?php echo formatDateIndo($participant['tanggal_ujian']); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Waktu</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo $participant['waktu_ujian']; ?> WITA
                                    <span class="text-sm font-normal text-gray-500">(Sesi
                                        <?php echo $participant['sesi_ujian']; ?>)</span>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500 mb-1">Lokasi Ujian</p>
                                <div class="flex items-start mt-1">
                                    <i class="fas fa-map-marker-alt text-red-500 mt-1 mr-2"></i>
                                    <div>
                                        <p class="font-bold text-gray-900 text-lg">
                                            <?php echo isset($examRoom['fakultas']) ? $examRoom['fakultas'] : 'Gedung Pascasarjana ULM'; ?>
                                        </p>
                                        <p class="text-gray-700">
                                            Ruang: <span
                                                class="font-semibold"><?php echo $participant['ruang_ujian']; ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action/Downloads Buttons -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center mb-6">
                    <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600 mr-4">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Unduhan</h3>
                        <p class="text-sm text-gray-500">Berkas yang dapat Anda unduh</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="/participant/formulir" target="_blank"
                        class="flex items-center justify-center px-6 py-4 border border-gray-300 rounded-xl shadow-sm bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all group">
                        <div class="bg-gray-100 p-2 rounded-lg group-hover:bg-gray-200 transition-colors mr-3">
                            <i class="fas fa-file-alt text-xl text-gray-600"></i>
                        </div>
                        <span class="font-semibold">Formulir Pendaftaran</span>
                    </a>

                    <?php if ($canDownload): ?>
                        <a href="/participant/exam-card" target="_blank"
                            class="flex items-center justify-center px-6 py-4 border border-transparent rounded-xl shadow-md bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg transition-all group">
                            <div class="bg-blue-500 bg-opacity-30 p-2 rounded-lg mr-3">
                                <i class="fas fa-print text-xl text-white"></i>
                            </div>
                            <span class="font-bold">Kartu Tanda Peserta</span>
                        </a>
                    <?php else: ?>
                        <div
                            class="flex items-center justify-center px-6 py-4 border border-gray-200 rounded-xl bg-gray-50 text-gray-400 cursor-not-allowed opacity-75">
                            <div class="bg-gray-200 p-2 rounded-lg mr-3">
                                <i class="fas fa-lock text-xl text-gray-400"></i>
                            </div>
                            <span class="font-semibold">Kartu Ujian Belum Tersedia</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$canDownload): ?>
                    <div class="mt-4 bg-blue-50 text-blue-800 text-sm p-4 rounded-lg flex items-start">
                        <i class="fas fa-info-circle mt-0.5 mr-2 flex-shrink-0"></i>
                        <p>
                            <?php if (!$hasSchedule && $participant['status_berkas'] == 'lulus'): ?>
                                Kartu ujian dapat didownload setelah <strong>Jadwal & Ruang Ujian</strong> ditentukan oleh
                                Admin.
                            <?php else: ?>
                                Kartu ujian dapat didownload jika berkas sudah diverifikasi (Lulus Verifikasi Fisik) dan
                                pembayaran lunas.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>