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

        page[size="A4"] {
            background: white;
            width: 21cm;
            height: 29.7cm;
            display: block;
            margin: 0 auto;
            padding: 25px;
            margin-bottom: 0.5cm;
            border: 1px solid #dadada;
        }

        @media print {

            body,
            page[size="A4"] {
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }

        body {
            background: #eee;
        }

        .no-print {
            text-align: center;
            padding: 20px;
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
        }

        .btn-print:hover {
            background: #0056b3;
        }

        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.attendance th,
        table.attendance td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        table.attendance th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        table.attendance td.center {
            text-align: center;
        }

        .signature-area {
            margin-top: 40px;
            width: 50%;
            margin-left: auto;
            text-align: center;
        }

        .signature-line {
            margin-top: 80px;
            border-top: 1px solid #333;
            display: inline-block;
            width: 200px;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan sebagai PDF</button>

        <!-- Filter Form -->
        <div
            style="background: white; padding: 20px; margin: 20px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; font-family: sans-serif;">Filter Daftar Hadir</h3>
            <form method="GET" action="/admin/attendance-list" style="font-family: sans-serif;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Sesi Ujian:</label>
                    <select name="sesi" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
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

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Ruang Ujian:</label>
                    <select name="ruang" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
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

                <button type="submit"
                    style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 14px;">
                    Tampilkan Daftar Hadir
                </button>
            </form>
        </div>
    </div>
    <page size="A4">
        <!-- Letterhead (Dynamic from Settings) -->
        <?php if (!empty($letterhead)): ?>
            <?php echo $letterhead; ?>
        <?php else: ?>
            <!-- Default Letterhead if not set -->
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
                            <span style="font-size:11px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin,
                                Kalimantan Selatan 70123</span><br>
                            <span style="font-size:11px;">Telp. (0511) 33066003, 3304177, 3306694, 3305195, Kotak Pos
                                219</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
        <hr style="border: 1px solid #000;">

        <!-- Title -->
        <table width="100%" style="font-weight:bold; margin-top: 20px;">
            <tbody>
                <tr>
                    <td align="center" style="font-size:16px;">DAFTAR HADIR PESERTA</td>
                </tr>
                <tr>
                    <td align="center" style="font-size:16px;">TES UJIAN MASUK PASCASARJANA</td>
                </tr>
                <tr>
                    <td align="center" style="font-size:14px; font-weight:normal; padding-top: 10px;">
                        Semester: <?php echo $semesterName ?? '-'; ?>
                    </td>
                </tr>
                <?php if (($filterSesi ?? 'all') !== 'all' || ($filterRuang ?? 'all') !== 'all'): ?>
                    <tr>
                        <td align="center" style="font-size:12px; font-weight:normal; padding-top: 5px;">
                            <?php if (($filterSesi ?? 'all') !== 'all'): ?>
                                <strong>Sesi:</strong> <?php echo htmlspecialchars($filterSesi); ?>
                            <?php endif; ?>
                            <?php if (($filterSesi ?? 'all') !== 'all' && ($filterRuang ?? 'all') !== 'all'): ?>
                                |
                            <?php endif; ?>
                            <?php if (($filterRuang ?? 'all') !== 'all'): ?>
                                <strong>Ruang:</strong> <?php echo htmlspecialchars($filterRuang); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Attendance Table -->
        <table class="attendance">
            <thead>
                <tr>
                    <th width="30px">NO.</th>
                    <th width="100px">NOMOR PESERTA</th>
                    <th>NAMA PESERTA</th>
                    <th width="200px">PROGRAM STUDI</th>
                    <th width="120px">TANDA TANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($participants)): ?>
                    <?php $no = 1;
                    foreach ($participants as $participant): ?>
                        <tr>
                            <td class="center">
                                <?php echo $no++; ?>
                            </td>
                            <td class="center">
                                <?php echo $participant['nomor_peserta']; ?>
                            </td>
                            <td>
                                <?php echo strtoupper($participant['nama_lengkap']); ?>
                            </td>
                            <td><?php echo strtoupper($participant['nama_prodi']); ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="center">Belum ada peserta</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Signature -->
        <div class="signature-area">
            <p style="margin-bottom: 5px;">Mengetahui,</p>
            <p style="margin-top: 0; margin-bottom: 100px;"><strong>Pengawas Ujian</strong></p>
            <div style="border-top: 1px solid #333; display: inline-block; width: 200px; padding-top: 5px;">
                ( ....................................... )
            </div>
        </div>

    </page>
</body>

</html>