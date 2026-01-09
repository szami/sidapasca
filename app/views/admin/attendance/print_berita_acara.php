<?php
// app/views/admin/attendance/print_berita_acara.php

// Helper for Indonesian Date
if (!function_exists('indoDate')) {
    function indoDate($timestamp)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
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
        $d = getdate($timestamp);
        return $days[$d['wday']] . ', ' . $d['mday'] . ' ' . $months[$d['mon']] . ' ' . $d['year'];
    }
}
if (!function_exists('indoDay')) {
    function indoDay($timestamp)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $d = getdate($timestamp);
        return $days[$d['wday']];
    }
}
if (!function_exists('indoDateOnly')) {
    function indoDateOnly($timestamp)
    {
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
        $d = getdate($timestamp);
        return $d['mday'] . ' ' . $months[$d['mon']] . ' ' . $d['year'];
    }
}

if (!function_exists('terbilang')) {
    function terbilang($x)
    {
        $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        if ($x < 12)
            return " " . $angka[$x];
        elseif ($x < 20)
            return terbilang($x - 10) . " belas";
        elseif ($x < 100)
            return terbilang($x / 10) . " puluh" . terbilang($x % 10);
        elseif ($x < 200)
            return " seratus" . terbilang($x - 100);
        elseif ($x < 1000)
            return terbilang($x / 100) . " ratus" . terbilang($x % 100);
        elseif ($x < 2000)
            return " seribu" . terbilang($x - 1000);
        elseif ($x < 1000000)
            return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
        elseif ($x < 1000000000)
            return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
    }
}

if (!function_exists('indoDateText')) {
    function indoDateText($timestamp)
    {
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
        $d = getdate($timestamp);
        $tgl = trim(terbilang($d['mday']));
        $bln = $months[$d['mon']];
        $thn = trim(terbilang($d['year']));

        return "tanggal $tgl bulan $bln tahun $thn";
    }
}
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html>

<head>
    <title>BERITA ACARA PELAKSANAAN UJIAN</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Roboto", Arial, sans-serif;
            font-size: 13px;
            background: #eee;
        }

        /* A4 Page Simulation for Screen Preview */
        page[size="A4"] {
            background: white;
            width: 210mm;
            min-height: 297mm;
            display: block;
            margin: 10px auto;
            padding: 20mm 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        /* Print Styles - Critical for A4 Precision */
        @media print {
            @page {
                size: A4 portrait;
                margin: 20mm 20mm 20mm 20mm;
            }

            html,
            body {
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }

            page[size="A4"] {
                width: 100%;
                min-height: auto;
                height: auto;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                page-break-inside: avoid;
            }

            /* Page break before all pages except first */
            page[size="A4"]~page[size="A4"] {
                page-break-before: always;
            }

            .no-print {
                display: none !important;
            }
        }

        .no-print {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-print {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }

        .btn-print:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .label-col {
            width: 200px;
            font-weight: bold;
        }

        .separator {
            width: 20px;
            text-align: center;
        }

        .content-text {
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: justify;
            font-size: 14px;
        }

        .recap-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .recap-table td {
            padding: 10px 8px;
            border: 1px solid #000;
        }

        .notes-box {
            border: 1px solid #000;
            min-height: 120px;
            padding: 10px;
            margin-bottom: 30px;
        }

        .signature-section {
            width: 100%;
            margin-top: 50px;
        }

        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
        }

        .signature-line {
            margin-top: 80px;
            border-bottom: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> Cetak / Simpan sebagai PDF
        </button>
        <button class="btn-print btn-secondary" onclick="window.close()">
            Tutup Tab
        </button>
    </div>

    <?php
    // Handle single data vs array of data (backward compatibility just in case, though controller sends array)
    if (!isset($baData) && isset($tanggal)) {
        $baData = [
            [
                'ruang' => $ruang,
                'sesi' => $sesi,
                'tanggal' => $tanggal,
                'gedung' => $gedung,
                'waktu' => $waktu,
                'total_assigned' => $total_assigned
            ]
        ];
    }
    ?>

    <?php foreach ($baData as $index => $data): ?>
        <?php $ts = strtotime($data['tanggal']); ?>

        <page size="A4">
            <!-- Letterhead -->
            <?php if (!empty($letterhead)): ?>
                <?php echo $letterhead; ?>
            <?php else: ?>
                <table class="header-table">
                    <tr>
                        <td width="15%" align="center">
                            <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo" width="80">
                        </td>
                        <td align="center">
                            <span style="font-size: 14px">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</span><br>
                            <span style="font-size: 16px; font-weight: bold">UNIVERSITAS LAMBUNG MANGKURAT</span><br>
                            <span style="font-size: 20px; font-weight: bold">ADMISI PASCASARJANA</span><br>
                            <span style="font-size: 12px">Jl. Brigjen H. Hasan Basry, Kayu Tangi, Banjarmasin 70123</span>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>



            <div class="title">
                BERITA ACARA<br>
                PELAKSANAAN SELEKSI PENERIMAAN MAHASISWA BARU<br>
                PROGRAM PASCASARJANA UNIVERSITAS LAMBUNG MANGKURAT<br>
                SEMESTER <?php echo strtoupper($activeSemester['nama'] ?? ''); ?>
            </div>

            <div class="content-text">
                Pada hari ini <strong><?php echo indoDay($ts); ?></strong>
                <strong><?php echo indoDateText($ts); ?></strong>, bertempat di
                <strong><?php echo $data['gedung'] . ' - ' . $data['ruang']; ?></strong>, telah dilaksanakan Ujian Seleksi
                Penerimaan Mahasiswa Baru Program Pascasarjana Universitas Lambung Mangkurat dengan rincian sebagai berikut:
            </div>

            <table class="info-table">
                <tr>
                    <td class="label-col">Semester</td>
                    <td class="separator">:</td>
                    <td><?php echo $activeSemester['nama'] ?? '-'; ?></td>
                </tr>
                <tr>
                    <td class="label-col">Hari / Tanggal</td>
                    <td class="separator">:</td>
                    <td><?php echo date('d/m/Y', $ts); ?></td>
                </tr>
                <tr>
                    <td class="label-col">Waktu</td>
                    <td class="separator">:</td>
                    <td><?php echo $data['waktu']; ?> WITA</td>
                </tr>
                <tr>
                    <td class="label-col">Sesi / Ruang</td>
                    <td class="separator">:</td>
                    <td><?php echo $data['sesi']; ?> / <?php echo $data['ruang']; ?></td>
                </tr>
            </table>

            <div class="content-text">
                <strong>Rekapitulasi Peserta:</strong>
            </div>

            <table class="recap-table">
                <tr>
                    <td width="60%">1. Jumlah Peserta Terdaftar</td>
                    <td width="40%"><strong><?php echo $data['total_assigned']; ?></strong> orang</td>
                </tr>
                <tr>
                    <td>2. Jumlah Peserta Hadir</td>
                    <td>................... orang</td>
                </tr>
                <tr>
                    <td>3. Jumlah Peserta Tidak Hadir</td>
                    <td>................... orang</td>
                </tr>
            </table>

            <div class="content-text">
                <strong>Catatan Kejadian Penting selama Ujian berlangsung:</strong>
            </div>

            <div class="notes-box">
                <!-- Manual filling area -->
                <br><br><br><br>
            </div>

            <div class="content-text">
                Demikian Berita Acara ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    Banjarmasin, <?php echo indoDateOnly($ts); ?><br>
                    Pengawas Ruangan,
                    <br><br><br><br><br>
                    ( ........................................................... )<br>
                </div>
                <div style="clear: both;"></div>
            </div>
        </page>
    <?php endforeach; ?>

</body>

</html>