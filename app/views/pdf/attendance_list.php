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

        table {
            font-family: "Roboto", Arial, sans-serif;
            font-size: 11px;
        }

        table.header {
            font-size: 11px;
            color: #333333;
            border-collapse: collapse;
        }

        table.header td {
            padding: 8px;
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
                width: 210mm;
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
        }

        .btn-print {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-family: sans-serif;
            text-decoration: none;
            margin: 0 5px;
        }

        .btn-print:hover {
            background: #0056b3;
        }

        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        /* Filter form styling */
        .filter-form {
            background: white;
            padding: 20px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form h3 {
            margin-top: 0;
            font-family: sans-serif;
        }

        .filter-form form {
            font-family: sans-serif;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }

        .btn-submit:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <?php
    // Maximum rows per page for precise A4 printing (from query param or default 18)
    $maxRowsPerPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 18;
    $maxRowsPerPage = max(10, min(35, $maxRowsPerPage)); // Limit between 10-35 for safety
    
    // Prepare data chunks
    $participantChunks = !empty($participants) ? array_chunk($participants, $maxRowsPerPage) : [[]];
    $totalPages = count($participantChunks);
    $totalParticipants = count($participants ?? []);

    // Current perPage for form selection
    $currentPerPage = $maxRowsPerPage;
    ?>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan sebagai PDF</button>

        <!-- Filter Form -->
        <div class="filter-form">
            <h3>Filter Daftar Hadir</h3>
            <form method="GET" action="/admin/attendance-print">
                <div class="form-group">
                    <label>Sesi Ujian:</label>
                    <select name="sesi">
                        <option value="all" <?php echo ($filterSesi ?? 'all') === 'all' ? 'selected' : ''; ?>>Semua Sesi
                        </option>
                        <?php if (!empty($sessions)): ?>
                            <?php foreach ($sessions as $session): ?>
                                <option value="<?php echo htmlspecialchars($session); ?>" <?php echo (is_string($filterSesi ?? '') && $filterSesi === $session) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($session); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ruang Ujian:</label>
                    <select name="ruang">
                        <option value="all" <?php echo ($filterRuang ?? 'all') === 'all' ? 'selected' : ''; ?>>Semua
                            Ruangan</option>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room); ?>" <?php echo (is_string($filterRuang ?? '') && $filterRuang === $room) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jumlah Data per Halaman:</label>
                    <select name="perPage">
                        <option value="15" <?php echo $currentPerPage == 15 ? 'selected' : ''; ?>>15 baris</option>
                        <option value="18" <?php echo $currentPerPage == 18 ? 'selected' : ''; ?>>18 baris (default)
                        </option>
                        <option value="20" <?php echo $currentPerPage == 20 ? 'selected' : ''; ?>>20 baris</option>
                        <option value="25" <?php echo $currentPerPage == 25 ? 'selected' : ''; ?>>25 baris</option>
                        <option value="30" <?php echo $currentPerPage == 30 ? 'selected' : ''; ?>>30 baris</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    Tampilkan Daftar Hadir
                </button>
            </form>
        </div>
    </div>

    <?php
    // Group participants by Room and Session (same as CAT schedule)
    $groups = [];
    if (!empty($participants)) {
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
                                        Kalimantan Selatan 70123</span><br>
                                    <span style="font-size:10px;">Telp. (0511) 33066003, 3304177, 3306694, 3305195, Kotak Pos
                                        219</span>
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
                            <th width="15%">NOMOR PESERTA</th>
                            <th width="30%">NAMA PESERTA</th>
                            <th width="30%">PROGRAM STUDI</th>
                            <th width="20%">TANDA TANGAN</th>
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
                                <td></td>
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