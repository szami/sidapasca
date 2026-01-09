<?php
// This view is for printing only - no filter form, just the attendance table
// Maximum rows per page for precise A4 printing (from query param or default 18)
$maxRowsPerPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 18;
$maxRowsPerPage = max(10, min(50, $maxRowsPerPage)); // Limit between 10-50 for safety
?>
<!DOCTYPE html>
<html>

<head>
    <title>DAFTAR HADIR PESERTA UJIAN</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Roboto", Arial, sans-serif;
            font-size: 12px;
            background: #eee;
        }

        /* A4 Page Simulation for Screen Preview */
        page[size="A4"] {
            background: white;
            width: 210mm;
            min-height: 297mm;
            display: block;
            margin: 10px auto;
            padding: 15mm 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        /* Print Styles - Critical for A4 Precision */
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
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

        table {
            width: 99.8%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        table.attendance th,
        table.attendance td {
            border: 1px solid #333;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
        }

        table.attendance th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .center,
        table.attendance td.center {
            text-align: center !important;
        }

        .signature-area {
            margin-top: 30px;
            width: 50%;
            margin-left: auto;
            text-align: center;
        }

        .page-indicator {
            font-size: 10px;
            color: #666;
            text-align: right;
            margin-bottom: 5px;
        }

        .continuation-note {
            font-size: 10px;
            color: #666;
            font-style: italic;
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
    // Group participants by Room and Session (same as CAT schedule)
    $groups = [];
    if (isset($participants) && is_array($participants)) {
        foreach ($participants as $p) {
            $key = ($p['ruang_ujian'] ?? 'Semua') . ' - ' . ($p['sesi_ujian'] ?? 'Semua');
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'ruang' => $p['ruang_ujian'] ?? '-',
                    'gedung' => $p['gedung'] ?? '-',
                    'sesi' => $p['sesi_ujian'] ?? '-',
                    'tanggal' => $p['tanggal_formatted'] ?? ($p['tanggal_ujian'] ?? '-'),
                    'waktu' => $p['waktu_ujian'] ?? '-',
                    'participants' => []
                ];
            }
            $groups[$key]['participants'][] = $p;
        }
    }
    ?>

    <?php foreach ($groups as $key => $group): ?>
        <?php
        // Split participants into chunks of max rows per page
        $participantChunks = array_chunk($group['participants'], $maxRowsPerPage);
        $totalPages = count($participantChunks);
        $totalParticipants = count($group['participants']);
        $globalNo = 0; // Global numbering across pages for this group
        ?>

        <?php foreach ($participantChunks as $pageIndex => $chunk): ?>
            <?php
            $currentPage = $pageIndex + 1;
            $isLastPage = ($currentPage === $totalPages);
            ?>
            <page size="A4">
                <!-- Page Indicator (if multiple pages) -->
                <?php if ($totalPages > 1): ?>
                    <div class="page-indicator">
                        Halaman <?php echo $currentPage; ?> dari <?php echo $totalPages; ?>
                        (Total: <?php echo $totalParticipants; ?> peserta)
                    </div>
                <?php endif; ?>

                <!-- Letterhead (Dynamic from Settings) -->
                <?php if (!empty($letterhead)): ?>
                    <?php echo $letterhead; ?>
                <?php else: ?>
                    <!-- Default Letterhead if not set -->
                    <table class="header" width="100%">
                        <tbody>
                            <tr>
                                <td width="100px" align="center">
                                    <img src="https://simari.ulm.ac.id/logo/ulm.png" alt="Logo ULM" width="80px">
                                </td>
                                <td align="center">
                                    <b style="font-size:16px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                                    <b style="font-size:18px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                                    <b style="font-size:22px;">ADMISI PASCASARJANA</b><br>
                                    <span style="font-size:10px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin,
                                        Kalimantan Selatan 70123</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                <hr style="border: 1px solid #000; margin: 10px 0;">

                <!-- Title (repeated on each page) -->
                <div align="center" style="font-weight:bold; margin-bottom: 10px;">
                    <div style="font-size:15px;">DAFTAR HADIR PESERTA</div>
                    <div style="font-size:15px;">TES UJIAN MASUK PASCASARJANA</div>
                    <?php if ($currentPage > 1): ?>
                        <div class="continuation-note">(Lanjutan)</div>
                    <?php endif; ?>
                </div>

                <!-- Info Table (same style as CAT schedule) -->
                <table class="info-table" style="margin-bottom: 15px; font-size: 12px; line-height: 1.6;">
                    <tr>
                        <td width="70px" style="font-weight: 600; color: #333;">Semester</td>
                        <td width="220px">: <strong><?php echo $semesterName ?? '-'; ?></strong></td>
                        <td width="70px" style="font-weight: 600; color: #333;">Gedung</td>
                        <td>: <strong><?php echo htmlspecialchars($group['gedung']); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #333;">Tanggal</td>
                        <td>: <strong><?php echo htmlspecialchars($group['tanggal']); ?></strong></td>
                        <td style="font-weight: 600; color: #333;">Ruang</td>
                        <td>: <strong><?php echo htmlspecialchars($group['ruang']); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #333;">Waktu</td>
                        <td>: <strong><?php echo htmlspecialchars($group['waktu']); ?></strong></td>
                        <td style="font-weight: 600; color: #333;">Sesi</td>
                        <td>: <strong><?php echo htmlspecialchars($group['sesi']); ?></strong></td>
                    </tr>
                </table>

                <!-- Attendance Table -->
                <table class="attendance">
                    <thead>
                        <tr>
                            <th width="5%">NO.</th>
                            <th width="14%">NOMOR PESERTA</th>
                            <th width="28%">NAMA PESERTA</th>
                            <th width="28%">PROGRAM STUDI</th>
                            <th width="25%">TANDA TANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chunk as $participant): ?>
                            <?php $globalNo++; ?>
                            <tr>
                                <td class="center"><?php echo $globalNo; ?></td>
                                <td class="center"><?php echo $participant['nomor_peserta']; ?></td>
                                <td><?php echo strtoupper($participant['nama_lengkap']); ?></td>
                                <td><?php echo strtoupper($participant['nama_prodi']); ?></td>
                                <td style="vertical-align: bottom; padding: 4px 6px;">
                                    <?php if ($globalNo % 2 == 1): ?>
                                        <div style="text-align: left; font-size: 10px;"><?php echo $globalNo; ?>. ..................
                                        </div>
                                    <?php else: ?>
                                        <div style="text-align: center; font-size: 10px; margin-left: 20px;"><?php echo $globalNo; ?>.
                                            ..................</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Signature (only on last page of each group) -->
                <?php if ($isLastPage): ?>
                    <div class="signature-area">
                        <p style="margin-bottom: 5px;">Mengetahui,</p>
                        <p style="margin-top: 0; margin-bottom: 80px;"><strong>Pengawas Ujian</strong></p>
                        <div style="display: inline-block; width: 200px; padding-top: 5px;">
                            ( ....................................... )
                        </div>
                    </div>
                <?php endif; ?>
            </page>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php if (empty($groups)): ?>
        <page size="A4">
            <div style="text-align: center; padding: 50px;">
                <h3>Belum ada data peserta</h3>
                <p>Silakan pilih filter yang sesuai</p>
            </div>
        </page>
    <?php endif; ?>
</body>

</html>