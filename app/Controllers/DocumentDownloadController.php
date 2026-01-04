<?php

namespace App\Controllers;

use App\Models\Participant;
use App\Models\Semester;
use Leaf\Http\Request;

class DocumentDownloadController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        // Only admin and superadmin can access
        if (!\App\Utils\RoleHelper::canCRUD()) {
            response()->redirect('/admin');
            return;
        }

        $db = \App\Utils\Database::connection();

        // Get active semester
        $activeSemester = Semester::getActive();

        // Get all semesters for dropdown with periode info
        $semesters = Semester::all();

        // Get distinct prodi list from active semester
        $semesterId = $activeSemester['id'] ?? null;
        $prodiListSql = "SELECT DISTINCT nama_prodi, kode_prodi 
                         FROM participants 
                         WHERE semester_id = '$semesterId' 
                         AND nama_prodi IS NOT NULL 
                         ORDER BY nama_prodi ASC";
        $prodiList = $db->query($prodiListSql)->fetchAll();

        echo \App\Utils\View::render('admin.documents.download', [
            'activeSemester' => $activeSemester,
            'semesters' => $semesters,
            'prodiList' => $prodiList
        ]);
    }

    public function preview()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        if (!\App\Utils\RoleHelper::canCRUD()) {
            response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        $data = Request::body();
        $status = $data['status'] ?? 'all';
        $prodiId = $data['prodi_id'] ?? 'all';
        $semesterId = $data['semester_id'] ?? null;

        $db = \App\Utils\Database::connection();

        // Build query
        $whereClause = "WHERE 1=1";

        if ($semesterId) {
            $whereClause .= " AND p.semester_id = '$semesterId'";
        }

        if ($status !== 'all') {
            if ($status === 'peserta_ujian') {
                $whereClause .= " AND p.nomor_peserta IS NOT NULL AND p.nomor_peserta != ''";
            } else {
                $whereClause .= " AND p.status_berkas = '$status'";
            }
        }

        if ($prodiId !== 'all') {
            $whereClause .= " AND p.kode_prodi = '$prodiId'";
        }

        $sql = "SELECT p.*, s.nama as semester_nama 
                FROM participants p 
                LEFT JOIN semesters s ON p.semester_id = s.id 
                $whereClause
                ORDER BY p.kode_prodi, p.nama_lengkap ASC";

        $participants = $db->query($sql)->fetchAll();

        // Calculate document completeness
        $preview = [];
        $totalSize = 0;

        foreach ($participants as $p) {
            $docs = $this->checkDocumentCompleteness($p);
            $preview[] = [
                'id' => $p['id'],
                'nomor_peserta' => $p['nomor_peserta'] ?? '-',
                'nama_lengkap' => $p['nama_lengkap'],
                'email' => $p['email'] ?? '-',
                'nama_prodi' => $p['nama_prodi'],
                'status_berkas' => $p['status_berkas'],
                'docs' => $docs,
                'is_s3' => (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false)
            ];

            // Estimate size (rough)
            $totalSize += $docs['total_files'] * 500000; // ~500KB per file
        }

        response()->json([
            'success' => true,
            'total' => count($participants),
            'participants' => $preview,
            'estimated_size' => $this->formatBytes($totalSize)
        ]);
    }

    public function generateZip()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        if (!\App\Utils\RoleHelper::canCRUD()) {
            response()->redirect('/admin');
            return;
        }

        $data = Request::body();
        $status = $data['status'] ?? 'all';
        $prodiId = $data['prodi_id'] ?? 'all';
        $semesterId = $data['semester_id'] ?? null;

        $db = \App\Utils\Database::connection();

        // Build query (same as preview)
        $whereClause = "WHERE 1=1";

        if ($semesterId) {
            $whereClause .= " AND p.semester_id = '$semesterId'";
        }

        if ($status !== 'all') {
            if ($status === 'peserta_ujian') {
                $whereClause .= " AND p.nomor_peserta IS NOT NULL AND p.nomor_peserta != ''";
            } else {
                $whereClause .= " AND p.status_berkas = '$status'";
            }
        }

        if ($prodiId !== 'all') {
            $whereClause .= " AND p.kode_prodi = '$prodiId'";
        }

        $sql = "SELECT p.*, s.nama as semester_nama, s.kode as semester_kode
                FROM participants p 
                LEFT JOIN semesters s ON p.semester_id = s.id 
                $whereClause
                ORDER BY p.kode_prodi, p.nama_lengkap ASC";

        $participants = $db->query($sql)->fetchAll();

        if (empty($participants)) {
            echo "Tidak ada data peserta untuk di-download.";
            return;
        }

        // Generate ZIP
        $statusLabel = $status === 'all' ? 'SEMUA' :
            ($status === 'peserta_ujian' ? 'PESERTA_UJIAN' : strtoupper($status));
        $prodiLabel = $prodiId === 'all' ? 'SEMUA_PRODI' : $prodiId;
        $semesterLabel = $participants[0]['semester_kode'] ?? 'N/A';
        $date = date('Ymd_His');

        $zipFilename = "BERKAS_{$statusLabel}_{$prodiLabel}_{$semesterLabel}_{$date}.zip";
        $tempPath = sys_get_temp_dir() . '/' . $zipFilename;

        $zip = new \ZipArchive();
        if ($zip->open($tempPath, \ZipArchive::CREATE) !== true) {
            echo "Gagal membuat ZIP file.";
            return;
        }

        // Determine S3 columns visibility
        $showS3Columns = true;
        if ($prodiId !== 'all' && !empty($participants)) {
            $pName = $participants[0]['nama_prodi'] ?? '';
            $showS3Columns = (stripos($pName, 'S3') !== false || stripos($pName, 'DOKTOR') !== false);
        }

        // Generate Excel summary
        $excelPath = $this->generateExcelSummary($participants, $status, $showS3Columns);
        $zip->addFile($excelPath, '0_REKAPITULASI.xlsx');

        $baseStoragePath = dirname(__DIR__, 2) . '/storage';

        // Add participant documents
        foreach ($participants as $p) {
            $folderName = ($p['nomor_peserta'] ?? 'temp_' . $p['id']) . '_' . $this->sanitizeFilename($p['nama_lengkap']);

            // 1. Formulir PDF
            $formulirPath = \App\Utils\FormulirPdfGenerator::generate($p);
            if (file_exists($formulirPath)) {
                $zip->addFile($formulirPath, $folderName . '/1_Formulir.pdf');
            }

            // 2. KTP
            if (!empty($p['ktp_filename'])) {
                $ktpPath = $baseStoragePath . '/documents/ktp/' . $p['ktp_filename'];
                if (file_exists($ktpPath)) {
                    $zip->addFile($ktpPath, $folderName . '/2_KTP.jpg');
                }
            }

            // 3. Foto
            if (!empty($p['photo_filename'])) {
                $fotoPath = $baseStoragePath . '/photos/' . $p['photo_filename'];
                if (file_exists($fotoPath)) {
                    $zip->addFile($fotoPath, $folderName . '/3_Foto.jpg');
                }
            }

            // 4. Ijazah S1
            if (!empty($p['ijazah_filename'])) {
                $ijazahPath = $baseStoragePath . '/documents/ijazah/' . $p['ijazah_filename'];
                if (file_exists($ijazahPath)) {
                    $zip->addFile($ijazahPath, $folderName . '/4_Ijazah_S1.jpg');
                }
            }

            // 5. Transkrip S1
            if (!empty($p['transkrip_filename'])) {
                $transkripPath = $baseStoragePath . '/documents/transkrip/' . $p['transkrip_filename'];
                if (file_exists($transkripPath)) {
                    $zip->addFile($transkripPath, $folderName . '/5_Transkrip_S1.pdf');
                }
            }

            // 6. Kartu Peserta (only if lulus & has exam number)
            if ($p['status_berkas'] === 'lulus' && !empty($p['nomor_peserta'])) {
                $kartuPath = \App\Utils\ExamCardGenerator::generate($p);
                if (file_exists($kartuPath)) {
                    $zip->addFile($kartuPath, $folderName . '/6_Kartu_Peserta.pdf');
                }
            }

            // 7-8. S2 documents (if S3/Doktor)
            $isS3 = stripos($p['nama_prodi'] ?? '', 'S3') !== false ||
                stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false;

            if ($isS3) {
                if (!empty($p['ijazah_s2_filename'])) {
                    $ijazahS2Path = $baseStoragePath . '/documents/ijazah_s2/' . $p['ijazah_s2_filename'];
                    if (file_exists($ijazahS2Path)) {
                        $zip->addFile($ijazahS2Path, $folderName . '/7_Ijazah_S2.jpg');
                    }
                }

                if (!empty($p['transkrip_s2_filename'])) {
                    $transkripS2Path = $baseStoragePath . '/documents/transkrip_s2/' . $p['transkrip_s2_filename'];
                    if (file_exists($transkripS2Path)) {
                        $zip->addFile($transkripS2Path, $folderName . '/8_Transkrip_S2.pdf');
                    }
                }
            }
        }

        $zip->close();

        // Cleanup Excel temp file
        if (file_exists($excelPath)) {
            unlink($excelPath);
        }

        // Stream download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($tempPath));
        readfile($tempPath);

        // Cleanup ZIP temp file
        unlink($tempPath);
        exit;
    }

    private function checkDocumentCompleteness($participant)
    {
        $baseStoragePath = dirname(__DIR__, 2) . '/storage';
        $isS3 = stripos($participant['nama_prodi'] ?? '', 'S3') !== false ||
            stripos($participant['nama_prodi'] ?? '', 'DOKTOR') !== false;

        $docs = [
            'formulir' => true, // Always generated
            'ktp' => !empty($participant['ktp_filename']) &&
                file_exists($baseStoragePath . '/documents/ktp/' . $participant['ktp_filename']),
            'foto' => !empty($participant['photo_filename']) &&
                file_exists($baseStoragePath . '/photos/' . $participant['photo_filename']),
            'ijazah_s1' => !empty($participant['ijazah_filename']) &&
                file_exists($baseStoragePath . '/documents/ijazah/' . $participant['ijazah_filename']),
            'transkrip_s1' => !empty($participant['transkrip_filename']) &&
                file_exists($baseStoragePath . '/documents/transkrip/' . $participant['transkrip_filename']),
            'kartu' => $participant['status_berkas'] === 'lulus' && !empty($participant['nomor_peserta']),
            'ijazah_s2' => $isS3 ? (!empty($participant['ijazah_s2_filename']) &&
                file_exists($baseStoragePath . '/documents/ijazah_s2/' . $participant['ijazah_s2_filename'])) : null,
            'transkrip_s2' => $isS3 ? (!empty($participant['transkrip_s2_filename']) &&
                file_exists($baseStoragePath . '/documents/transkrip_s2/' . $participant['transkrip_s2_filename'])) : null
        ];

        $required = ['formulir', 'ktp', 'foto', 'ijazah_s1', 'transkrip_s1'];
        if ($docs['kartu'] !== false)
            $required[] = 'kartu';
        if ($isS3) {
            $required[] = 'ijazah_s2';
            $required[] = 'transkrip_s2';
        }

        $completed = 0;
        $total = count($required);

        foreach ($required as $key) {
            if ($docs[$key])
                $completed++;
        }

        $docs['total_files'] = $total;
        $docs['completed_files'] = $completed;
        $docs['completeness_percent'] = $total > 0 ? round(($completed / $total) * 100) : 0;

        return $docs;
    }

    private function generateExcelSummary($participants, $status, $showS3Columns = true)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi');

        // Headers Construction
        $headers = [
            'No',
            'No Peserta',
            'Nama',
            'Prodi',
            // Identitas
            'Tempat Lahir',
            'Tgl Lahir',
            'Alamat KTP',
            'Kecamatan',
            'Kota/Kab',
            'Provinsi',
            'Kode Pos',
            'Telpon/HP',
            'Agama',
            'Gender',
            'Status Pernikahan',
            'Pekerjaan',
            'Instansi Pekerjaan',
            'Alamat Pekerjaan',
            'Telpon Pekerjaan',
            // Pendidikan S1
            'S1 Tahun Masuk',
            'S1 Tahun Tamat',
            'S1 Perguruan Tinggi',
            'S1 Fakultas',
            'S1 Prodi',
            'S1 IPK',
            'S1 Gelar'
        ];

        // S2 Headers (Only if S3/Superadmin)
        if ($showS3Columns) {
            $headers = array_merge($headers, [
                'S2 Tahun Masuk',
                'S2 Tahun Tamat',
                'S2 Perguruan Tinggi',
                'S2 Fakultas',
                'S2 Prodi',
                'S2 IPK',
                'S2 Gelar'
            ]);
        }

        // Checklist Common
        $headers = array_merge($headers, [
            'Formulir',
            'KTP',
            'Foto',
            'Ijazah S1',
            'Transkrip S1',
            'Kartu'
        ]);

        // Checklist S2 (Only if S3/Superadmin)
        if ($showS3Columns) {
            $headers = array_merge($headers, [
                'Ijazah S2',
                'Transkrip S2'
            ]);
        }

        // Set Headers
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Style header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";

        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');

        // Data
        $row = 2;
        foreach ($participants as $i => $p) {
            $docs = $this->checkDocumentCompleteness($p);
            $isS3Applicant = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);

            $colIdx = 1;
            // Basic
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['nomor_peserta'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['nama_lengkap']);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['nama_prodi']);

            // Identitas
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['tempat_lahir'] ?? '-');
            $tglLahir = !empty($p['tgl_lahir']) ? date('d-m-Y', strtotime($p['tgl_lahir'])) : '-';
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $tglLahir);
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['alamat_ktp'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['kecamatan'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['kota'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['provinsi'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['kode_pos'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['no_hp'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['agama'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['jenis_kelamin'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['status_pernikahan'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['pekerjaan'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['instansi_pekerjaan'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['alamat_pekerjaan'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['telpon_pekerjaan'] ?? '-');

            // Pendidikan S1
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_tahun_masuk'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_tahun_tamat'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_perguruan_tinggi'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_fakultas'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_prodi'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_ipk'] ?? '-');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $p['s1_gelar'] ?? '-');

            // Pendidikan S2 (Only if Show S3 Columns)
            if ($showS3Columns) {
                // If it's a mixed dataset (Superadmin), only show S2 data if the *row* is S3
                // If it's single Prodi S3, isS3Applicant is always true.
                // If it's single Prodi S2, showS3Columns is false, so we skip this block entirely.
                $hasS2Data = $isS3Applicant;

                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_tahun_masuk'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_tahun_tamat'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_perguruan_tinggi'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_fakultas'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_prodi'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_ipk'] ?? '-') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $hasS2Data ? ($p['s2_gelar'] ?? '-') : '-');
            }

            // Checklist Dokumen Common
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['formulir'] ? '✅' : '❌');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['ktp'] ? '✅' : '❌');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['foto'] ? '✅' : '❌');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['ijazah_s1'] ? '✅' : '❌');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['transkrip_s1'] ? '✅' : '❌');
            $sheet->setCellValueByColumnAndRow($colIdx++, $row, $docs['kartu'] ? '✅' : '❌');

            // Checklist S2 (Only if Show S3 Columns)
            if ($showS3Columns) {
                // Same logic, show real status only if applicant is S3
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $isS3Applicant ? ($docs['ijazah_s2'] ? '✅' : '❌') : '-');
                $sheet->setCellValueByColumnAndRow($colIdx++, $row, $isS3Applicant ? ($docs['transkrip_s2'] ? '✅' : '❌') : '-');
            }

            $row++;
        }

        // Auto-size columns
        foreach (range(1, count($headers)) as $col) {
            $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colStr)->setAutoSize(true);
        }

        // Save to temp
        $tempPath = sys_get_temp_dir() . '/rekapitulasi_' . time() . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    private function sanitizeFilename($filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        return substr($filename, 0, 50);
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
