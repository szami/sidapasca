<?php

namespace App\Utils;

use Dompdf\Dompdf;
use Dompdf\Options;

class ExamCardGenerator
{
    public static function generate($participant)
    {
        // Render HTML view first
        $db = \App\Utils\Database::connection();
        $query = "SELECT p.*, r.fakultas as gedung 
                  FROM participants p 
                  LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang 
                  WHERE p.id = ?";
        $result = $db->query($query)->bind($participant['id'])->fetchAll();
        $p = !empty($result) ? $result[0] : null;

        if (!$p) {
            $p = $participant;
        }

        // Get custom layout from settings if available
        $layout = \App\Models\Setting::get('exam_card_layout', '');

        if (!empty($layout)) {
            // Use custom layout with parseTemplate
            $html = self::parseTemplate($layout, $p);
        } else {
            // Use default template
            $html = self::getDefaultHtml($p);
        }

        // Generate PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save to temp
        $filename = 'kartu_' . ($p['nomor_peserta'] ?? $p['id']) . '_' . time() . '.pdf';
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempPath, $dompdf->output());

        return $tempPath;
    }

    private static function getDefaultHtml($participant)
    {
        $p = $participant;

        // Month translation
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

        // Format tanggal lahir
        $tmpt = $p['tempat_lahir'] ?? '';
        $tgl = !empty($p['tgl_lahir']) ? date('d F Y', strtotime($p['tgl_lahir'])) : '-';
        foreach ($months as $en => $id_mon) {
            $tgl = str_replace($en, $id_mon, $tgl);
        }
        $ttl = strtoupper(trim("$tmpt, $tgl", ", "));

        // Format tanggal ujian
        $tglUjian = !empty($p['tanggal_ujian']) ? date('d F Y', strtotime($p['tanggal_ujian'])) : 'Jadwal Menyusul';
        foreach ($months as $en => $id_mon) {
            $tglUjian = str_replace($en, $id_mon, $tglUjian);
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <title>KARTU UJIAN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        table.header {
            font-size: 11px;
            color: #333333;
            border-collapse: collapse;
            width: 100%;
        }
        table.header td {
            padding: 8px;
        }
        .info {
            font-size: 14px;
        }
        hr {
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <table class="header" width="100%">
        <tbody>
            <tr>
                <td width="120px" align="center">
                    <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo ULM" width="100px">
                </td>
                <td align="center">
                    <b style="font-size:18px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                    <b style="font-size:20px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                    <b style="font-size:24px;">ADMISI PASCASARJANA</b><br>
                    <span style="font-size:11px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70123</span><br>
                    <span style="font-size:11px;">Telp. (0511) 33066003, 3304177, 3306694, 3305195, Kotak Pos 219</span>
                </td>
            </tr>
        </tbody>
    </table>
    <hr>
    
    <table width="100%" style="font-weight:bold; margin-top: 20px;">
        <tbody>
            <tr>
                <td align="center" style="font-size:16px;">KARTU PESERTA</td>
            </tr>
            <tr>
                <td align="center" style="font-size:16px;">TES UJIAN MASUK</td>
            </tr>
        </tbody>
    </table>
    
    <br><br>
    
    <table width="100%" class="info" align="center">
        <tbody>
            <tr>
                <td width="180px" align="center" style="vertical-align: top;">
                    <div style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center;">
                        <span style="line-height: 4cm; text-align: center;">FOTO 3x4</span>
                    </div>
                </td>
                <td style="vertical-align: top;">
                    <table style="font-size:14px; width: 100%;">
                        <tbody>
                            <tr style="height: 30px;">
                                <td width="150px">NOMOR PESERTA</td>
                                <td width="10px">:</td>
                                <td><b>' . htmlspecialchars($p['nomor_peserta'] ?? '-') . '</b></td>
                            </tr>
                            <tr style="height: 30px;">
                                <td>NAMA</td>
                                <td>:</td>
                                <td>' . strtoupper(htmlspecialchars($p['nama_lengkap'] ?? '-')) . '</td>
                            </tr>
                            <tr style="height: 30px;">
                                <td>TEMPAT, TGL LAHIR</td>
                                <td>:</td>
                                <td>' . $ttl . '</td>
                            </tr>
                            <tr style="height: 30px;">
                                <td>PROGRAM STUDI</td>
                                <td>:</td>
                                <td>' . strtoupper(htmlspecialchars($p['nama_prodi'] ?? '-')) . '</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    
    <br><br>
    
    <table width="100%">
        <tbody>
            <tr>
                <td align="center" style="font-size:14px;">WAKTU PELAKSANAAN TES :</td>
            </tr>
            <tr>
                <td align="center" style="font-size:14px; font-weight:bold;">
                    <p><strong>' . $tglUjian . '</strong></p>
                    <p><strong>' . htmlspecialchars($p['waktu_ujian'] ?? 'Waktu Menyusul') . '</strong></p>
                </td>
            </tr>
            <tr>
                <td align="center" style="font-size:14px; padding-top: 10px;">TEMPAT PELAKSANAAN TES :</td>
            </tr>
            <tr>
                <td align="center" style="font-size:14px; font-weight:bold;">
                    <p><strong>' . htmlspecialchars($p['ruang_ujian'] ?? 'Gedung Pascasarjana ULM') . '</strong></p>
                </td>
            </tr>
        </tbody>
    </table>
    
    <table border="0" style="font-size:12px; width:100%; margin-top: 30px;">
        <tbody>
            <tr>
                <td>Catatan:</td>
            </tr>
            <tr>
                <td>
                    <ul style="padding-left: 20px; list-style-type: disc;">
                        <li><strong>Kartu ini wajib dibawa saat pelaksanaan ujian.</strong></li>
                        <li><strong>Informasi detail lokasi ujian TPA akan diinformasikan melalui email peserta atau website.</strong></li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>';

        return $html;
    }

    private static function parseTemplate($html, $p)
    {
        // Define month replacements for Indonesian date formatting
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

        // Format dates
        $tgl_lahir = !empty($p['tgl_lahir']) ? date('d F Y', strtotime($p['tgl_lahir'])) : '-';
        $tanggal_ujian = !empty($p['tanggal_ujian']) ? date('d F Y', strtotime($p['tanggal_ujian'])) : 'Jadwal Menyusul';

        foreach ($months as $en => $id_mon) {
            $tgl_lahir = str_replace($en, $id_mon, $tgl_lahir);
            $tanggal_ujian = str_replace($en, $id_mon, $tanggal_ujian);
        }

        // Prepare photo HTML
        $photoHtml = '<div style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center;"><span style="line-height: 4cm;">FOTO</span></div>';
        if (!empty($p['photo_filename'])) {
            // Fix path: __DIR__ is app/Utils. Level 2 up is project root.
            $photoPath = dirname(__DIR__, 2) . '/storage/photos/' . $p['photo_filename'];
            if (file_exists($photoPath)) {
                $imageData = base64_encode(file_get_contents($photoPath));
                $ext = pathinfo($p['photo_filename'], PATHINFO_EXTENSION);
                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $photoHtml = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" style="width: 3cm; height: 4cm; object-fit: cover; border: 1px solid #000;">';
            }
        }

        // Get Letterhead - DEFAULT to Setting, fallback to hardcoded if empty
        $kopSurat = \App\Models\Setting::get('exam_card_letterhead', '');
        if (empty($kopSurat)) {
            // Default letterhead
            $kopSurat = '
            <table width="100%" style="border-collapse: collapse; margin-bottom: 5px;">
                <tr>
                    <td width="100px" align="center">
                        <img src="https://simari.ulm.ac.id/logo/ulm.png" width="80px">
                    </td>
                    <td align="center">
                        <b style="font-size:14px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                        <b style="font-size:16px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                        <b style="font-size:18px;">ADMISI PASCASARJANA</b><br>
                        <span style="font-size:9px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70123</span><br>
                        <span style="font-size:9px;">Telp. (0511) 33066003, 3304177, 3306694, 3305195, Kotak Pos 219</span>
                    </td>
                </tr>
            </table><hr>';
        }

        // Prepare data variables
        $nama = strtoupper(htmlspecialchars($p['nama_lengkap'] ?? '-'));
        $nomor = htmlspecialchars($p['nomor_peserta'] ?? '-');
        $prodi = strtoupper(htmlspecialchars($p['nama_prodi'] ?? '-'));
        $tempat = htmlspecialchars($p['tempat_lahir'] ?? '-');
        $waktu = htmlspecialchars($p['waktu_ujian'] ?? 'Waktu Menyusul');
        $ruang = htmlspecialchars($p['ruang_ujian'] ?? 'Gedung Pascasarjana ULM');
        $gedung = htmlspecialchars($p['gedung'] ?? $ruang);

        // Comprehensive replacements array (supports both { } and [ ])
        $replacements = [
            // Standard brackets { }
            '{kop_surat}' => $kopSurat,
            '{nomor_peserta}' => $nomor,
            '{nama_lengkap}' => $nama,
            '{nama}' => $nama,
            '{tempat_lahir}' => $tempat,
            '{tgl_lahir}' => $tgl_lahir,
            '{nama_prodi}' => $prodi,
            '{prodi}' => $prodi,
            '{tanggal_ujian}' => $tanggal_ujian,
            '{waktu_ujian}' => $waktu,
            '{ruang_ujian}' => $ruang,
            '{gedung}' => $gedung,
            '{foto}' => $photoHtml,
            '{foto_peserta}' => $photoHtml,

            // Square brackets [ ] (as seen in user screenshot)
            '[kop_surat]' => $kopSurat,
            '[nomor_peserta]' => $nomor,
            '[nama_peserta]' => $nama,
            '[nama]' => $nama,
            '[tempat_lahir]' => $tempat,
            '[tgl_lahir]' => $tgl_lahir,
            '[prodi]' => $prodi,
            '[program_studi]' => $prodi,
            '[tanggal_ujian]' => $tanggal_ujian,
            '[waktu_ujian]' => $waktu,
            '[ruang_ujian]' => $ruang,
            '[gedung]' => $gedung,
            '[foto]' => $photoHtml,
            '[foto_peserta]' => $photoHtml,

            // Legacy/Typo handlers
            '[nama_lengkap]' => $nama,
            '[nama_prodi]' => $prodi,
        ];

        foreach ($replacements as $placeholder => $value) {
            $html = str_replace($placeholder, $value, $html);
        }

        return $html;
    }
}
