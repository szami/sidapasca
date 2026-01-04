<?php
// This view is for printing only - no filter form, just the attendance table
?>
<!DOCTYPE html>
<html>

<head>
    <title>DAFTAR HADIR PESERTA UJIAN</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: "Roboto", Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #eee;
        }

        page[size="A4"] {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            /* Changed from height to min-height */
            display: block;
            margin: 0 auto;
            padding: 2cm;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            page-break-after: always;
            /* Ensure next group starts on new page */
        }

        @media print {
            body {
                background: white;
            }

            page[size="A4"] {
                margin: 0;
                box-shadow: none;
                width: 100%;
                padding: 0;
                page-break-before: always;
                /* Force new page for each group */
            }

            .no-print {
                display: none;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.attendance th,
        table.attendance td {
            border: 1px solid #333;
            padding: 4px 8px;
            text-align: left;
        }

        table.attendance th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .signature-area {
            margin-top: 25px;
            page-break-inside: avoid;
            width: 250px;
            margin-left: auto;
            text-align: center;
        }

        /* Ensure headers don't split from content */
        .group-header {
            page-break-after: avoid;
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
    // Group participants by Room and Session
    $groups = [];
    foreach ($participants as $p) {
        $key = $p['ruang_ujian'] . ' - ' . $p['sesi_ujian'];
        if (!isset($groups[$key])) {
            $groups[$key] = [
                'ruang' => $p['ruang_ujian'],
                'gedung' => $p['gedung'] ?? '-',
                'sesi' => $p['sesi_ujian'],
                'tanggal' => $p['tanggal_formatted'] ?? $p['tanggal_ujian'],
                'waktu' => $p['waktu_ujian'],
                'participants' => []
            ];
        }
        $groups[$key]['participants'][] = $p;
    }
    ?>

    <?php foreach ($groups as $key => $group): ?>
        <page size="A4">
            <div class="group-header">
                <!-- Letterhead (Dynamic from Settings) -->
                <?php if (!empty($letterhead)): ?>
                    <?php echo $letterhead; ?>
                <?php else: ?>
                    <!-- Default Letterhead if not set -->
                    <table class="header">
                        <tbody>
                            <tr>
                                <td width="100px" align="center">
                                    <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo ULM" width="80px">
                                </td>
                                <td align="center">
                                    <b style="font-size:16px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                                    <b style="font-size:18px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                                    <b style="font-size:22px;">ADMISI PASCASARJANA</b><br>
                                    <span style="font-size:10px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota
                                        Banjarmasin,
                                        70123</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                <hr style="border: 1px solid #000; margin: 10px 0;">

                <!-- Title & Info -->
                <div align="center" style="font-weight:bold; margin-bottom: 15px;">
                    <div style="font-size:15px;">DAFTAR HADIR PESERTA</div>
                    <div style="font-size:15px;">TES UJIAN MASUK PASCASARJANA</div>
                </div>

                <table style="margin-bottom: 12px; font-size: 11px;">
                    <tr>
                        <td width="80px">Semester</td>
                        <td width="250px">: <strong><?php echo $semesterName ?? '-'; ?></strong></td>
                        <td width="80px">Gedung</td>
                        <td>: <strong><?php echo htmlspecialchars($group['gedung']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: <strong><?php echo htmlspecialchars($group['tanggal']); ?></strong></td>
                        <td>Ruang</td>
                        <td>: <strong><?php echo htmlspecialchars($group['ruang']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>: <strong><?php echo htmlspecialchars($group['waktu']); ?></strong></td>
                        <td>Sesi</td>
                        <td>: <strong><?php echo htmlspecialchars($group['sesi']); ?></strong></td>
                    </tr>
                </table>
            </div>

            <!-- Attendance Table -->
            <table class="attendance">
                <thead>
                    <tr>
                        <th width="25px" class="center">NO.</th>
                        <th width="95px" class="center">NOMOR PESERTA</th>
                        <th>NAMA PESERTA</th>
                        <th width="180px">PROGRAM STUDI</th>
                        <th width="120px" class="center">TANDA TANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($group['participants'] as $p): ?>
                        <tr>
                            <td class="center" align="center"><?php echo $no++; ?></td>
                            <td class="center" align="center"><?php echo $p['nomor_peserta']; ?></td>
                            <td><?php echo strtoupper($p['nama_lengkap']); ?></td>
                            <td style="font-size: 9px; line-height: 1.1;"><?php echo strtoupper($p['nama_prodi']); ?></td>
                            <td height="30px" style="vertical-align: bottom; padding-bottom: 2px;">
                                <?php if (($no - 1) % 2 != 0): ?>
                                    <div style="padding-left: 5px;">
                                        <span style="font-size: 10px; color: #333;"><?php echo $no - 1; ?>.
                                            ...................</span>
                                    </div>
                                <?php else: ?>
                                    <div style="padding-left: 50px;">
                                        <span style="font-size: 10px; color: #333;"><?php echo $no - 1; ?>.
                                            ...................</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Signature -->
            <div class="signature-area">
                <p style="margin-bottom: 5px;">Banjarmasin, <?php echo $group['tanggal']; ?></p>
                <p style="margin-top: 0; margin-bottom: 55px;"><strong>Pengawas Ujian</strong></p>
                <div style="display: inline-block; width: 220px; padding-top: 5px;">
                    ( ....................................... )
                </div>
            </div>
        </page>
    <?php endforeach; ?>
</body>

</html>