<?php
// app/views/admin/attendance/print_attendance_list.php

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
?>
<!DOCTYPE html>
<html>

<head>
    <title>DAFTAR PESERTA
        <?php echo strtoupper($type === 'present' ? 'HADIR' : 'TIDAK HADIR'); ?>
    </title>
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
            margin-bottom: 20px;
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
            width: 150px;
            font-weight: bold;
        }

        .separator {
            width: 20px;
            text-align: center;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .signature-section {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
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

    <?php foreach ($listData as $data): ?>
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
                DAFTAR PESERTA UJIAN
                <?php echo strtoupper($type === 'present' ? 'HADIR' : 'TIDAK HADIR'); ?><br>
                SELEKSI PENERIMAAN MAHASISWA BARU<br>
                TAHUN AKADEMIK
                <?php echo strtoupper($activeSemester['nama'] ?? ''); ?>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label-col">Hari / Tanggal</td>
                    <td class="separator">:</td>
                    <td>
                        <?php echo indoDay($ts) . ', ' . indoDateOnly($ts); ?>
                    </td>
                    <td class="label-col" style="padding-left: 20px;">Sesi</td>
                    <td class="separator">:</td>
                    <td>
                        <?php echo $data['sesi']; ?> (
                        <?php echo $data['waktu']; ?>)
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Ruangan</td>
                    <td class="separator">:</td>
                    <td>
                        <?php echo $data['ruang']; ?>
                    </td>
                    <td class="label-col" style="padding-left: 20px;">Gedung</td>
                    <td class="separator">:</td>
                    <td>
                        <?php echo $data['gedung']; ?>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Nama Lengkap</th>
                        <th width="20%">Nomor Peserta</th>
                        <th width="30%">Program Studi</th>
                        <?php if ($type === 'present'): ?>
                            <th width="15%">Tanda Tangan</th>
                        <?php else: ?>
                            <th width="15%">Keterangan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['participants'])): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; font-style: italic;">
                                Tidak ada peserta
                                <?php echo $type === 'present' ? 'hadir' : 'tidak hadir'; ?> pada sesi ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($data['participants'] as $p): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php echo $no++; ?>
                                </td>
                                <td>
                                    <?php echo strtoupper($p['nama_lengkap']); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php echo $p['nomor_peserta']; ?>
                                </td>
                                <td>
                                    <?php echo $p['nama_prodi']; ?>
                                </td>
                                <?php if ($type === 'present'): ?>
                                    <td></td>
                                <?php else: ?>
                                    <td style="text-align: center;">Tidak Hadir</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="signature-section">
                <div class="signature-box">
                    Banjarmasin,
                    <?php echo indoDateOnly($ts); ?><br>
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