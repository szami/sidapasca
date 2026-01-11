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
    return $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
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
    return $days[date('l', strtotime($dateStr))] ?? date('l', strtotime($dateStr));
}

$hasSchedule = !empty($participant['ruang_ujian']) && !empty($participant['tanggal_ujian']) && !empty($participant['waktu_ujian']);
// 4 Points Check: Nomor + Jadwal + Fisik + Setting
$isDownloadOpen = \App\Models\Setting::get('allow_exam_card_download', '0') == '1';
// Use $verification variable passed from Controller/Route (Source of Truth)
$isVerified = !empty($verification) && ($verification['status_verifikasi_fisik'] === 'lengkap' || !empty($verification['bypass_verification']));
$canDownload = !empty($participant['nomor_peserta']) && $hasSchedule && $isVerified && $isDownloadOpen;

// Photo URL
$photoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($participant['nama_lengkap']) . '&background=random';
if (!empty($participant['photo_filename'])) {
    $photoUrl = strpos($participant['photo_filename'], 'photos/') !== false ? '/storage/' . $participant['photo_filename'] : '/storage/photos/' . $participant['photo_filename'];
}

ob_start();
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'survey_completed'): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">
                        Terima kasih! Anda telah menyelesaikan kuisioner dan sekarang dapat mengakses Dashboard kembali.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 h-32"></div>
        <div class="px-8 pb-8 flex flex-col md:flex-row items-center md:items-end -mt-12 gap-6">
            <div class="h-32 w-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white flex-shrink-0">
                <img src="<?php echo $photoUrl; ?>" alt="Foto Peserta" class="h-full w-full object-cover">
            </div>
            <div class="flex-grow text-center md:text-left mb-2">
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($participant['nama_lengkap']); ?>
                </h1>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-2 text-gray-600">
                    <span
                        class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <?php echo htmlspecialchars($participant['nomor_peserta'] ?? 'Belum ada nomor'); ?>
                    </span>
                    <span class="flex items-center text-sm"><i class="fas fa-university mr-2 text-gray-400"></i>
                        <?php echo htmlspecialchars($participant['nama_prodi']); ?></span>
                    <span class="flex items-center text-sm"><i class="fas fa-envelope mr-2 text-gray-400"></i>
                        <?php echo htmlspecialchars($participant['email']); ?></span>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="openGuideModal()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                    <i class="fas fa-book mr-2"></i> Panduan
                </button>
                <a href="/logout"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 mb-8" x-data="{ activeTab: 'biodata' }">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'biodata'"
                :class="activeTab === 'biodata' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-user-circle mr-2"
                    :class="activeTab === 'biodata' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"></i>
                Biodata & Jadwal
            </button>
            <button @click="activeTab = 'berkas'"
                :class="activeTab === 'berkas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-folder-open mr-2"
                    :class="activeTab === 'berkas' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"></i>
                Berkas Fisik
            </button>
            <button @click="activeTab = 'berita'"
                :class="activeTab === 'berita' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-newspaper mr-2"
                    :class="activeTab === 'berita' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"></i>
                Berita & Informasi
            </button>
            <button @click="activeTab = 'hasil'"
                :class="activeTab === 'hasil' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm">
                <i class="fas fa-award mr-2"
                    :class="activeTab === 'hasil' ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500'"></i>
                Hasil Seleksi
            </button>
        </nav>

        <!-- Tab Contents -->
        <div class="py-6">

            <!-- Tab: Biodata -->
            <div x-show="activeTab === 'biodata'" class="space-y-6">
                <!-- Schedule & Downloads -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Schedule -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
                        <div class="flex items-center mb-4">
                            <div
                                class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3">
                                <i class="fas fa-calendar-alt text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Jadwal Ujian</h3>
                        </div>
                        <?php if ($hasSchedule): ?>
                            <div class="space-y-4 flex-grow">
                                <div class="flex justify-between border-b pb-2"><span
                                        class="text-gray-500">Hari/Tanggal</span> <span
                                        class="font-medium text-gray-900 text-right"><?php echo getDayName($participant['tanggal_ujian']) . ', ' . formatDateIndo($participant['tanggal_ujian']); ?></span>
                                </div>
                                <div class="flex justify-between border-b pb-2"><span class="text-gray-500">Waktu</span>
                                    <span class="font-medium text-gray-900 text-right">
                                        <?php
                                        $waktu = $participant['waktu_ujian'];
                                        // Remove hardcoded WITA if likely present, or just display as is?
                                        // User report: "10:00 - 11:30 WITA WITA" -> DB likely has "10:00 - 11:30 WITA".
                                        // Safe bet: Display raw, let admin control format. Or strict trim.
                                        echo str_replace(' WITA', '', $waktu) . ' WITA';
                                        ?>
                                        (<?php
                                        // Fix "Sesi Sesi": Remove 'Sesi ' prefix if exists in DB value, then re-add, or just display DB value?
                                        // User report: "Sesi Sesi 1". DB likely "Sesi 1".
                                        $sesi = $participant['sesi_ujian'];
                                        echo (stripos($sesi, 'Sesi') === false) ? 'Sesi ' . $sesi : $sesi;
                                        ?>)
                                    </span>
                                </div>
                                <div class="flex justify-between pt-2">
                                    <span class="text-gray-500">Lokasi</span>
                                    <span class="font-medium text-gray-900 text-right">
                                        <?php echo isset($examRoom['fakultas']) ? $examRoom['fakultas'] : 'Pascasarjana ULM'; ?><br>
                                        Ruang <?php echo $participant['ruang_ujian']; ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($examRoom['google_map_link'])): ?>
                                <div class="mt-3 text-right">
                                    <a href="<?php echo htmlspecialchars($examRoom['google_map_link']); ?>" target="_blank"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                        <i class="fas fa-map-marker-alt mr-1"></i> Lihat Lokasi (Google Maps)
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex-grow flex items-center justify-center text-center p-4">
                            <p class="text-gray-500">Jadwal ujian belum tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Downloads -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
                    <div class="flex items-center mb-4">
                        <div
                            class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600 mr-3">
                            <i class="fas fa-download text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Unduhan</h3>
                    </div>
                    <div class="space-y-3 flex-grow">
                        <a href="/participant/formulir" target="_blank"
                            class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-alt text-gray-400 mr-3"></i> <span
                                class="font-medium text-gray-700">Formulir Pendaftaran</span>
                        </a>
                        <?php if ($canDownload): ?>
                            <a href="/participant/exam-card" target="_blank"
                                class="flex items-center p-3 border-l-4 border-l-blue-500 border rounded-lg hover:bg-gray-50 transition-colors bg-blue-50 border-gray-100">
                                <i class="fas fa-id-card text-blue-500 mr-3"></i> <span
                                    class="font-bold text-blue-700">Kartu Tanda Peserta</span>
                            </a>
                        <?php else: ?>
                            <div
                                class="flex items-center p-3 border border-gray-200 rounded-lg bg-gray-50 opacity-60 cursor-not-allowed">
                                <i class="fas fa-lock text-gray-400 mr-3"></i> <span class="font-medium text-gray-500">Kartu
                                    Ujian Belum Tersedia</span>
                            </div>
                            <p class="text-xs text-red-500 mt-1">*Kartu dapat diunduh jika jadwal sudah diterbitkan dan
                                berkas fisik telah diserahkan ke Petugas Admisi Pascasarjana - Gedung Pascasarjana ULM
                                Banjarmasin.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Personal Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Data Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tempat, Tanggal Lahir</label>
                        <p class="mt-1 text-gray-900 font-medium">
                            <?php echo htmlspecialchars($participant['tempat_lahir']); ?>,
                            <?php echo formatDateIndo($participant['tgl_lahir']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Nomor HP / WhatsApp</label>
                        <p class="mt-1 text-gray-900 font-medium">
                            <?php echo htmlspecialchars($participant['no_hp']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Alamat</label>
                        <p class="mt-1 text-gray-900 font-medium">
                            <?php echo htmlspecialchars($participant['alamat_ktp']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Asal Perguruan Tinggi (S1)</label>
                        <p class="mt-1 text-gray-900 font-medium">
                            <?php echo htmlspecialchars($participant['s1_perguruan_tinggi']); ?>
                            (<?php echo htmlspecialchars($participant['s1_prodi']); ?>)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Berkas Fisik -->
        <div x-show="activeTab === 'berkas'" style="display: none;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <?php
                $statusFisik = !empty($verification) ? ($verification['status_verifikasi_fisik'] ?? 'pending') : 'pending';
                $icon = 'fa-hourglass-half';
                $color = 'text-yellow-500';
                $bg = 'bg-yellow-50';
                $label = 'Belum Diverifikasi';
                $subtext = 'Berkas fisik Anda belum diserahkan atau masih dalam antrean verifikasi petugas.';

                if ($statusFisik === 'lengkap') {
                    $icon = 'fa-check-circle';
                    $color = 'text-green-500';
                    $bg = 'bg-green-50';
                    $label = 'Lengkap / Terverifikasi';
                    $subtext = 'Semua berkas fisik Anda telah diverifikasi dan dinyatakan lengkap.';
                } elseif ($statusFisik === 'tidak_lengkap') {
                    $icon = 'fa-times-circle';
                    $color = 'text-red-500';
                    $bg = 'bg-red-50';
                    $label = 'Tidak Lengkap / Perlu Perbaikan';
                    $subtext = 'Terdapat berkas yang belum lengkap atau perlu diperbaiki. Silakan cek catatan di bawah.';
                }
                ?>

                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full <?php echo $bg; ?> mb-6">
                    <i class="fas <?php echo $icon; ?> <?php echo $color; ?> text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo $label; ?></h2>
                <p class="text-gray-500 max-w-lg mx-auto">
                    <?php echo $subtext; ?>
                </p>

                <?php if (!empty($verification) && !empty($verification['catatan_admin'])): ?>
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200 max-w-2xl mx-auto text-left">
                        <h4 class="font-bold text-gray-800 mb-1"><i
                                class="fas fa-sticky-note mr-2 text-gray-400"></i>Catatan Verifikator:</h4>
                        <p class="text-gray-700">
                            <?php echo nl2br(htmlspecialchars($verification['catatan_admin'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Berita -->
        <div x-show="activeTab === 'berita'" style="display: none;">
            <div id="news-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loaded via AJAX -->
                <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Memuat Berita...
                </div>
            </div>
        </div>

        <!-- Tab: Hasil Seleksi -->
        <div x-show="activeTab === 'hasil'" style="display: none;">
            <?php
            $hasil = $participant['hasil_seleksi'] ?? 'belum_ada';
            if ($hasil == 'belum_ada'):
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-gray-100 mb-6">
                        <i class="fas fa-clock text-gray-400 text-3xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Pengumuman</h2>
                    <p class="text-gray-500">Hasil seleksi belum dimumumkan. Silakan cek secara berkala.</p>
                </div>
            <?php else:
                $hColor = 'text-gray-800';
                $hBg = 'bg-gray-100';
                $hIcon = 'fa-info-circle';
                $hText = 'Belum Ada';

                if ($hasil == 'lulus') {
                    $hColor = 'text-green-600';
                    $hBg = 'bg-green-50';
                    $hIcon = 'fa-graduation-cap';
                    $hText = 'LULUS SELEKSI';
                } elseif ($hasil == 'tidak_lulus') {
                    $hColor = 'text-red-600';
                    $hBg = 'bg-red-50';
                    $hIcon = 'fa-times-circle';
                    $hText = 'TIDAK LULUS';
                } elseif ($hasil == 'cadangan') {
                    $hColor = 'text-yellow-600';
                    $hBg = 'bg-yellow-50';
                    $hIcon = 'fa-exclamation-circle';
                    $hText = 'CADANGAN';
                }
                ?>
                <div
                    class="bg-white rounded-xl shadow-lg border-2 <?php echo ($hasil == 'lulus' ? 'border-green-500' : ($hasil == 'tidak_lulus' ? 'border-red-500' : 'border-yellow-500')); ?> overflow-hidden">
                    <div
                        class="<?php echo $hBg; ?> p-8 text-center border-b <?php echo ($hasil == 'lulus' ? 'border-green-100' : ($hasil == 'tidak_lulus' ? 'border-red-100' : 'border-yellow-100')); ?>">
                        <i class="fas <?php echo $hIcon; ?> <?php echo $hColor; ?> text-6xl mb-4"></i>
                        <h2 class="text-3xl font-extrabold <?php echo $hColor; ?> tracking-tight"><?php echo $hText; ?>
                        </h2>
                        <p
                            class="mt-2 text-<?php echo ($hasil == 'lulus' ? 'green' : ($hasil == 'tidak_lulus' ? 'red' : 'yellow')); ?>-800 font-medium">
                            Program Studi <?php echo htmlspecialchars($participant['nama_prodi']); ?>
                        </p>
                    </div>
                    <div class="p-8">
                        <?php if (!empty($participant['hasil_seleksi_date'])): ?>
                            <p class="text-center text-gray-500 text-sm mb-6">Ditetapkan pada:
                                <?php echo formatDateIndo($participant['hasil_seleksi_date']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($participant['hasil_seleksi_note'])): ?>
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h4 class="font-bold text-gray-900 mb-2">Keterangan / Keputusan:</h4>
                                <p class="text-gray-700 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($participant['hasil_seleksi_note'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasil == 'lulus'): ?>
                            <div class="mt-8 text-center">
                                <p class="mb-4 text-gray-600">Silakan lakukan daftar ulang sesuai instruksi di bawah ini.
                                </p>
                                <button onclick="openGuideModal()"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-book mr-2"></i> Lihat Panduan Daftar Ulang
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</div>

<!-- News Detail Modal -->
<div id="news-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeNewsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-news-title">Judul Berita</h3>
                <p class="text-sm text-gray-500 mt-1 mb-4" id="modal-news-date">Tanggal</p>
                <div class="mt-2 prose prose-sm max-w-none text-gray-700" id="modal-news-content">
                    <!-- Content -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    onclick="closeNewsModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Guide Modal -->
<div id="guide-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeGuideModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4"><i
                        class="fas fa-book mr-2 text-blue-500"></i> Panduan Peserta</h3>
                <div id="guide-content" class="space-y-4">
                    <p class="text-center text-gray-500">Memuat panduan...</p>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    onclick="closeGuideModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<script>
    // Helper to decode HTML entities
    function decodeHtml(html) {
        if (!html) return '';
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    // Load News
    const defaultNewsImage = 'https://images.unsplash.com/photo-1546422904-90eab23c3d7e?auto=format&fit=crop&w=800&q=80'; // News/Journalism background

    fetch('/api/news/published')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('news-container');
            if (data.length === 0) {
                container.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">Belum ada berita terbaru.</div>';
                return;
            }

            container.innerHTML = data.map(news => {
                const decodedContent = decodeHtml(news.content || '');
                const plainText = decodedContent.replace(/<[^>]*>?/gm, '');
                const imageUrl = news.image_url || defaultNewsImage;

                return `
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow cursor-pointer flex flex-col h-full" onclick="openNewsModal(${news.id})">
                    <div class="h-48 bg-gray-200 relative overflow-hidden flex-shrink-0">
                        <img src="${imageUrl}" class="w-full h-full object-cover">
                        <div class="absolute top-0 right-0 m-3 px-2 py-1 bg-white bg-opacity-90 rounded-md text-xs font-bold text-gray-800 uppercase tracking-wide shadow-sm">
                            ${news.category}
                        </div>
                    </div>
                    <div class="p-5 flex flex-col flex-grow">
                        <div class="text-xs text-gray-500 mb-2"><i class="far fa-clock mr-1"></i> ${new Date(news.published_at).toLocaleDateString('id-ID')}</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">${news.title}</h3>
                        <p class="text-sm text-gray-600 line-clamp-3 mb-4">${plainText}</p>
                        <div class="mt-auto flex items-center text-blue-600 text-sm font-medium">
                            Baca Selengkapnya <i class="fas fa-arrow-right ml-1"></i>
                        </div>
                    </div>
                </div>
            `;
            }).join('');
        });

    function openNewsModal(id) {
        fetch(`/api/news/get/${id}`)
            .then(res => res.json())
            .then(news => {
                document.getElementById('modal-news-title').innerText = news.title;
                document.getElementById('modal-news-date').innerText = new Date(news.published_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                const imageUrl = news.image_url;
                let contentHtml = '';
                if (imageUrl) {
                    contentHtml += `<img src="${imageUrl}" class="w-full rounded-lg mb-6 shadow-sm max-h-96 object-cover">`;
                }

                // Decode twice if needed, or just handle once. Usually once is enough if saved correctly.
                contentHtml += decodeHtml(news.content || '');

                document.getElementById('modal-news-content').innerHTML = contentHtml;
                document.getElementById('news-modal').classList.remove('hidden');
            });
    }

    function closeNewsModal() {
        document.getElementById('news-modal').classList.add('hidden');
    }

    // Load Guides
    function loadGuides() {
        fetch('/api/guides/role/participant') // Or determine role dynamically if needed
            .then(res => res.json())
            .then(guides => {
                const container = document.getElementById('guide-content');
                if (guides.length === 0) {
                    container.innerHTML = '<p class="text-center text-gray-500">Tidak ada panduan tersedia saat ini.</p>';
                    return;
                }
                container.innerHTML = guides.map((guide, index) => `
                    <div class="border rounded-lg overflow-hidden">
                        <button class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center text-left focus:outline-none" onclick="toggleAccordion('guide-${index}')">
                            <span class="font-medium text-gray-900">${guide.title}</span>
                            <i class="fas fa-chevron-down text-gray-400 transform transition-transform" id="icon-guide-${index}"></i>
                        </button>
                        <div id="guide-${index}" class="hidden bg-white p-4 prose prose-sm max-w-none">
                            ${decodeHtml(guide.content)}
                        </div>
                    </div>
                `).join('');
            });
    }

    function toggleAccordion(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            el.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    function openGuideModal() {
        loadGuides();
        document.getElementById('guide-modal').classList.remove('hidden');
    }

    function closeGuideModal() {
        document.getElementById('guide-modal').classList.add('hidden');
    }
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/participant.php';
?>