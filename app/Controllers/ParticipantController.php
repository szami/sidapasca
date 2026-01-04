<?php

namespace App\Controllers;

use App\Models\Participant;
use App\Models\Semester;
use Leaf\Http\Request;

class ParticipantController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin');
            return;
        }

        // Fetch all participants with simple pagination or all for DataTables
        // Since we switched to raw PDO in models, let's use a simple query
        // For now, fetch ALL and let DataTables (client-side) handle text search/pagination
        // Optimization: In real app, server-side processing is better.

        $db = \App\Utils\Database::connection();

        // Get Active Semester
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;
        $semesterName = $activeSemester['nama'] ?? 'Semua Semester';

        // Default filter logic based on the new menu grouping
        $filter = Request::get('filter') ?? 'exam_ready';
        $prodiFilter = Request::get('prodi') ?? 'all'; // NEW: Prodi filter
        $paymentFilter = Request::get('payment') ?? 'all'; // NEW: Payment filter
        $hideExamNumber = false;
        $hideBilling = false;
        $hidePaymentStatus = false;
        $title = "Data Peserta";

        $whereClause = "WHERE 1=1";
        if ($semesterId) {
            $whereClause .= " AND p.semester_id = '$semesterId'";
        }

        switch ($filter) {
            case 'exam_ready':
                $whereClause .= " AND (p.nomor_peserta IS NOT NULL AND p.nomor_peserta != '')";
                $hideBilling = true; // Hide billing since they already paid
                $hidePaymentStatus = true; // Hide payment status (already paid)
                $title = "Data Peserta Ujian";
                break;
            case 'pending':
                $whereClause .= " AND p.status_berkas = 'pending'";
                $hideExamNumber = true;
                $hideBilling = true;
                $hidePaymentStatus = true;
                $title = "Formulir Masuk (Pending)";
                break;
            case 'lulus':
                // Show ALL lulus berkas (regardless of payment/exam number)
                $whereClause .= " AND p.status_berkas = 'lulus'";
                $hideExamNumber = true; // Hide exam number since not all have it yet
                $title = "Peserta Lulus Berkas";
                break;
            case 'gagal':
                $whereClause .= " AND p.status_berkas = 'gagal'";
                $hideExamNumber = true;
                $hideBilling = true;
                $hidePaymentStatus = true;
                $title = "Peserta Gagal Berkas";
                break;
            case 'all':
                $title = "Semua Data Peserta";
                break;
        }

        // NEW: Payment status filter
        if ($paymentFilter === 'paid') {
            $whereClause .= " AND p.status_pembayaran = 1";
        } elseif ($paymentFilter === 'unpaid') {
            $whereClause .= " AND (p.status_pembayaran = 0 OR p.status_pembayaran IS NULL)";
        }

        // NEW: Add prodi filter
        if ($prodiFilter !== 'all') {
            // Escape prodi name for SQL safety
            $prodiFilterEscaped = str_replace("'", "''", $prodiFilter);
            $whereClause .= " AND p.nama_prodi = '$prodiFilterEscaped'";
        }

        // ROLE-BASED: Auto-filter by prodi for admin_prodi
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $adminProdiId = \App\Utils\RoleHelper::getProdiId();
            if ($adminProdiId) {
                $whereClause .= " AND p.kode_prodi = '$adminProdiId'";
            }
        }

        // Get distinct prodi list for filter dropdown with counts (from active semester only)
        // Use active semester for dropdown regardless of main filter
        $prodiListSemesterId = $semesterId ?? 0; // Active semester ID
        $prodiListSql = "SELECT nama_prodi, COUNT(*) as total 
                         FROM participants 
                         WHERE semester_id = '$prodiListSemesterId' 
                         AND nama_prodi IS NOT NULL 
                         AND nama_prodi != ''
                         GROUP BY nama_prodi 
                         ORDER BY nama_prodi ASC";
        $prodiList = $db->query($prodiListSql)->fetchAll();

        $sql = "SELECT p.*, s.nama as semester_nama 
                FROM participants p 
                LEFT JOIN semesters s ON p.semester_id = s.id 
                $whereClause
                ORDER BY p.id DESC";

        $participants = $db->query($sql)->fetchAll();

        // Pass permission info to view
        $canCRUD = \App\Utils\RoleHelper::canCRUD();
        $isAdminProdi = \App\Utils\RoleHelper::isAdminProdi();
        $prodiName = '';

        if ($isAdminProdi && !empty($participants)) {
            // Get prodi name from first participant
            $prodiName = $participants[0]['nama_prodi'] ?? '';
        }

        echo \App\Utils\View::render('admin.participants.index', [
            'participants' => $participants,
            'activeSemester' => $activeSemester,
            'filter' => $filter,
            'prodiFilter' => $prodiFilter, // NEW
            'paymentFilter' => $paymentFilter, // NEW
            'prodiList' => $prodiList, // NEW
            'hideExamNumber' => $hideExamNumber,
            'hideBilling' => $hideBilling,
            'hidePaymentStatus' => $hidePaymentStatus,
            'pageTitle' => $title,
            'canCRUD' => $canCRUD,
            'isAdminProdi' => $isAdminProdi,
            'prodiName' => $prodiName
        ]);
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $participant = Participant::find($id);
        $semesters = Semester::all();

        echo \App\Utils\View::render('admin.participants.edit', [
            'p' => $participant,
            'semesters' => $semesters
        ]);
    }

    public function view($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $participant = Participant::find($id);
        if (!$participant) {
            response()->redirect('/admin/participants');
            return;
        }

        echo \App\Utils\View::render('admin.participants.view', ['p' => $participant]);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        // Filter allowed fields
        $updateData = [
            'nama_lengkap' => $data['nama_lengkap'],
            'email' => $data['email'],
            'no_billing' => $data['no_billing'] ?? null,
            'nomor_peserta' => $data['nomor_peserta'] ?? null,
            'semester_id' => $data['semester_id'],
            'status_berkas' => $data['status_berkas'],
            'status_pembayaran' => isset($data['status_pembayaran']) ? 1 : 0,
            // 'ruang_ujian' => $data['ruang_ujian'] ?? null,  <-- Removed to prevent overwrite
            // 'tanggal_ujian' => $data['tanggal_ujian'] ?? null,
            // 'waktu_ujian' => $data['waktu_ujian'] ?? null,
            // 'sesi_ujian' => $data['sesi_ujian'] ?? null,

            // Biodata Lengkap
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'alamat_ktp' => $data['alamat_ktp'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kota' => $data['kota'] ?? null,
            'provinsi' => $data['provinsi'] ?? null,
            'kode_pos' => $data['kode_pos'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'agama' => $data['agama'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'status_pernikahan' => $data['status_pernikahan'] ?? null,

            // Pekerjaan
            'pekerjaan' => $data['pekerjaan'] ?? null,
            'instansi_pekerjaan' => $data['instansi_pekerjaan'] ?? null,
            'alamat_pekerjaan' => $data['alamat_pekerjaan'] ?? null,
            'telpon_pekerjaan' => $data['telpon_pekerjaan'] ?? null,

            // Pendidikan S1
            's1_tahun_masuk' => $data['s1_tahun_masuk'] ?? null,
            's1_tahun_tamat' => $data['s1_tahun_tamat'] ?? null,
            's1_perguruan_tinggi' => $data['s1_perguruan_tinggi'] ?? null,
            's1_fakultas' => $data['s1_fakultas'] ?? null,
            's1_prodi' => $data['s1_prodi'] ?? null,
            's1_ipk' => $data['s1_ipk'] ?? null,
            's1_gelar' => $data['s1_gelar'] ?? null,

            // Pendidikan S2
            's2_tahun_masuk' => $data['s2_tahun_masuk'] ?? null,
            's2_tahun_tamat' => $data['s2_tahun_tamat'] ?? null,
            's2_perguruan_tinggi' => $data['s2_perguruan_tinggi'] ?? null,
            's2_fakultas' => $data['s2_fakultas'] ?? null,
            's2_prodi' => $data['s2_prodi'] ?? null,
            's2_ipk' => $data['s2_ipk'] ?? null,
            's2_gelar' => $data['s2_gelar'] ?? null,

            // Payment Details
            'transaction_id' => $data['transaction_id'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'payment_method' => $data['payment_method'] ?? null
        ];

        \App\Utils\Database::connection()->update('participants')
            ->params($updateData)
            ->where('id', $id)
            ->execute();

        response()->redirect('/admin/participants');
    }


    public function destroy($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        \App\Utils\Database::connection()->delete('participants')->where('id', $id)->execute();
        response()->redirect('/admin/participants');
    }

    public function uploadPhoto($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant) {
            response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            return;
        }

        $file = $_FILES['photo'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            response()->json(['success' => false, 'message' => 'File upload gagal'], 400);
            return;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            response()->json(['success' => false, 'message' => 'Hanya file JPG/PNG yang diizinkan'], 400);
            return;
        }

        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            response()->json(['success' => false, 'message' => 'Ukuran file maksimal 2MB'], 400);
            return;
        }

        // Determine Subfolder: SemesterCode-Periode
        $semester = Semester::find($participant['semester_id']);
        $subfolder = $semester ? $semester['kode'] : 'legacy';

        // Base photos directory
        $basePhotoDir = dirname(__DIR__, 2) . '/storage/photos';

        // Full target directory
        $targetDir = $basePhotoDir . '/' . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate filename based on nomor_peserta or participant ID
        $filename = ($participant['nomor_peserta'] ?? 'temp_' . $id) . '.jpg';
        $targetPath = $targetDir . '/' . $filename;

        // Relative path to store in DB
        $dbPath = $subfolder . '/' . $filename;

        // Delete old photo if exists
        if (!empty($participant['photo_filename'])) {
            $oldPath = $basePhotoDir . '/' . $participant['photo_filename'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            response()->json(['success' => false, 'message' => 'Gagal menyimpan file'], 500);
            return;
        }

        // Update database
        \App\Utils\Database::connection()->update('participants')
            ->params(['photo_filename' => $dbPath])
            ->where('id', $id)
            ->execute();

        response()->json(['success' => true, 'message' => 'Foto berhasil diupload', 'filename' => $dbPath]);
    }

    public function deletePhoto($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant || !$participant['photo_filename']) {
            response()->json(['success' => false, 'message' => 'Foto tidak ditemukan'], 404);
            return;
        }

        $photoPath = dirname(__DIR__, 2) . '/storage/photos/' . $participant['photo_filename'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }

        \App\Utils\Database::connection()->update('participants')
            ->params(['photo_filename' => null])
            ->where('id', $id)
            ->execute();

        response()->json(['success' => true, 'message' => 'Foto berhasil dihapus']);
    }

    public function autoDownloadPhoto($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant) {
            response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            return;
        }

        $email = $participant['email'];
        if (empty($email)) {
            response()->json(['success' => false, 'message' => 'Email peserta tidak ditemukan'], 400);
            return;
        }

        // Get session cookie from settings (admin must input this)
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        if (empty($sessionCookie)) {
            response()->json(['success' => false, 'message' => 'Session cookie belum dikonfigurasi. Silakan set di menu Settings.'], 400);
            return;
        }

        // Download ZIP from main system
        $semesterCode = '1'; // Default, bisa disesuaikan
        $url = "https://admisipasca.ulm.ac.id/administrator/formulir/download_zip/{$email}/{$semesterCode}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing

        $zipContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($zipContent)) {
            response()->json(['success' => false, 'message' => 'Gagal download ZIP dari sistem utama. HTTP Code: ' . $httpCode], 500);
            return;
        }

        // Save temporary ZIP file
        $tempZipPath = sys_get_temp_dir() . '/berkas_' . md5($email) . '.zip';
        file_put_contents($tempZipPath, $zipContent);

        // Extract photo from ZIP
        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath) !== true) {
            unlink($tempZipPath);
            response()->json(['success' => false, 'message' => 'Gagal membuka file ZIP'], 500);
            return;
        }

        $photoExtracted = false;

        // Determine Subfolder
        $semester = Semester::find($participant['semester_id']);
        $subfolder = $semester ? $semester['kode'] : 'legacy';

        $basePhotoDir = dirname(__DIR__, 2) . '/storage/photos';
        $targetDir = $basePhotoDir . '/' . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Find and extract photo (pasfoto_umum_*.jpeg or pasfoto_umum_*.jpg)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (
                strpos($filename, 'pasfoto_umum_') === 0 &&
                (strpos($filename, '.jpeg') !== false || strpos($filename, '.jpg') !== false)
            ) {

                $photoContent = $zip->getFromIndex($i);
                $newFilename = ($participant['nomor_peserta'] ?? 'temp_' . $id) . '.jpg';
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                // Delete old photo if exists
                if (!empty($participant['photo_filename'])) {
                    $oldPath = $basePhotoDir . '/' . $participant['photo_filename'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                file_put_contents($targetPath, $photoContent);

                // Update database
                \App\Utils\Database::connection()->update('participants')
                    ->params(['photo_filename' => $dbPath])
                    ->where('id', $id)
                    ->execute();

                $photoExtracted = true;
                break;
            }
        }

        $zip->close();
        unlink($tempZipPath);

        if ($photoExtracted) {
            response()->json(['success' => true, 'message' => 'Foto berhasil didownload dan disimpan']);
        } else {
            response()->json(['success' => false, 'message' => 'Foto tidak ditemukan dalam ZIP'], 404);
        }
    }

    public function exportExcel()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin');
            return;
        }

        $db = \App\Utils\Database::connection();
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        if (!$semesterId) {
            echo "Tidak ada semester aktif untuk ekspor.";
            return;
        }

        $sql = "SELECT p.*, r.fakultas 
                FROM participants p 
                LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang 
                WHERE p.semester_id = '$semesterId' 
                AND p.nomor_peserta IS NOT NULL 
                AND p.ruang_ujian IS NOT NULL 
                ORDER BY p.ruang_ujian ASC, p.nama_lengkap ASC";

        $participants = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($participants)) {
            echo "Tidak ada data peserta yang sudah dijadwalkan untuk diekspor.";
            return;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header - Sesuai urutan yang diminta
        $headers = [
            'ID',
            'GEDUNG',
            'RUANG',
            'TANGGAL',
            'SESI',
            'WAKTU',
            'NO URUT',
            'NO_PESERTA',
            'PASSWORD',
            'NAMA_PESERTA',
            'TTL',
            'JK',
            'PRODI PILIHAN',
            'EMAIL',
            'NO HP'
        ];

        foreach ($headers as $key => $title) {
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $title);
        }

        // Style Header
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        $rowNum = 2;
        $roomCounter = [];

        $months_id = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        foreach ($participants as $p) {
            $room = $p['ruang_ujian'] ?? '-';
            if (!isset($roomCounter[$room])) {
                $roomCounter[$room] = 1;
            } else {
                $roomCounter[$room]++;
            }

            // Format TTL (Tempat Tanggal Lahir)
            $ttl = '-';
            if (!empty($p['tgl_lahir'])) {
                $date = new \DateTime($p['tgl_lahir']);
                $day = $date->format('d');
                $month = $months_id[(int) $date->format('n')];
                $year = $date->format('Y');
                $ttl = ($p['tempat_lahir'] ? strtoupper($p['tempat_lahir']) . ', ' : '') . "$day $month $year";
            }

            // Format Tanggal Pelaksanaan
            $tanggal_pelaksanaan = '-';
            if (!empty($p['tanggal_ujian'])) {
                $date = new \DateTime($p['tanggal_ujian']);
                $day = $date->format('d');
                $month = $months_id[(int) $date->format('n')];
                $year = $date->format('Y');
                $tanggal_pelaksanaan = "$day $month $year";
            }

            // Password = Tanggal Lahir (Format: YYYY-MM-DD, tanpa hash)
            $password = $p['tgl_lahir'] ?? '-';

            // Data sesuai kolom yang diminta
            $sheet->setCellValue('A' . $rowNum, $p['id']);                                                      // ID
            $sheet->setCellValue('B' . $rowNum, $p['fakultas'] ?? 'Gedung Pascasarjana ULM');                  // GEDUNG
            $sheet->setCellValue('C' . $rowNum, $p['ruang_ujian']);                                            // RUANG
            $sheet->setCellValue('D' . $rowNum, $tanggal_pelaksanaan);                                         // TANGGAL_PELAKSANAAN
            $sheet->setCellValue('E' . $rowNum, $p['sesi_ujian']);                                             // SESI
            $sheet->setCellValue('F' . $rowNum, $p['waktu_ujian']);                                            // WAKTU
            $sheet->setCellValue('G' . $rowNum, $roomCounter[$room]);                                          // NO URUT
            $sheet->setCellValue('H' . $rowNum, $p['nomor_peserta']);                                          // NO_PESERTA
            $sheet->setCellValue('I' . $rowNum, $password);                                                    // PASSWORD (tgl lahir)
            $sheet->setCellValue('J' . $rowNum, strtoupper($p['nama_lengkap']));                               // NAMA_PESERTA
            $sheet->setCellValue('K' . $rowNum, $ttl);                                                         // TTL
            $sheet->setCellValue('L' . $rowNum, $p['jenis_kelamin'] ?? '-');                                   // JK
            $sheet->setCellValue('M' . $rowNum, $p['nama_prodi']);                                             // PRODI PILIHAN
            $sheet->setCellValue('N' . $rowNum, strtolower($p['email']));                                      // EMAIL
            $sheet->setCellValue('O' . $rowNum, $p['no_hp'] ?? '-');                                           // NO HP

            $rowNum++;
        }

        // Auto size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Data_Peserta_Ujian_" . str_replace(' ', '_', $activeSemester['nama'] ?? 'Export') . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generic document upload handler
     * Supports: foto, ktp, ijazah, transkrip
     */
    public function uploadDocument($id, $type)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $allowedTypes = ['foto', 'ktp', 'ijazah', 'transkrip', 'ijazah_s2', 'transkrip_s2'];
        if (!in_array($type, $allowedTypes)) {
            response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant) {
            response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            response()->json(['success' => false, 'message' => 'File upload error'], 400);
            return;
        }

        $file = $_FILES['file'];

        // Configuration per document type  
        $config = [
            'foto' => [
                'extensions' => ['jpg', 'jpeg', 'png'],
                'max_size' => 2097152,
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'folder' => dirname(__DIR__, 2) . '/storage/photos',
                'column' => 'photo_filename'
            ],
            'ktp' => [
                'extensions' => ['jpg', 'jpeg', 'png'],
                'max_size' => 5242880,
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'folder' => dirname(__DIR__, 2) . '/storage/documents/ktp',
                'column' => 'ktp_filename'
            ],
            'ijazah' => [
                'extensions' => ['jpg', 'jpeg', 'png'],
                'max_size' => 5242880,
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'folder' => dirname(__DIR__, 2) . '/storage/documents/ijazah',
                'column' => 'ijazah_filename'
            ],
            'transkrip' => [
                'extensions' => ['pdf'],
                'max_size' => 10485760,
                'mime_types' => ['application/pdf'],
                'folder' => dirname(__DIR__, 2) . '/storage/documents/transkrip',
                'column' => 'transkrip_filename'
            ],
            'ijazah_s2' => [
                'extensions' => ['jpg', 'jpeg', 'png'],
                'max_size' => 5242880,
                'mime_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                'folder' => dirname(__DIR__, 2) . '/storage/documents/ijazah_s2',
                'column' => 'ijazah_s2_filename'
            ],
            'transkrip_s2' => [
                'extensions' => ['pdf'],
                'max_size' => 10485760,
                'mime_types' => ['application/pdf'],
                'folder' => dirname(__DIR__, 2) . '/storage/documents/transkrip_s2',
                'column' => 'transkrip_s2_filename'
            ]
        ];

        $cfg = $config[$type];

        // Validate file size
        if ($file['size'] > $cfg['max_size']) {
            $maxMB = $cfg['max_size'] / 1048576;
            response()->json(['success' => false, 'message' => "File terlalu besar. Maksimal {$maxMB}MB"], 400);
            return;
        }

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $cfg['mime_types'])) {
            response()->json(['success' => false, 'message' => 'Format file tidak valid'], 400);
            return;
        }

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $cfg['extensions'])) {
            response()->json(['success' => false, 'message' => 'Ekstensi file tidak diizinkan'], 400);
            return;
        }

        // Determine Subfolder: SemesterCode-Periode
        $semester = Semester::find($participant['semester_id']);
        $subfolder = $semester ? $semester['kode'] : 'legacy';

        // Full target directory
        $targetDir = $cfg['folder'] . '/' . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate filename
        $nomor = $participant['nomor_peserta'] ?? 'temp_' . $id;
        if ($type === 'foto') {
            $newFilename = $nomor . '.jpg';
        } else {
            $newFilename = $nomor . '_' . $type . '.' . $ext;
        }

        $targetPath = $targetDir . '/' . $newFilename;
        $dbPath = $subfolder . '/' . $newFilename;

        // Delete old file if exists
        if (!empty($participant[$cfg['column']])) {
            $oldPath = $cfg['folder'] . '/' . $participant[$cfg['column']];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            response()->json(['success' => false, 'message' => 'Gagal menyimpan file'], 500);
            return;
        }

        // Update database (Save Relative Path)
        \App\Utils\Database::connection()->update('participants')
            ->params([$cfg['column'] => $dbPath])
            ->where('id', $id)
            ->execute();

        response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' berhasil diupload',
            'filename' => $dbPath
        ]);
    }

    /**
     * Generic document delete handler
     */
    public function deleteDocument($id, $type)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $allowedTypes = ['foto', 'ktp', 'ijazah', 'transkrip', 'ijazah_s2', 'transkrip_s2'];
        if (!in_array($type, $allowedTypes)) {
            response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant) {
            response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            return;
        }

        // Mapping type to folder and column
        $mapping = [
            'foto' => ['folder' => 'photos', 'column' => 'photo_filename'],
            'ktp' => ['folder' => 'documents/ktp', 'column' => 'ktp_filename'],
            'ijazah' => ['folder' => 'documents/ijazah', 'column' => 'ijazah_filename'],
            'transkrip' => ['folder' => 'documents/transkrip', 'column' => 'transkrip_filename'],
            'ijazah_s2' => ['folder' => 'documents/ijazah_s2', 'column' => 'ijazah_s2_filename'],
            'transkrip_s2' => ['folder' => 'documents/transkrip_s2', 'column' => 'transkrip_s2_filename']
        ];

        $map = $mapping[$type];
        $column = $map['column'];

        if (empty($participant[$column])) {
            response()->json(['success' => false, 'message' => ucfirst($type) . ' tidak ada'], 404);
            return;
        }

        // Delete file
        $filePath = dirname(__DIR__, 2) . '/storage/' . $map['folder'] . '/' . $participant[$column];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Update database (set to NULL)
        \App\Utils\Database::connection()->update('participants')
            ->params([$column => null])
            ->where('id', $id)
            ->execute();

        response()->json(['success' => true, 'message' => ucfirst($type) . ' berhasil dihapus']);
    }

    /**
     * Auto-download all documents from main system
     * Downloads ZIP and extracts: foto, KTP, ijazah, transkrip
     */
    public function autoDownloadDocuments($id)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $participant = Participant::find($id);
        if (!$participant) {
            response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan'], 404);
            return;
        }

        $email = $participant['email'];
        if (empty($email)) {
            response()->json(['success' => false, 'message' => 'Email peserta tidak ditemukan'], 400);
            return;
        }

        // Get session cookie
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        if (empty($sessionCookie)) {
            response()->json(['success' => false, 'message' => 'Session cookie belum dikonfigurasi'], 400);
            return;
        }

        // Download ZIP
        $semesterCode = '1';
        $url = "https://admisipasca.ulm.ac.id/administrator/formulir/download_zip/{$email}/{$semesterCode}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $zipContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($zipContent)) {
            response()->json(['success' => false, 'message' => 'Gagal download ZIP. HTTP: ' . $httpCode], 500);
            return;
        }

        // Save temp ZIP
        $tempZipPath = sys_get_temp_dir() . '/berkas_' . md5($email) . '.zip';
        file_put_contents($tempZipPath, $zipContent);

        // Extract documents
        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath) !== true) {
            unlink($tempZipPath);
            response()->json(['success' => false, 'message' => 'Gagal membuka ZIP'], 500);
            return;
        }

        $results = [
            'foto' => ['success' => false, 'message' => 'Tidak ditemukan'],
            'ktp' => ['success' => false, 'message' => 'Tidak ditemukan'],
            'ijazah' => ['success' => false, 'message' => 'Tidak ditemukan'],
            'transkrip' => ['success' => false, 'message' => 'Tidak ditemukan']
        ];

        $nomor = $participant['nomor_peserta'] ?? 'temp_' . $id;

        // Determine Subfolder
        $semester = Semester::find($participant['semester_id']);
        $subfolder = $semester ? $semester['kode'] : 'legacy';

        // Extract each document type
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Foto: pasfoto_umum_*.jpeg|jpg
            if (preg_match('/pasfoto_umum_.*\.(jpeg|jpg|png)$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $folder = dirname(__DIR__, 2) . '/storage/photos';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '.jpg';
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['photo_filename'])) {
                    $oldPath = $folder . '/' . $participant['photo_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['photo_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['foto'] = ['success' => true, 'filename' => $dbPath];
            }

            // KTP: ktp_umum_*.jpg|jpeg|png  
            elseif (preg_match('/ktp_umum_.*\.(jpg|jpeg|png)$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $folder = dirname(__DIR__, 2) . '/storage/documents/ktp';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '_ktp.' . $ext;
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['ktp_filename'])) {
                    $oldPath = $folder . '/' . $participant['ktp_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['ktp_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['ktp'] = ['success' => true, 'filename' => $dbPath];
            }

            // Ijazah: S1_ijasah_*.jpeg|jpg|png
            elseif (preg_match('/S1_ijasah_.*\.(jpeg|jpg|png)$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $folder = dirname(__DIR__, 2) . '/storage/documents/ijazah';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '_ijazah.' . $ext;
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['ijazah_filename'])) {
                    $oldPath = $folder . '/' . $participant['ijazah_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['ijazah_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['ijazah'] = ['success' => true, 'filename' => $dbPath];
            }

            // Transkrip: S1_transkrip_*.pdf
            elseif (preg_match('/S1_transkrip_.*\.pdf$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $folder = dirname(__DIR__, 2) . '/storage/documents/transkrip';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '_transkrip.pdf';
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['transkrip_filename'])) {
                    $oldPath = $folder . '/' . $participant['transkrip_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['transkrip_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['transkrip'] = ['success' => true, 'filename' => $dbPath];
            }

            // Ijazah S2: S2_ijasah_*.jpeg|jpg|png
            elseif (preg_match('/S2_ijasah_.*\.(jpeg|jpg|png)$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $folder = dirname(__DIR__, 2) . '/storage/documents/ijazah_s2';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '_ijazah_s2.' . $ext;
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['ijazah_s2_filename'])) {
                    $oldPath = $folder . '/' . $participant['ijazah_s2_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['ijazah_s2_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['ijazah_s2'] = ['success' => true, 'filename' => $dbPath];
            }

            // Transkrip S2: S2_transkrip_*.pdf
            elseif (preg_match('/S2_transkrip_.*\.pdf$/i', $filename)) {
                $content = $zip->getFromIndex($i);
                $folder = dirname(__DIR__, 2) . '/storage/documents/transkrip_s2';
                $targetDir = $folder . '/' . $subfolder;
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0755, true);

                $newFilename = $nomor . '_transkrip_s2.pdf';
                $targetPath = $targetDir . '/' . $newFilename;
                $dbPath = $subfolder . '/' . $newFilename;

                if (!empty($participant['transkrip_s2_filename'])) {
                    $oldPath = $folder . '/' . $participant['transkrip_s2_filename'];
                    if (file_exists($oldPath))
                        unlink($oldPath);
                }

                file_put_contents($targetPath, $content);
                \App\Utils\Database::connection()->update('participants')
                    ->params(['transkrip_s2_filename' => $dbPath])
                    ->where('id', $id)->execute();

                $results['transkrip_s2'] = ['success' => true, 'filename' => $dbPath];
            }
        }

        $zip->close();
        unlink($tempZipPath);

        // Count successes
        $successCount = 0;
        foreach ($results as $r) {
            if ($r['success'])
                $successCount++;
        }

        response()->json([
            'success' => true,
            'message' => "Berhasil download {$successCount}/4 dokumen",
            'results' => $results
        ]);
    }

    public function rotateDocument($id, $type)
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Only allow foto, ktp, ijazah (not transkrip - PDF)
        $allowedTypes = ['foto', 'ktp', 'ijazah'];
        if (!in_array($type, $allowedTypes)) {
            response()->json(['success' => false, 'message' => 'Invalid document type'], 400);
            return;
        }

        $direction = request()->get('direction'); // 'left' or 'right'
        if (!in_array($direction, ['left', 'right'])) {
            response()->json(['success' => false, 'message' => 'Invalid direction'], 400);
            return;
        }

        try {
            $participant = Participant::find($id);
            if (!$participant) {
                throw new \Exception('Participant not found');
            }

            // Get filename
            $filenameField = $type === 'foto' ? 'photo_filename' : $type . '_filename';
            $filename = $participant[$filenameField];

            if (empty($filename)) {
                throw new \Exception('No document to rotate');
            }

            // Get file path
            if ($type === 'foto') {
                $filepath = __DIR__ . '/../../storage/photos/' . $filename;
            } else {
                $filepath = __DIR__ . '/../../storage/documents/' . $type . '/' . $filename;
            }

            if (!file_exists($filepath)) {
                throw new \Exception('File not found');
            }

            // Detect image type
            $imageInfo = getimagesize($filepath);
            $mimeType = $imageInfo['mime'];

            // Load image based on type
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($filepath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($filepath);
                    break;
                default:
                    throw new \Exception('Unsupported image format');
            }

            if (!$image) {
                throw new \Exception('Failed to load image');
            }

            // Calculate rotation angle (GD uses counter-clockwise)
            // User requested to try 45 degrees
            $angle = $direction === 'right' ? -45 : 45;

            // Rotate image. For 45 degrees, we might need to handle the background color
            // Use white (255, 255, 255) as background for the empty areas
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            $rotated = imagerotate($image, $angle, $bgColor);

            if (!$rotated) {
                imagedestroy($image);
                throw new \Exception('Failed to rotate image');
            }

            // Save rotated image
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($rotated, $filepath, 90);
                    break;
                case 'image/png':
                    imagepng($rotated, $filepath, 9);
                    break;
            }

            // Free memory
            imagedestroy($image);
            imagedestroy($rotated);

            response()->json([
                'success' => true,
                'message' => 'Image rotated successfully'
            ]);

        } catch (\Exception $e) {
            response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
