<?php

namespace App\Controllers;

use App\Utils\Database;
use App\Utils\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Semester;

class ReportController
{
    /**
     * Show Report Filter Page
     */
    public function index()
    {
        // Admin middleware check
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $activeSemester = Semester::getActive();
        $semesters = Database::connection()->select('semesters')->orderBy('id', 'DESC')->fetchAll();

        echo View::render('admin.report.index', [
            'activeSemester' => $activeSemester,
            'semesters' => $semesters
        ]);
    }

    /**
     * Generate PDF Report
     */
    public function print()
    {
        // Admin middleware check
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $semesterId = $_POST['semester_id'] ?? 'all';
        $db = Database::connection();

        // 1. Fetch Semester Info
        if ($semesterId === 'all') {
            $semesterName = "Semua Semester (Gabungan)";
            $whereClause = "";  // No filter
        } else {
            $sem = $db->select('semesters')->where('id', $semesterId)->first();
            $semesterName = $sem['nama'] ?? 'Semester Tidak Diketahui';
            $whereClause = "WHERE semester_id = '$semesterId'";
        }

        // 2. Aggregate Data (S2 & S3 Split)
        $sqlProdi = "SELECT 
            nama_prodi,
            COUNT(*) as total,
            SUM(CASE WHEN status_berkas = 'lulus' THEN 1 ELSE 0 END) as lulus,
            SUM(CASE WHEN status_berkas = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status_berkas = 'gagal' THEN 1 ELSE 0 END) as gagal,
            SUM(CASE WHEN status_pembayaran = 1 THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status_pembayaran = 0 AND status_berkas = 'lulus' THEN 1 ELSE 0 END) as unpaid
            FROM participants 
            $whereClause
            GROUP BY nama_prodi
            ORDER BY nama_prodi ASC";

        $prodiStats = $db->query($sqlProdi)->fetchAll(\PDO::FETCH_ASSOC);

        // Split Logic
        $s2Stats = array_filter($prodiStats, function ($item) {
            $name = strtoupper($item['nama_prodi']);
            return strpos($name, 'S2') !== false || strpos($name, 'MAGISTER') !== false;
        });

        $s3Stats = array_filter($prodiStats, function ($item) {
            $name = strtoupper($item['nama_prodi']);
            return strpos($name, 'S3') !== false || strpos($name, 'DOKTOR') !== false;
        });

        // 3. Setup DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // For images if needed

        $dompdf = new Dompdf($options);

        // Fetch Letterhead (Kop Surat)
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');
        if (empty($letterhead)) {
            // Fallback default from ExamCardSettingController
            $letterhead = '
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="100px" align="center">
                        <img src="https://simari.ulm.ac.id/logo/ulm.png" width="80px">
                    </td>
                    <td align="center">
                        <b style="font-size:16px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                        <b style="font-size:18px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                        <b style="font-size:22px;">ADMISI PASCASARJANA</b><br>
                        <span style="font-size:10px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin, 70123</span>
                    </td>
                </tr>
            </table>';
        }

        // Indonesian Date Formatting
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        $dateStr = date('d F Y, H:i');
        foreach ($months as $en => $id) {
            $dateStr = str_replace($en, $id, $dateStr);
        }

        // 4. Render HTML for PDF
        $html = View::render('admin.report.pdf', [
            'semesterName' => $semesterName,
            's2Stats' => $s2Stats,
            's3Stats' => $s3Stats,
            'generatedAt' => $dateStr,
            'letterhead' => $letterhead
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 5. Output PDF (Stream)
        $filename = "Laporan_PMB_" . str_replace(['/', ' '], '_', $semesterName) . ".pdf";
        $dompdf->stream($filename, ["Attachment" => false]); // false = Preview in Browser
    }
}
