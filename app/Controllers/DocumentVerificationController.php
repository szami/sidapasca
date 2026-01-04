<?php

namespace App\Controllers;

use App\Models\Participant;
use App\Models\DocumentVerification;
use App\Models\Semester;
use Leaf\Http\Request;
use App\Utils\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DocumentVerificationController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $statusFilter = Request::get('status') ?? 'all';

        $verifications = DocumentVerification::getAllWithParticipants($semesterId, $statusFilter);
        $semesters = Semester::all();
        $stats = DocumentVerification::getStatistics($semesterId);

        echo View::render('admin.verification.index', [
            'verifications' => $verifications,
            'semesters' => $semesters,
            'currentSemester' => $semesterId,
            'statusFilter' => $statusFilter,
            'stats' => $stats
        ]);
    }

    public function verify($id)
    {
        $this->checkAuth();

        $participant = Participant::find($id);
        if (!$participant) {
            header('Location: /admin/verification/physical');
            exit;
        }

        $verification = DocumentVerification::findByParticipant($id);
        if (!$verification) {
            // Create initial record if not exists
            $verification = DocumentVerification::create($id);
        }

        // Determine if S3 based on prodi name
        $isS3 = (stripos($participant['nama_prodi'] ?? '', 'S3') !== false || stripos($participant['nama_prodi'] ?? '', 'DOKTOR') !== false);

        echo View::render('admin.verification.verify', [
            'participant' => $participant,
            'verification' => $verification,
            'isS3' => $isS3
        ]);
    }

    public function save($id)
    {
        $this->checkAuth();

        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/verification/physical');
            exit;
        }

        $data = [
            'formulir_pendaftaran' => Request::get('formulir_pendaftaran') ? 1 : 0,
            'formulir_pendaftaran_jumlah' => Request::get('formulir_pendaftaran_jumlah'),

            'ijazah_s1_legalisir' => Request::get('ijazah_s1_legalisir') ? 1 : 0,
            'ijazah_s1_jumlah' => Request::get('ijazah_s1_jumlah'),

            'transkrip_s1_legalisir' => Request::get('transkrip_s1_legalisir') ? 1 : 0,
            'transkrip_s1_jumlah' => Request::get('transkrip_s1_jumlah'),

            'bukti_pembayaran' => Request::get('bukti_pembayaran') ? 1 : 0,
            'bukti_pembayaran_jumlah' => Request::get('bukti_pembayaran_jumlah'),

            'surat_rekomendasi' => Request::get('surat_rekomendasi') ? 1 : 0,
            'surat_rekomendasi_jumlah' => Request::get('surat_rekomendasi_jumlah'),

            'ijazah_s2_legalisir' => Request::get('ijazah_s2_legalisir') ? 1 : 0,
            'ijazah_s2_jumlah' => Request::get('ijazah_s2_jumlah'),

            'transkrip_s2_legalisir' => Request::get('transkrip_s2_legalisir') ? 1 : 0,
            'transkrip_s2_jumlah' => Request::get('transkrip_s2_jumlah'),

            'catatan_admin' => Request::get('catatan_admin'),
        ];

        // Handle Bypass Verification Logic
        // Only Superadmin can change it. 
        // If not superadmin, preserve existing value (or default 0) to avoid overwrite.
        $existing = DocumentVerification::findByParticipant($id);
        $bypassValue = $existing['bypass_verification'] ?? 0;

        if (($_SESSION['admin_role'] ?? '') === 'superadmin') {
            $bypassValue = Request::get('bypass_verification') ? 1 : 0;
        }

        $data['bypass_verification'] = $bypassValue;
        $data['verified_by'] = $_SESSION['admin'];


        DocumentVerification::updateVerification($id, $data);

        // Redirect back with success message
        header("Location: /admin/verification/physical/$id?success=1");
        exit;
    }
    public function reset($id)
    {
        $this->checkAuth();
        DocumentVerification::deleteByParticipant($id);
        header("Location: /admin/verification/physical/$id?success=reset");
        exit;
    }

    public function downloadTemplate()
    {
        $this->checkAuth();

        // Fetch active semester
        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        // Fetch participants for active semester only
        $db = \App\Utils\Database::connection();
        $sql = "SELECT id, nomor_peserta, nama_lengkap, nama_prodi FROM participants WHERE 1=1";
        $params = [];

        if ($semesterId) {
            $sql .= " AND semester_id = ?";
            $params[] = $semesterId;
        }

        $sql .= " ORDER BY nama_lengkap ASC";

        $participants = $db->query($sql)->bind(...$params)->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'A1' => 'participant_id',
            'B1' => 'nomor_peserta',
            'C1' => 'nama_peserta',
            'D1' => 'nama_prodi',
            'E1' => 'formulir_pendaftaran',
            'F1' => 'ijazah_s1_legalisir',
            'G1' => 'transkrip_s1_legalisir',
            'H1' => 'bukti_pembayaran',
            'I1' => 'surat_rekomendasi',
            'J1' => 'ijazah_s2_legalisir',
            'K1' => 'transkrip_s2_legalisir',
            'L1' => 'catatan_admin'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($participants as $p) {
            $sheet->setCellValue('A' . $row, $p['id']);
            $sheet->setCellValue('B' . $row, $p['nomor_peserta']);
            $sheet->setCellValue('C' . $row, $p['nama_lengkap']);
            $sheet->setCellValue('D' . $row, $p['nama_prodi']);

            // Set default 0 for verification columns
            $sheet->setCellValue('E' . $row, 0);
            $sheet->setCellValue('F' . $row, 0);
            $sheet->setCellValue('G' . $row, 0);
            $sheet->setCellValue('H' . $row, 0);
            $sheet->setCellValue('I' . $row, 0);
            $sheet->setCellValue('J' . $row, 0);
            $sheet->setCellValue('K' . $row, 0);

            $row++;
        }

        $lastRow = $row - 1;

        // Protection Logic
        $sheet->getProtection()->setSheet(true);
        // Optional: Set password
        // $sheet->getProtection()->setPassword('admin');

        // Unlock E-L
        if ($lastRow >= 2) {
            $sheet->getStyle('E2:L' . $lastRow)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        // Hide ID Column (A)
        $sheet->getColumnDimension('A')->setVisible(false);

        // AutoSize
        foreach (range('B', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (range('E', 'L') as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('L')->setWidth(30);

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_verifikasi_fisik.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        $this->checkAuth();

        $file = Request::files('verification_file');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            header("Location: /admin/verification/physical?error=upload_failed");
            exit;
        }

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $count = 0;

            foreach ($rows as $index => $row) {
                // Skip Header
                if ($index == 0)
                    continue;

                $participantId = $row[0]; // Column A
                if (empty($participantId))
                    continue;

                // Map Data (Column Index 0-based: A=0, B=1, C=2, D=3, E=4...)
                // E=4: Formulir
                $data = [
                    'formulir_pendaftaran' => $row[4] ? 1 : 0,
                    'ijazah_s1_legalisir' => $row[5] ? 1 : 0,
                    'transkrip_s1_legalisir' => $row[6] ? 1 : 0,
                    'bukti_pembayaran' => $row[7] ? 1 : 0,
                    'surat_rekomendasi' => $row[8] ? 1 : 0,
                    'ijazah_s2_legalisir' => $row[9] ? 1 : 0,
                    'transkrip_s2_legalisir' => $row[10] ? 1 : 0,
                    'catatan_admin' => $row[11] ?? '',
                    // Preserve session admin as verifier?
                    // 'verified_by' => $_SESSION['admin'] // Automatic in updateVerification if passed or default?
                    // updateVerification uses passed data['verified_by'] OR session fallback.
                    // But I should pass it to be explicit.
                    'verified_by' => $_SESSION['admin']
                ];

                // Note: We are bypassing the "Check existing bypass" logic in Controller::save
                // Should we respect bypass?
                // Import file does NOT have bypass column.
                // updateVerification retains bypass if we don't pass it?
                // Let's check updateVerification.
                // Line 110: `UPDATE ... bypass_verification = ?,`
                // Line 151: `isset($data['bypass_verification']) ? ... : 0`.
                // If I don't pass 'bypass_verification', `isset` is false -> returns 0.
                // GLOBAL RESET of bypass if I import!
                // BAD.
                // I need to fetch existing and preserve bypass.

                $existing = DocumentVerification::findByParticipant($participantId);
                if ($existing) {
                    $data['bypass_verification'] = $existing['bypass_verification'];
                } else {
                    $data['bypass_verification'] = 0;
                }

                DocumentVerification::updateVerification($participantId, $data);
                $count++;
            }

            header("Location: /admin/verification/physical?success=import&count=$count");
            exit;

        } catch (\Exception $e) {
            header("Location: /admin/verification/physical?error=" . urlencode('Error processing file: ' . $e->getMessage()));
            exit;
        }
    }
}
