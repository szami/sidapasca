<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan PMB Pascasarjana</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 5px 0 0;
            font-size: 14pt;
            font-weight: normal;
        }

        .meta {
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 10px;
        }

        .data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .data-table td.center {
            text-align: center;
        }

        .data-table td.right {
            text-align: right;
        }

        .section-title {
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 12pt;
            font-weight: bold;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10pt;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            width: 250px;
            margin-top: 20px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <div class="header">
        <!-- Dynamic Letterhead -->
        <?php echo $letterhead; ?>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <h2 style="margin: 0; font-size: 14pt; text-transform: uppercase;">Rekapitulasi Admisi Pascasarjana</h2>
    </div>

    <div class="meta">
        <strong>Periode Data:</strong> <?php echo htmlspecialchars($semesterName); ?><br>
        <strong>Update Data:</strong> <?php echo $generatedAt; ?> WITA
    </div>

    <!-- S2 Section -->
    <?php if (!empty($s2Stats)): ?>
        <div class="section-title">Program Magister (S2)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 35%;">Program Studi</th>
                    <th rowspan="2" style="width: 10%;">Total Pendaftar</th>
                    <th colspan="3">Status Pemberkasan</th>
                    <th colspan="2">Status Pembayaran</th>
                </tr>
                <tr>
                    <th>Formulir Masuk (Pending)</th>
                    <th>Gagal</th>
                    <th>Lulus</th>
                    <th>Lunas</th>
                    <th>Belum</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalS2 = 0;
                $lulusS2 = 0;
                $pendingS2 = 0;
                $gagalS2 = 0;
                $paidS2 = 0;
                $unpaidS2 = 0;
                $no = 1;
                foreach ($s2Stats as $stat):
                    $statUnpaid = isset($stat['unpaid']) ? $stat['unpaid'] : ($stat['total'] - $stat['paid']);
                    $totalS2 += $stat['total'];
                    $lulusS2 += $stat['lulus'];
                    $pendingS2 += $stat['pending'];
                    $gagalS2 += $stat['gagal'];
                    $paidS2 += $stat['paid'];
                    $unpaidS2 += $statUnpaid;
                    ?>
                    <tr>
                        <td class="center"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($stat['nama_prodi']); ?></td>
                        <td class="center"><?php echo $stat['total']; ?></td>
                        <td class="center"><?php echo $stat['pending']; ?></td>
                        <td class="center"><?php echo $stat['gagal']; ?></td>
                        <td class="center"><?php echo $stat['lulus']; ?></td>
                        <td class="center"><?php echo $stat['paid']; ?></td>
                        <td class="center"><?php echo $statUnpaid; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="2">Total Magister</td>
                    <td class="center"><?php echo $totalS2; ?></td>
                    <td class="center"><?php echo $pendingS2; ?></td>
                    <td class="center"><?php echo $gagalS2; ?></td>
                    <td class="center"><?php echo $lulusS2; ?></td>
                    <td class="center"><?php echo $paidS2; ?></td>
                    <td class="center"><?php echo $unpaidS2; ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- S3 Section -->
    <?php if (!empty($s3Stats)): ?>
        <div class="section-title">Program Doktor (S3)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%;">No</th>
                    <th rowspan="2" style="width: 35%;">Program Studi</th>
                    <th rowspan="2" style="width: 10%;">Total Pendaftar</th>
                    <th colspan="3">Status Pemberkasan</th>
                    <th colspan="2">Status Pembayaran</th>
                </tr>
                <tr>
                    <th>Formulir Masuk (Pending)</th>
                    <th>Gagal</th>
                    <th>Lulus</th>
                    <th>Lunas</th>
                    <th>Belum</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalS3 = 0;
                $lulusS3 = 0;
                $pendingS3 = 0;
                $gagalS3 = 0;
                $paidS3 = 0;
                $unpaidS3 = 0;
                $no = 1;
                foreach ($s3Stats as $stat):
                    $statUnpaid = isset($stat['unpaid']) ? $stat['unpaid'] : ($stat['total'] - $stat['paid']);
                    $totalS3 += $stat['total'];
                    $lulusS3 += $stat['lulus'];
                    $pendingS3 += $stat['pending'];
                    $gagalS3 += $stat['gagal'];
                    $paidS3 += $stat['paid'];
                    $unpaidS3 += $statUnpaid;
                    ?>
                    <tr>
                        <td class="center"><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($stat['nama_prodi']); ?></td>
                        <td class="center"><?php echo $stat['total']; ?></td>
                        <td class="center"><?php echo $stat['pending']; ?></td>
                        <td class="center"><?php echo $stat['gagal']; ?></td>
                        <td class="center"><?php echo $stat['lulus']; ?></td>
                        <td class="center"><?php echo $stat['paid']; ?></td>
                        <td class="center"><?php echo $statUnpaid; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="2">Total Doktor</td>
                    <td class="center"><?php echo $totalS3; ?></td>
                    <td class="center"><?php echo $pendingS3; ?></td>
                    <td class="center"><?php echo $gagalS3; ?></td>
                    <td class="center"><?php echo $lulusS3; ?></td>
                    <td class="center"><?php echo $paidS3; ?></td>
                    <td class="center"><?php echo $unpaidS3; ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        Dicetak oleh Administrator pada <?php echo date('d/m/Y H:i'); ?>
    </div>

</body>

</html>