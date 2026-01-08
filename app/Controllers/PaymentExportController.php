<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\View;
use App\Utils\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;

class PaymentExportController
{
    private $db;

    public function __construct()
    {
        // Check Admin Auth
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $this->db = Database::connection();
    }

    /**
     * Dashboard View
     */
    public function index()
    {
        try {
            // Stats
            $stats = [
                'total' => $this->db->query("SELECT count(*) as count FROM reregistrations")->fetchAssoc()['count'] ?? 0,
                'verified' => $this->db->query("SELECT count(*) as count FROM reregistrations WHERE status_sia = 'Aktif'")->fetchAssoc()['count'] ?? 0,
                'unverified' => $this->db->query("SELECT count(*) as count FROM reregistrations WHERE status_sia != 'Aktif' OR status_sia IS NULL")->fetchAssoc()['count'] ?? 0,
            ];

            echo View::render('admin.payment_export.index', [
                'stats' => $stats
            ]);
        } catch (\PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    /**
     * Import Sirema Data (Data Mentah)
     */
    public function importSirema()
    {
        $file = Request::files('file_sirema');
        $periode = Request::get('periode') ?? '20251';

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            header('Location: /admin/payment-export?error=Upload failed');
            return;
        }

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Detect headers (simple scan of first few rows logic or assume standard)
            // Based on analysis: Row 1 has headers: 'Nomor Ujian', 'Nama', 'NIM', 'Kode', 'UKT'

            $headerMap = [];
            $dataStartRow = 2; // Default

            // Find header row
            foreach ($rows as $idx => $row) {
                if (in_array('Nomor Ujian', $row) && in_array('NIM', $row)) {
                    $headerMap = array_flip($row);
                    $dataStartRow = $idx + 1;
                    break;
                }
            }

            if (empty($headerMap)) {
                throw new \Exception("Invalid File Format: Could not find 'Nomor Ujian' and 'NIM' headers");
            }

            $imported = 0;
            $updated = 0;

            for ($i = $dataStartRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nomorPeserta = trim($row[$headerMap['Nomor Ujian']] ?? '');
                $nim = trim($row[$headerMap['NIM']] ?? '');
                $nama = trim($row[$headerMap['Nama']] ?? '');
                $kodeProdi = trim($row[$headerMap['Kode']] ?? ''); // 'Kode' in file
                $nominal = $row[$headerMap['UKT']] ?? 0;

                if (!$nomorPeserta || !$nim)
                    continue;

                // Cleanup nominal (remove non-numeric)
                $nominal = preg_replace('/[^0-9]/', '', $nominal);

                // Check existing
                $exists = $this->db->select('reregistrations')
                    ->where('nomor_peserta', $nomorPeserta)
                    ->where('periode', $periode)
                    ->fetchAssoc();

                if ($exists) {
                    $this->db->update('reregistrations')
                        ->params([
                            'nim' => $nim,
                            'nama' => $nama,
                            'kode_prodi' => $kodeProdi,
                            'nominal' => $nominal
                        ])
                        ->where('id', $exists['id'])
                        ->execute();
                    $updated++;
                } else {
                    $this->db->insert('reregistrations')
                        ->params([
                            'nomor_peserta' => $nomorPeserta,
                            'nim' => $nim,
                            'nama' => $nama,
                            'kode_prodi' => $kodeProdi,
                            'periode' => $periode,
                            'nominal' => $nominal,
                            'status_sia' => 'unknown'
                        ])
                        ->execute();
                    $imported++;
                }
            }

            header("Location: /admin/payment-export?success=Imported $imported, Updated $updated Sirema records");

        } catch (\Exception $e) {
            header('Location: /admin/payment-export?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Download Sirema Import Template
     */
    public function downloadTemplateSirema()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set Headers
            $headers = ['No', 'Nomor Ujian', 'NIM', 'Nama', 'Kode', 'UKT'];
            $sheet->fromArray($headers, null, 'A1');

            // Add Example Data
            $example = [
                ['1', '20251122271', '2520420310018', 'CONTOH MAHASISWA', '63111', '5500000']
            ];
            $sheet->fromArray($example, null, 'A2');

            // Style Header
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Template_Import_Sirema.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            header('Location: /admin/payment-export?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Download SIA Import Template
     */
    public function downloadTemplateSia()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set Headers based on standard SIA format
            $headers = ['No', 'NIM', 'Nama', 'Fakultas', 'Program Studi', 'Status'];
            $sheet->fromArray($headers, null, 'A1');

            // Add Example Data
            $example = [
                ['1', '2520420310018', 'CONTOH MAHASISWA', 'PASCASARJANA', 'MAGISTER MANAJEMEN', 'Aktif'],
                ['2', '2520420310019', 'MAHASISWA NON AKTIF', 'PASCASARJANA', 'MAGISTER ILMU HUKUM', 'Non-Aktif']
            ];
            $sheet->fromArray($example, null, 'A2');

            // Style Header
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Template_Import_SIA.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            header('Location: /admin/payment-export?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Import SIA Data (Verification)
     */
    public function importSia()
    {
        $file = Request::files('file_sia');
        $periode = Request::get('periode') ?? '20251';

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            header('Location: /admin/payment-export?error=Upload failed');
            return;
        }

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(); // Takes memory, but simpler

            // Analyzed headers: NIM | Nama | Angkatan | Jenjang | Program Studi | Fakultas | Status

            // Normalize and Find Headers
            $headerMap = [];
            $dataStartRow = 4;
            $foundHeaders = false;

            // Common aliases
            $nimAliases = ['nim', 'nomor induk mahasiswa', 'no. nim'];
            $statusAliases = ['status', 'status mahasiswa', 'status bayar', 'keterangan', 'ket'];

            foreach ($rows as $idx => $row) {
                // Normalize row values for check
                $rowLower = array_map(function ($val) {
                    return strtolower(trim($val ?? ''));
                }, $row);

                $colNimIndex = -1;
                $colStatusIndex = -1;

                // Find NIM col
                foreach ($nimAliases as $alias) {
                    $key = array_search($alias, $rowLower);
                    if ($key !== false) {
                        $colNimIndex = $key;
                        break;
                    }
                }

                // Find Status col
                foreach ($statusAliases as $alias) {
                    $key = array_search($alias, $rowLower);
                    if ($key !== false) {
                        $colStatusIndex = $key;
                        break;
                    }
                }

                if ($colNimIndex !== -1 && $colStatusIndex !== -1) {
                    $headerMap['NIM'] = $colNimIndex;
                    $headerMap['Status'] = $colStatusIndex;
                    $dataStartRow = $idx + 1;
                    $foundHeaders = true;
                    file_put_contents('debug_payment.log', "Found headers at row $idx. Indexes: NIM=$colNimIndex, Status=$colStatusIndex\n", FILE_APPEND);
                    break;
                }
            }

            if (!$foundHeaders) {
                file_put_contents('debug_payment.log', "FAILED TO FIND HEADERS. First 5 rows:\n" . print_r(array_slice($rows, 0, 5), true), FILE_APPEND);
                throw new \Exception("Invalid SIA File Format: Could not find 'NIM' and 'Status' columns");
            }

            $matched = 0;
            file_put_contents('debug_payment.log', "Processing data starting from row $dataStartRow\n", FILE_APPEND);

            // Debug: Check total rows
            file_put_contents('debug_payment.log', "Total rows in file: " . count($rows) . "\n", FILE_APPEND);

            for ($i = $dataStartRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rawNim = $row[$headerMap['NIM']] ?? null;
                $nim = trim((string) $rawNim); // Force string cast
                $status = trim($row[$headerMap['Status']] ?? 'unknown');

                // Debug first 5 rows to see what we are getting
                if ($i < $dataStartRow + 5) {
                    $rawType = gettype($rawNim);
                    $valStr = (string) $rawNim;
                    file_put_contents('debug_payment.log', "Row $i Debug: NIM RawType=$rawType Val='$valStr', Trimmed='$nim' | Status='$status'\n", FILE_APPEND);
                }

                if (!$nim)
                    continue;

                // Specific debug for problem NIM - Loose Check
                if (strpos($nim, '2520420320018') !== false) {
                    file_put_contents('debug_payment.log', "Row $i MATCH FOUND. NIM='$nim'. Status='$status'\n", FILE_APPEND);
                }

                // Normalize Status (maybe Title Case?)
                $status = ucwords(strtolower($status));

                // Update
                // Try exact match first
                $updated = $this->db->update('reregistrations')
                    ->params(['status_sia' => $status])
                    ->where('nim', $nim)
                    ->where('periode', $periode)
                    ->execute();

                // If no rows updated, try matching with legacy dirty data (spaces)
                // Use raw query for TRIM(nim) = ?
                if ($updated === 0) {
                    $this->db->query(
                        "UPDATE reregistrations SET status_sia = ? WHERE TRIM(nim) = ? AND periode = ?",
                        [$status, $nim, $periode]
                    );
                    // Check if it worked
                    if (strpos($nim, '2520420320018') !== false) {
                        file_put_contents('debug_payment.log', "Row $i: Fallback update attempt for 2520420320018 executed.\n", FILE_APPEND);
                    }
                } else {
                    if (strpos($nim, '2520420320018') !== false) {
                        file_put_contents('debug_payment.log', "Row $i: Exact update success for 2520420320018.\n", FILE_APPEND);
                    }
                }

                $matched++;
            }

            file_put_contents('debug_payment.log', "Finished. Total loop steps: $matched\n", FILE_APPEND);

            header("Location: /admin/payment-export?success=Verified status for $matched students");

        } catch (\Exception $e) {
            file_put_contents('debug_payment.log', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            header('Location: /admin/payment-export?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Generate Payment File
     */
    public function generatePayment()
    {
        $template = Request::files('template_payment');
        $setZero = isset($_POST['set_zero']) && $_POST['set_zero'] == '1';
        $periode = Request::get('periode') ?? '20251';

        if (!$template || $template['error'] !== UPLOAD_ERR_OK) {
            header('Location: /admin/payment-export?error=Upload failed');
            return;
        }

        try {
            $reader = IOFactory::createReaderForFile($template['tmp_name']);
            $spreadsheet = $reader->load($template['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            // Analyze where data starts (Row 6 based on analysis)
            // Headers: No | No Ujian | Nama | Status Tagihan | Jumlah Tagihan | Status Mahasiswa
            // Looking for "No Ujian" col index

            $colNoUjian = null;
            $colNama = null;
            $colNominal = null;
            $headerRow = 0;

            // Scan first 10 rows
            for ($r = 1; $r <= 10; $r++) {
                for ($c = 1; $c <= 6; $c++) { // Check up to column F
                    $val = $worksheet->getCellByColumnAndRow($c, $r)->getValue();
                    if (strpos(strtolower($val ?? ''), 'no ujian') !== false) {
                        $colNoUjian = $c;
                        $headerRow = $r;
                    }
                    if (strpos(strtolower($val ?? ''), 'jumlah tagihan') !== false) {
                        $colNominal = $c;
                    }
                    if (strpos(strtolower($val ?? ''), 'nama') !== false) {
                        $colNama = $c;
                    }
                }
                if ($colNoUjian)
                    break;
            }

            if (!$colNoUjian) {
                // Fallback: Assume Col B is No Ujian, Col E is Nominal (Standard ULM Template)
                $colNoUjian = 2; // B
                $colNominal = 5; // E
                $headerRow = 6;
            }

            $startRow = $headerRow + 1;
            $processedCount = 0;

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $cellNoUjian = $worksheet->getCellByColumnAndRow($colNoUjian, $row);
                $noUjian = trim($cellNoUjian->getValue() ?? '');

                if (empty($noUjian))
                    continue;

                // Find Map
                $data = $this->db->select('reregistrations')
                    ->where('nomor_peserta', $noUjian)
                    ->where('periode', $periode)
                    ->fetchAssoc();

                if ($data && !empty($data['nim'])) {
                    // Replace No Ujian with NIM (Force Text Format)
                    $cellNoUjian->setValueExplicit(
                        $data['nim'],
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );

                    // Optional: Update Nama if mismatch? (Better keep template/sirema names)
                    // if ($colNama && $data['nama']) {
                    //     $worksheet->getCellByColumnAndRow($colNama, $row)->setValue($data['nama']);
                    // }

                    $processedCount++;
                }

                // Set Zero Nominal
                if ($setZero && $colNominal) {
                    $worksheet->getCellByColumnAndRow($colNominal, $row)->setValue(0);
                }
            }

            // Output
            $originalName = pathinfo($template['name'], PATHINFO_FILENAME);
            $filename = $originalName . '-update-nim.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            header('Location: /admin/payment-export?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * API Data for DataTables
     */
    public function apiData()
    {
        $db = Database::connection();

        // Parameters
        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $search = $_GET['search']['value'] ?? '';

        // Count Total
        $totalRecords = $db->select('reregistrations')->count();

        // Query with Search - Manual Builder

        $filteredRecords = $totalRecords;

        if (!empty($search)) {
            $filteredRecords = $db->select('reregistrations')
                ->where('nomor_peserta', 'LIKE', "%$search%")
                ->orWhere('nim', 'LIKE', "%$search%")
                ->orWhere('nama', 'LIKE', "%$search%")
                ->count();

            $data = $db->select('reregistrations')
                ->where('nomor_peserta', 'LIKE', "%$search%")
                ->orWhere('nim', 'LIKE', "%$search%")
                ->orWhere('nama', 'LIKE', "%$search%")
                ->orderBy('id', 'desc')
                ->limit($length)
                ->offset($start)
                ->fetchAll();
        } else {
            $data = $db->select('reregistrations')
                ->orderBy('id', 'desc')
                ->limit($length)
                ->offset($start)
                ->fetchAll();
        }

        // Helper for Status Badge
        foreach ($data as &$row) {
            if ($row['status_sia'] == 'Aktif') {
                $row['status_html'] = '<span class="badge badge-success">Aktif</span>';
            } elseif ($row['status_sia'] == 'unknown') {
                $row['status_html'] = '<span class="badge badge-secondary">Unknown</span>';
            } else {
                $row['status_html'] = '<span class="badge badge-danger">' . htmlspecialchars($row['status_sia']) . '</span>';
            }
            // Format Nominal
            $row['nominal_formatted'] = 'Rp ' . number_format($row['nominal'], 0, ',', '.');
        }

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $data
        ]);
        exit;
    }
}
