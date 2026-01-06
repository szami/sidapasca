<?php

namespace App\Controllers;

use Leaf\Blade;
use Dompdf\Dompdf;
use App\Models\Participant;

class ExamCardController
{
    public function download()
    {
        // Auth Check
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        $id = $_SESSION['user'];
        $db = \App\Utils\Database::connection();
        $query = "SELECT p.*, r.fakultas as gedung 
                  FROM participants p 
                  LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang 
                  WHERE p.id = ?";
        $participant = $db->query($query)->bind($id)->first();

        if (!$participant) {
            header('Location: /');
            exit;
        }

        // Validate Status (Only 'Lulus' and 'Paid')
        // Agents.md: "Hanya aktif jika status berkas = 'Lulus' DAN ada di data 'Peserta Ujian TPA'"
        // My logic for importer sets status_pembayaran=1 if in TPA file.
        // So check both.
        if ($participant['status_berkas'] !== 'lulus' || !$participant['status_pembayaran']) {
            echo "Maaf, Kartu Ujian belum tersedia. Pastikan Anda Lulus Administrasi dan sudah melakukan Pembayaran/Verifikasi TPA.";
            return;
        }

        // Check Admin Setting
        $allowDownload = \App\Models\Setting::get('allow_exam_card_download', '0');
        if ($allowDownload !== '1') {
            echo "Maaf, Download Kartu Ujian belum dibuka oleh Panitia.";
            return;
        }

        // NEW: Check Physical Verification Status
        // Uses DocumentVerification::canDownloadCard($id)
        // Note: Default to ALLOW if no verification record exists YET, assuming they need to come to campus to verify?
        // Wait, user requirement: "kalau verifikasi akhir belum lengkap, peserta tidak bisa download kartu"
        // This implies strict checking.
        $canDownload = \App\Models\DocumentVerification::canDownloadCard($participant['id']);
        if (!$canDownload) {
            echo "Maaf, Anda belum bisa mengunduh Kartu Ujian karena verifikasi berkas fisik belum lengkap. Silakan hubungi admin untuk verifikasi berkas.";
            return;
        }

        // Check if Schedule is determined
        if (empty($participant['ruang_ujian']) || empty($participant['tanggal_ujian']) || empty($participant['waktu_ujian'])) {
            echo "Maaf, Jadwal Ujian Anda belum ditentukan. Silahkan tunggu informasi selanjutnya.";
            return;
        }

        if (empty($participant['nomor_peserta'])) {
            echo "Maaf, Anda belum memiliki Nomor Peserta. Mohon tunggu verifikasi oleh panitia.";
            return;
        }

        // Fetch Template
        $layout = \App\Models\Setting::get('exam_card_layout', '');
        if (empty($layout)) {
            echo \App\Utils\View::render('pdf.exam_card', ['participant' => $participant]);
            return;
        }

        $html = $this->parseTemplate($layout, $participant);

        // Render View to HTML directly
        echo \App\Utils\View::render('pdf.exam_card', ['content' => $html]);
    }
    public function dummy()
    {
        $participant = [
            'id' => 999,
            'nomor_peserta' => '20252121001',
            'nama_lengkap' => 'CONTOH PESERTA DUMMY',
            'nama_prodi' => 'S2- MAGISTER ILMU KOMPUTER',
            'jalur_masuk' => 'REGULER',
            'tempat_lahir' => 'BANJARMASIN',
            'tgl_lahir' => '1995-08-17',
            'ruang_ujian' => 'Gedung Pascasarjana Lt. 3 R. 305',
            'tanggal_ujian' => '2026-01-15',
            'waktu_ujian' => '08:00 - 10:00 WITA',
            'sesi_ujian' => 'Sesi 1',
            // Dummy Biodata
            'alamat_ktp' => 'Jl. Kebun Bunga No. 12',
            'kecamatan' => 'Banjarmasin Tengah',
            'kota' => 'Banjarmasin',
            'provinsi' => 'Kalimantan Selatan',
            'no_hp' => '081234567890',
            's1_ipk' => '3.85',
            's1_perguruan_tinggi' => 'Universitas Lambung Mangkurat'
        ];

        // Fetch Template
        $layout = \App\Models\Setting::get('exam_card_layout', '');
        if (empty($layout)) {
            echo \App\Utils\View::render('pdf.exam_card', ['participant' => $participant]);
            return;
        }

        $html = $this->parseTemplate($layout, $participant);

        // Generate HTML
        echo \App\Utils\View::render('pdf.exam_card', ['content' => $html]);
    }

    public function downloadFormulir()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        $id = $_SESSION['user'];
        $participant = Participant::find($id);

        if (!$participant) {
            header('Location: /');
            exit;
        }



        if (empty($participant['nomor_peserta'])) {
            echo "Maaf, Anda belum memiliki Nomor Peserta. Silahkan hubungi panitia.";
            return;
        }

        // Render View to HTML directly
        echo \App\Utils\View::render('pdf.registration_form', ['p' => $participant]);
    }

    public function adminViewCard($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = \App\Utils\Database::connection();
        $query = "SELECT p.*, r.fakultas as gedung 
                  FROM participants p 
                  LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang 
                  WHERE p.id = ?";
        $participant = $db->query($query)->bind($id)->first();

        if (!$participant) {
            echo "Data peserta tidak ditemukan.";
            return;
        }

        if (empty($participant['nomor_peserta'])) {
            echo "Peserta ini belum memiliki Nomor Peserta.";
            return;
        }

        // Fetch Template
        $layout = \App\Models\Setting::get('exam_card_layout', '');
        if (empty($layout)) {
            echo \App\Utils\View::render('pdf.exam_card', ['participant' => $participant]);
            return;
        }

        $html = $this->parseTemplate($layout, $participant);

        echo \App\Utils\View::render('pdf.exam_card', ['content' => $html]);
    }

    public function adminViewForm($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $participant = Participant::find($id);

        if (!$participant) {
            echo "Data peserta tidak ditemukan.";
            return;
        }



        echo \App\Utils\View::render('pdf.registration_form', ['p' => $participant]);
    }

    public function attendanceFilter()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        // Get active semester
        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = (int) ($activeSemester['id'] ?? null);

        if (!$semesterId) {
            echo "Tidak ada semester aktif.";
            return;
        }

        $db = \App\Utils\Database::connection();

        // Get statistics
        $statsQuery = "SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL THEN 1 ELSE 0 END) as eligible,
                       SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL THEN 1 ELSE 0 END) as scheduled
                       FROM participants WHERE semester_id = '$semesterId'";
        $stats = $db->query($statsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $stats = $stats[0] ?? ['total' => 0, 'eligible' => 0, 'scheduled' => 0];

        // Get sessions and rooms for dropdowns
        $sessionsQuery = "SELECT DISTINCT sesi_ujian FROM participants 
                         WHERE semester_id = '$semesterId' AND sesi_ujian IS NOT NULL 
                         ORDER BY sesi_ujian ASC";
        $sessionsResult = $db->query($sessionsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $sessions = array_column($sessionsResult, 'sesi_ujian');

        $roomsQuery = "SELECT DISTINCT ruang_ujian FROM participants 
                      WHERE semester_id = '$semesterId' AND ruang_ujian IS NOT NULL 
                      ORDER BY ruang_ujian ASC";
        $roomsResult = $db->query($roomsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $rooms = array_column($roomsResult, 'ruang_ujian');

        // Get letterhead template from settings
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        echo \App\Utils\View::render('admin.attendance_filter', [
            'semesterName' => $activeSemester['nama'],
            'sessions' => $sessions,
            'rooms' => $rooms,
            'stats' => $stats,
            'letterhead' => $letterhead
        ]);
    }

    public function catScheduleFilter()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        // Get active semester
        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = (int) ($activeSemester['id'] ?? null);

        if (!$semesterId) {
            echo "Tidak ada semester aktif.";
            return;
        }

        $db = \App\Utils\Database::connection();

        // Get statistics
        $statsQuery = "SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL THEN 1 ELSE 0 END) as eligible,
                       SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL THEN 1 ELSE 0 END) as scheduled
                       FROM participants WHERE semester_id = '$semesterId'";
        $stats = $db->query($statsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $stats = $stats[0] ?? ['total' => 0, 'eligible' => 0, 'scheduled' => 0];

        // Get sessions and rooms for dropdowns
        $sessionsQuery = "SELECT DISTINCT sesi_ujian FROM participants 
                         WHERE semester_id = '$semesterId' AND sesi_ujian IS NOT NULL 
                         ORDER BY sesi_ujian ASC";
        $sessionsResult = $db->query($sessionsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $sessions = array_column($sessionsResult, 'sesi_ujian');

        $roomsQuery = "SELECT DISTINCT ruang_ujian FROM participants 
                      WHERE semester_id = '$semesterId' AND ruang_ujian IS NOT NULL 
                      ORDER BY ruang_ujian ASC";
        $roomsResult = $db->query($roomsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $rooms = array_column($roomsResult, 'ruang_ujian');

        echo \App\Utils\View::render('pdf.cat_schedule_filter', [
            'semesterName' => $activeSemester['nama'],
            'sessions' => $sessions,
            'rooms' => $rooms,
            'stats' => $stats
        ]);
    }

    public function catSchedulePrint()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        // Get active semester
        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = (int) ($activeSemester['id'] ?? null);

        if (!$semesterId) {
            echo "Tidak ada semester aktif.";
            return;
        }

        // Get filter parameters
        $filterSesi = request()->get('sesi') ?? 'all';
        $filterRuang = request()->get('ruang') ?? 'all';

        // Get all participants for active semester with status lulus and paid
        $db = \App\Utils\Database::connection();

        // Base query - MUST have schedule set (ruang_ujian, sesi_ujian, tanggal_ujian NOT NULL)
        // Join with exam_rooms to get 'fakultas' as Building (Gedung)
        $query = "SELECT p.id, p.nomor_peserta, p.nama_lengkap, p.nama_prodi, p.ruang_ujian, p.sesi_ujian, p.tanggal_ujian, p.waktu_ujian, r.fakultas as gedung
                  FROM participants p
                  LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang
                  WHERE p.semester_id = '$semesterId'
                  AND p.status_berkas = 'lulus'
                  AND p.status_pembayaran = 1
                  AND p.nomor_peserta IS NOT NULL
                  AND p.ruang_ujian IS NOT NULL
                  AND p.sesi_ujian IS NOT NULL
                  AND p.tanggal_ujian IS NOT NULL";

        // Add filters if not 'all'
        if ($filterSesi !== 'all') {
            $filterSesiEscaped = addslashes($filterSesi);
            $query .= " AND p.sesi_ujian = '$filterSesiEscaped'";
        }

        if ($filterRuang !== 'all') {
            $filterRuangEscaped = addslashes($filterRuang);
            $query .= " AND p.ruang_ujian = '$filterRuangEscaped'";
        }

        $query .= " ORDER BY p.ruang_ujian ASC, p.sesi_ujian ASC, p.nama_lengkap ASC";

        $participants = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        // Format dates for Indonesian display
        foreach ($participants as &$p) {
            $p['tanggal_formatted'] = $this->formatDateID($p['tanggal_ujian']);
        }
        unset($p); // Break reference

        // Check if empty
        if (empty($participants)) {
            echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color: #dc3545; margin-top: 0;'>❌ Belum Ada Jadwal CAT</h2>";
            echo "<p style='font-size: 16px;'>Belum ada peserta dengan jadwal yang lengkap untuk filter ini.</p>";
            echo "<div style='margin-top: 30px; text-align: center;'>";
            echo "<button onclick='window.close()' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;'>Tutup Tab Ini</button>";
            echo "</div>";
            echo "</div>";
            return;
        }

        // Get sessions and rooms for filter form
        $sessionsQuery = "SELECT DISTINCT sesi_ujian FROM participants 
                         WHERE semester_id = '$semesterId' AND sesi_ujian IS NOT NULL 
                         ORDER BY sesi_ujian ASC";
        $sessionsResult = $db->query($sessionsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $sessions = array_column($sessionsResult, 'sesi_ujian');

        $roomsQuery = "SELECT DISTINCT ruang_ujian FROM participants 
                      WHERE semester_id = '$semesterId' AND ruang_ujian IS NOT NULL 
                      ORDER BY ruang_ujian ASC";
        $roomsResult = $db->query($roomsQuery)->fetchAll(\PDO::FETCH_ASSOC);
        $rooms = array_column($roomsResult, 'ruang_ujian');

        // Get letterhead template from settings
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        // Render print view
        echo \App\Utils\View::render('pdf.cat_schedule', [
            'participants' => $participants,
            'semesterName' => $activeSemester['nama'],
            'filterSesi' => $filterSesi,
            'filterRuang' => $filterRuang,
            'sessions' => $sessions,
            'rooms' => $rooms,
            'letterhead' => $letterhead
        ]);
    }

    public function attendancePrint()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        // Get active semester
        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = (int) ($activeSemester['id'] ?? null);

        if (!$semesterId) {
            echo "Tidak ada semester aktif.";
            return;
        }

        // Get filter parameters
        $filterSesi = request()->get('sesi') ?? 'all';
        $filterRuang = request()->get('ruang') ?? 'all';

        // Get all participants for active semester with status lulus and paid
        $db = \App\Utils\Database::connection();

        // Base query - MUST have schedule set (ruang_ujian, sesi_ujian, tanggal_ujian NOT NULL)
        // Join with exam_rooms to get 'fakultas' as Building (Gedung)
        $query = "SELECT p.id, p.nomor_peserta, p.nama_lengkap, p.nama_prodi, p.ruang_ujian, p.sesi_ujian, p.tanggal_ujian, p.waktu_ujian, r.fakultas as gedung
                  FROM participants p
                  LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang
                  WHERE p.semester_id = '$semesterId'
                  AND p.status_berkas = 'lulus'
                  AND p.status_pembayaran = 1
                  AND p.nomor_peserta IS NOT NULL
                  AND p.ruang_ujian IS NOT NULL
                  AND p.sesi_ujian IS NOT NULL
                  AND p.tanggal_ujian IS NOT NULL";

        // Add filters if not 'all'
        if ($filterSesi !== 'all') {
            $filterSesiEscaped = addslashes($filterSesi);
            $query .= " AND p.sesi_ujian = '$filterSesiEscaped'";
        }

        if ($filterRuang !== 'all') {
            $filterRuangEscaped = addslashes($filterRuang);
            $query .= " AND p.ruang_ujian = '$filterRuangEscaped'";
        }

        $query .= " ORDER BY p.ruang_ujian ASC, p.sesi_ujian ASC, p.nama_lengkap ASC";

        $participants = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        // Check if empty - provide helpful message with detailed breakdown
        if (empty($participants)) {
            // ... (stats query and error message logic remains same)
            $statsQuery = "SELECT
                           COUNT(*) as total,
                           SUM(CASE WHEN status_berkas = 'lulus' THEN 1 ELSE 0 END) as lulus,
                           SUM(CASE WHEN status_pembayaran = 1 THEN 1 ELSE 0 END) as paid,
                           SUM(CASE WHEN nomor_peserta IS NOT NULL THEN 1 ELSE 0 END) as has_number,
                           SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 THEN 1 ELSE 0 END) as lulus_paid,
                           SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL THEN 1 ELSE 0 END) as eligible,
                           SUM(CASE WHEN status_berkas = 'lulus' AND status_pembayaran = 1 AND nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL THEN 1 ELSE 0 END) as scheduled
                           FROM participants WHERE semester_id = '$semesterId'";
            $stats = $db->query($statsQuery)->fetchAll(\PDO::FETCH_ASSOC);
            $s = $stats[0] ?? [];

            echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color: #dc3545; margin-top: 0;'>❌ Belum Ada Daftar Hadir</h2>";
            echo "<p style='font-size: 16px;'>Belum ada peserta dengan jadwal yang lengkap untuk filter ini.</p>";

            echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3 style='margin-top: 0; color: #495057;'>📊 Status Peserta Saat Ini:</h3>";
            echo "<table style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>Total Pendaftar</strong></td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'>" . ($s['total'] ?? 0) . "</td></tr>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>└─ Lulus Berkas</td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'>" . ($s['lulus'] ?? 0) . "</td></tr>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>└─ Sudah Bayar</td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'>" . ($s['paid'] ?? 0) . "</td></tr>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>└─ Punya Nomor Peserta</td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'>" . ($s['has_number'] ?? 0) . "</td></tr>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>└─ Lulus + Bayar</strong></td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'><strong>" . ($s['lulus_paid'] ?? 0) . "</strong></td></tr>";
            echo "<tr><td style='padding: 8px; border-bottom: 1px solid #dee2e6;'><strong>└─ Eligible (Lulus + Bayar + Nomor)</strong></td><td style='padding: 8px; border-bottom: 1px solid #dee2e6; text-align: right;'><strong style='color: #17a2b8;'>" . ($s['eligible'] ?? 0) . "</strong></td></tr>";
            echo "<tr><td style='padding: 8px;'><strong>└─ Sudah Dijadwalkan ✅</strong></td><td style='padding: 8px; text-align: right;'><strong style='color: #28a745;'>" . ($s['scheduled'] ?? 0) . "</strong></td></tr>";
            echo "</table>";
            echo "</div>";

            $eligible = $s['eligible'] ?? 0;
            $scheduled = $s['scheduled'] ?? 0;

            if ($eligible > 0 && $scheduled == 0) {
                echo "<div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>";
                echo "<strong>⚠️ Langkah Selanjutnya:</strong><br>";
                echo "Ada <strong>{$eligible} peserta</strong> yang sudah memenuhi syarat tetapi <strong>belum dijadwalkan</strong>.<br>";
                echo "Silakan jadwalkan peserta melalui menu <a href='/admin/scheduler' style='color: #007bff; text-decoration: none;'><strong>Penjadwalan Ujian</strong></a>.";
                echo "</div>";
            } elseif ($eligible == 0) {
                echo "<div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;'>";
                echo "<strong>❌ Tidak Ada Peserta Eligible:</strong><br>";
                echo "Belum ada peserta yang memenuhi kriteria: <strong>Lulus Berkas + Sudah Bayar + Punya Nomor Peserta</strong>.<br>";
                echo "Pastikan data peserta sudah diimport dan diverifikasi dengan benar.";
                echo "</div>";
            }

            echo "<div style='margin-top: 30px; text-align: center;'>";
            echo "<button onclick='window.close()' style='display: inline-block; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;'>Tutup Tab Ini</button>";
            echo "</div>";
            echo "</div>";

            return;
        }

        // Format Date to Indonesia dd mmmm yyyy
        foreach ($participants as &$p) {
            $p['tanggal_formatted'] = $this->formatDateID($p['tanggal_ujian']);
        }

        // Get letterhead template from settings
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        // Render print view without filter form
        echo \App\Utils\View::render('pdf.attendance_print', [
            'participants' => $participants,
            'semesterName' => $activeSemester['nama'],
            'filterSesi' => $filterSesi,
            'filterRuang' => $filterRuang,
            'letterhead' => $letterhead
        ]);
    }

    private function parseTemplate($html, $p)
    {
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        // 1. Placeholder Kop Surat
        $html = str_replace('[kop_surat]', $letterhead, $html);

        // 2. Foto Peserta
        $photoHtml = '';
        if (!empty($p['photo_filename'])) {
            $photoPath = dirname(__DIR__, 2) . '/storage/photos/' . $p['photo_filename'];
            if (file_exists($photoPath)) {
                // Convert to base64 for PDF embedding
                $imageData = base64_encode(file_get_contents($photoPath));
                $ext = pathinfo($p['photo_filename'], PATHINFO_EXTENSION);
                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $photoHtml = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" style="width: 3cm; height: 4cm; object-fit: cover; border: 1px solid #ccc;">';
            }
        }

        if (empty($photoHtml)) {
            // Placeholder jika tidak ada foto
            $photoHtml = '<div style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center; font-size: 12px; text-align: center; background: #f5f5f5;">FOTO<br>3x4</div>';
        }

        $html = str_replace('[foto_peserta]', $photoHtml, $html);

        // 3. Placeholder Data Peserta
        $tgl_lahir = $this->formatDateID($p['tgl_lahir'] ?? '');
        $tmpt_tgl = strtoupper(trim(($p['tempat_lahir'] ?? '') . ', ' . $tgl_lahir, ', '));

        $tags = [
            '[nomor_peserta]' => $p['nomor_peserta'] ?? '-',
            '[nama_peserta]' => strtoupper($p['nama_lengkap'] ?? '-'),
            '[tgl_lahir]' => $tmpt_tgl,
            '[prodi]' => strtoupper($p['nama_prodi'] ?? '-'),
            '[tanggal_ujian]' => $this->formatDateID($p['tanggal_ujian'] ?? ''),
            '[waktu_ujian]' => $p['waktu_ujian'] ?? 'Waktu Menyusul',
            '[ruang_ujian]' => $p['ruang_ujian'] ?? 'Ruangan Menyusul',
            '[gedung]' => $p['gedung'] ?? ($p['ruang_ujian'] ? 'Gedung Terkait' : 'Gedung Menyusul')
        ];

        foreach ($tags as $tag => $value) {
            $html = str_replace($tag, $value, $html);
        }

        // 4. Barcode (Simple implementation using a library or online service)
        if (strpos($html, '[barcode]') !== false) {
            $nomor = $p['nomor_peserta'] ?? '00000000000';
            // Using a simple barcode generator service for public URL access
            $barcodeHtml = '<img src="https://barcodeapi.org/api/128/' . $nomor . '" style="height: 40px; width: auto;">';
            $html = str_replace('[barcode]', $barcodeHtml, $html);
        }

        return $html;
    }

    private function formatDateID($date)
    {
        if (empty($date))
            return '-';
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        try {
            $time = strtotime($date);
            $d = date('j', $time);
            $m = (int) date('n', $time);
            $y = date('Y', $time);

            return "{$d} {$months[$m]} {$y}";
        } catch (\Exception $e) {
            return $date;
        }
    }
}
