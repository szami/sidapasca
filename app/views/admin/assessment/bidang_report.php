<?php
/**
 * Laporan Hasil Penilaian Tes Bidang
 * Printable/PDF View for Admin Prodi
 */

$title = 'Hasil Penilaian Tes Bidang';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $title; ?> -
        <?php echo htmlspecialchars($prodiName); ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm 25mm;
        }

        /* Header */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .kop-surat img {
            height: 70px;
            margin-bottom: 10px;
        }

        .kop-surat h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .kop-surat h2 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-surat p {
            font-size: 10pt;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin: 30px 0 20px;
        }

        .doc-title h3 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 20px;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            padding: 3px 0;
            vertical-align: top;
        }

        .info-section td:first-child {
            width: 150px;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11pt;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
        }

        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .data-table td.center {
            text-align: center;
        }

        .data-table td.right {
            text-align: right;
        }

        .status-lulus {
            color: #155724;
            font-weight: bold;
        }

        .status-tidak {
            color: #721c24;
            font-weight: bold;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            width: 250px;
            text-align: center;
        }

        .signature-box .date {
            margin-bottom: 10px;
        }

        .signature-box .title {
            margin-bottom: 60px;
        }

        .signature-box .name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-box .nip {
            font-size: 10pt;
        }

        /* Print Styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .container {
                padding: 0;
            }
        }

        /* Print Button */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .print-controls button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin: 0 5px;
        }

        .btn-print {
            background: #007bff;
            color: #fff;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }

        .btn-print:hover {
            background: #0056b3;
        }

        .btn-back:hover {
            background: #545b62;
        }
    </style>
</head>

<body>
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
        <button class="btn-back" onclick="history.back()">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
    </div>

    <div class="container">
        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h3>Hasil Penilaian Tes Bidang</h3>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <table>
                <tr>
                    <td>Program Studi</td>
                    <td>: <strong>
                            <?php echo htmlspecialchars($prodiName); ?>
                        </strong></td>
                </tr>
                <tr>
                    <td>Semester</td>
                    <td>:
                        <?php echo htmlspecialchars($semester['nama'] ?? '-'); ?>
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>:
                        <?php echo date('d F Y'); ?>
                    </td>
                </tr>
                <?php if ($minimumThreshold > 0): ?>
                    <tr>
                        <td>Nilai Minimum</td>
                        <td>: <strong>
                                <?php echo $minimumThreshold; ?>
                            </strong> poin</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 120px;">No. Peserta</th>
                    <th>Nama Lengkap</th>
                    <?php foreach ($components as $comp): ?>
                        <th style="width: 80px;">
                            <?php echo htmlspecialchars($comp['nama_komponen']); ?>
                        </th>
                    <?php endforeach; ?>
                    <th style="width: 80px;">Total</th>
                    <th style="width: 100px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($participants as $p): ?>
                    <tr>
                        <td class="center">
                            <?php echo $no++; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($p['nomor_peserta'] ?? '-'); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                        </td>
                        <?php foreach ($components as $comp): ?>
                            <td class="center">
                                <?php
                                $score = $p['scores'][$comp['id']] ?? '-';
                                echo $score !== '-' ? number_format($score, 0) : '-';
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="center">
                            <strong>
                                <?php echo number_format($p['nilai_bidang_total'] ?? 0, 0); ?>
                            </strong>
                        </td>
                        <td class="center">
                            <?php
                            $status = $p['status_tes_bidang'] ?? null;
                            if ($status === 'lulus') {
                                echo '<span class="status-lulus">LULUS</span>';
                            } elseif ($status === 'tidak_lulus') {
                                echo '<span class="status-tidak">TIDAK LULUS</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($participants)): ?>
                    <tr>
                        <td colspan="<?php echo 5 + count($components); ?>" class="center">
                            <em>Tidak ada data peserta</em>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="info-section" style="margin-top: 20px;">
            <p><strong>Ringkasan:</strong></p>
            <?php
            $totalPeserta = count($participants);
            $lulusCount = 0;
            $tidakLulusCount = 0;
            foreach ($participants as $p) {
                if (($p['status_tes_bidang'] ?? null) === 'lulus')
                    $lulusCount++;
                if (($p['status_tes_bidang'] ?? null) === 'tidak_lulus')
                    $tidakLulusCount++;
            }
            ?>
            <ul style="margin-left: 20px; margin-top: 5px;">
                <li>Total Peserta: <strong>
                        <?php echo $totalPeserta; ?>
                    </strong> orang</li>
                <li>Disarankan Lulus: <strong>
                        <?php echo $lulusCount; ?>
                    </strong> orang</li>
                <li>Tidak Disarankan: <strong>
                        <?php echo $tidakLulusCount; ?>
                    </strong> orang</li>
                <li>Belum Dinilai: <strong>
                        <?php echo $totalPeserta - $lulusCount - $tidakLulusCount; ?>
                    </strong> orang</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-row">
                <div class="signature-box">
                    <p class="date">Banjarmasin,
                        <?php echo date('d F Y'); ?>
                    </p>
                    <p class="title">Koordinator Program Studi,</p>
                    <p class="name">......................................</p>
                    <p class="nip">NIP. ...................................</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Focus print dialog on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>