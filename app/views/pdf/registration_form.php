<!DOCTYPE html>
<html>

<head>
    <title>FORMULIR PENDAFTARAN</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: "Roboto";
            font-size: 9px;
            margin: 0;
            padding: 0;
            background: #eee;
        }

        .rotate1 {
            height: 14px;
            font-size: 9px;
            border-bottom: 1px solid #dadada;
            vertical-align: middle;
            padding: 2px 3px;
        }

        /* Responsive A4 Container */
        page[size="A4"] {
            background: white;
            width: 21cm;
            min-height: 29.7cm;
            display: block;
            margin: 0.5cm auto;
            padding: 25px;
            border: 1px solid #dadada;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        @media print {

            body,
            page[size="A4"] {
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                background: white;
                width: 100%;
            }

            .no-print {
                display: none;
            }
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

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 1px 2px;
        }

        /* Reduced padding */
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </div>
    <page size="A4">
        <?php
        // Get letterhead from settings (prefer form specific, fallback to card, then default)
        $letterhead = \App\Models\Setting::get('exam_form_letterhead');
        if (empty($letterhead)) {
            $letterhead = \App\Models\Setting::get('exam_card_letterhead');
        }

        if (empty($letterhead)) {
            // Default letterhead
            $letterhead = '
            <table width="100%" style="border-collapse: collapse; margin-bottom: 2px;">
                <tr>
                    <td width="80px" align="center">
                        <img src="https://simari.ulm.ac.id/logo/ulm.png" width="60px">
                    </td>
                    <td align="center">
                        <b style="font-size:12px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                        <b style="font-size:14px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                        <b style="font-size:16px;">ADMISI PASCASARJANA</b><br>
                        <span style="font-size:8px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin, Kalimantan Selatan 70123</span><br>
                        <span style="font-size:8px;">Telp. (0511) 33066003, 3304177, 3306694, 3305195, Kotak Pos 219</span>
                    </td>
                </tr>
            </table>';
        }
        echo $letterhead;
        ?>


        <table width="100%" style="font-weight:bold; margin-top: 5px; margin-bottom: 5px;">
            <tbody>
                <tr>
                    <td align="center" style="font-size:14px;">Formulir Pendaftaran</td>
                </tr>
            </tbody>
        </table>


        <table width="100%" border="0">
            <tbody>
                <tr valign="top">
                    <!-- Biodata Column -->
                    <td width="80%">
                        <table width="100%" border="0">
                            <!--  Program Studi -->
                            <tr>
                                <td class="rotate1" colspan="3" style="background-color: #f0f0f0; font-weight: bold;">
                                    PROGRAM STUDI PILIHAN</td>
                            </tr>
                            <tr>
                                <td class="rotate1" width="30%"><b>Jenjang</b></td>
                                <td class="rotate1" width="1%" align="center">:</td>
                                <td class="rotate1">
                                    <?php
                                    if (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false)
                                        echo "S3";
                                    elseif (stripos($p['nama_prodi'] ?? '', 'S2') !== false || stripos($p['nama_prodi'] ?? '', 'MAGISTER') !== false)
                                        echo "S2";
                                    else
                                        echo "-";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Pilihan I</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['nama_prodi']; ?>
                                </td>
                            </tr>

                            <!-- Identitas Umum -->
                            <tr>
                                <td class="rotate1" colspan="3" style="background-color: #f0f0f0; font-weight: bold;">
                                    IDENTITAS UMUM</td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Nama</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['nama_lengkap']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Tempat Tanggal Lahir</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php
                                    $tgl = !empty($p['tgl_lahir']) ? date('d-m-Y', strtotime($p['tgl_lahir'])) : '-';
                                    echo ($p['tempat_lahir'] ?? '-') . ', ' . $tgl;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Alamat KTP</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['alamat_ktp'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Kecamatan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['kecamatan'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Kota/Kab</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['kota'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Provinsi</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['provinsi'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Kode Pos</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['kode_pos'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Telpon/HP</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['no_hp'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Agama</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['agama'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Gender</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['jenis_kelamin'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Status Pernikahan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['status_pernikahan'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Pekerjaan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['pekerjaan'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1"><b>Instansi Pekerjaan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['instansi_pekerjaan'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Alamat Pekerjaan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['alamat_pekerjaan'] ?? '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="rotate1" align="right"><b>Telpon Pekerjaan</b></td>
                                <td class="rotate1" align="center">:</td>
                                <td class="rotate1">
                                    <?php echo $p['telpon_pekerjaan'] ?? '-'; ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Photo Column -->
                    <td valign="top" align="center" width="20%">
                        <?php if (!empty($p['photo_filename'])): ?>
                            <?php
                            $baseStorage = dirname(__DIR__, 3) . '/storage';
                            // New Structure
                            $photoPath = $baseStorage . '/' . $p['photo_filename'];

                            // Legacy Structure
                            if (!file_exists($photoPath)) {
                                $photoPath = $baseStorage . '/photos/' . $p['photo_filename'];
                            }

                            if (file_exists($photoPath)):
                                $imageData = base64_encode(file_get_contents($photoPath));
                                $ext = pathinfo($p['photo_filename'], PATHINFO_EXTENSION);
                                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                                ?>
                                <img src="data:<?php echo $mimeType; ?>;base64,<?php echo $imageData; ?>"
                                    style="width: 3cm; height: 4cm; object-fit: cover; border: 1px solid #000; margin-bottom: 10px;">
                            <?php else: ?>
                                <div
                                    style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <span style="line-height: 4cm;">FOTO 3x4</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div
                                style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                <span style="line-height: 4cm;">FOTO 3x4</span>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- New Table for Pendidikan (Standard Flow) -->
        <table width="100%" border="0">
            <!-- Pendidikan S1 -->
            <tr>
                <td class="rotate1" colspan="3" align="center" style="background-color: #f0f0f0; font-weight: bold;">
                    PENDIDIKAN S1</td>
            </tr>
            <tr>
                <td class="rotate1" width="24%"><b>Tahun Masuk</b></td>
                <td class="rotate1" width="1%" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_tahun_masuk'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>Tahun Tamat</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_tahun_tamat'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>Perguruan Tinggi</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_perguruan_tinggi'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>Fakultas</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_fakultas'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>Program Studi</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_prodi'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>IPK</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_ipk'] ?? '-'; ?>
                </td>
            </tr>
            <tr>
                <td class="rotate1"><b>Gelar</b></td>
                <td class="rotate1" align="center">:</td>
                <td class="rotate1">
                    <?php echo $p['s1_gelar'] ?? '-'; ?>
                </td>
            </tr>

            <!-- Pendidikan S2 (Only for S3) -->
            <?php
            $isS3 = (stripos($p['nama_prodi'] ?? '', 'S3') !== false || stripos($p['nama_prodi'] ?? '', 'DOKTOR') !== false);
            if ($isS3):
                ?>
                <tr>
                    <td class="rotate1" colspan="3" align="center" style="background-color: #f0f0f0; font-weight: bold;">
                        PENDIDIKAN S2</td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Tahun Masuk</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_tahun_masuk'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Tahun Tamat</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_tahun_tamat'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Perguruan Tinggi</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_perguruan_tinggi'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Fakultas</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_fakultas'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Program Studi</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_prodi'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>IPK</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_ipk'] ?? '-'; ?>
                    </td>
                </tr>
                <tr>
                    <td class="rotate1"><b>Gelar</b></td>
                    <td class="rotate1" align="center">:</td>
                    <td class="rotate1">
                        <?php echo $p['s2_gelar'] ?? '-'; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>

    </page>
    <script>
        // window.onload = function () { window.print() } 
    </script>
</body>

</html>