<!DOCTYPE html>
<html>

<head>
    <title>KARTU UJIAN</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: "Roboto";
            font-size: 7px;
            margin: 0;
            padding: 0;
        }

        .info {
            font-size: 12px;
        }

        table {
            font-family: "Roboto";
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
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </div>
    <page size="A4">
        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php else: ?>
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
            <hr style="border: 1px solid #000;">

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
                            <div
                                style="width: 3cm; height: 4cm; border: 1px solid #000; display: flex; align-items: center; justify-content: center;">
                                <span style="line-height: 4cm; text-align: center;">FOTO 3x4</span>
                            </div>
                        </td>
                        <td style="vertical-align: top;">
                            <table style="font-size:14px; width: 100%;">
                                <tbody>
                                    <tr style="height: 30px;">
                                        <td width="150px">NOMOR PESERTA</td>
                                        <td width="10px">:</td>
                                        <td><b><?php echo $participant['nomor_peserta'] ?? '-'; ?></b></td>
                                    </tr>
                                    <tr style="height: 30px;">
                                        <td>NAMA</td>
                                        <td>:</td>
                                        <td><?php echo strtoupper($participant['nama_lengkap'] ?? '-'); ?></td>
                                    </tr>
                                    <tr style="height: 30px;">
                                        <td>TEMPAT, TGL LAHIR</td>
                                        <td>:</td>
                                        <td>
                                            <?php
                                            $tmpt = $participant['tempat_lahir'] ?? '';
                                            $tgl = !empty($participant['tgl_lahir']) ? date('d F Y', strtotime($participant['tgl_lahir'])) : '-';

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
                                            foreach ($months as $en => $id_mon) {
                                                $tgl = str_replace($en, $id_mon, $tgl);
                                            }

                                            echo strtoupper(trim("$tmpt, $tgl", ", "));
                                            ?>
                                        </td>
                                    </tr>
                                    <tr style="height: 30px;">
                                        <td>PROGRAM STUDI</td>
                                        <td>:</td>
                                        <td><?php echo strtoupper($participant['nama_prodi'] ?? '-'); ?></td>
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
                            <p>
                                <strong>
                                    <?php
                                    $tglUjian = !empty($participant['tanggal_ujian']) ? date('d F Y', strtotime($participant['tanggal_ujian'])) : 'Jadwal Menyusul';
                                    foreach ($months as $en => $id_mon) {
                                        $tglUjian = str_replace($en, $id_mon, $tglUjian);
                                    }
                                    echo $tglUjian;
                                    ?>
                                </strong>
                            </p>
                            <p><strong><?php echo $participant['waktu_ujian'] ?? 'Waktu Menyusul'; ?></strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:14px; padding-top: 10px;">TEMPAT PELAKSANAAN TES :</td>
                    </tr>
                    <tr>
                        <td align="center" style="font-size:14px; font-weight:bold;">
                            <p><strong><?php echo $participant['ruang_ujian'] ?? 'Gedung Pascasarjana ULM'; ?></strong></p>
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
                                <li><strong>Informasi detail lokasi ujian TPA akan diinformasikan melalui email peserta atau
                                        website.</strong></li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 50px; border-top: 2px dashed #333; position: relative;">
                <span
                    style="position: absolute; top: -12px; left: 50%; background: white; padding: 0 10px; font-size: 12px;">Gunting
                    Disini</span>
            </div>
        <?php endif; ?>
    </page>
    <script>
        // window.onload = function () { window.print() } 
    </script>
</body>

</html>