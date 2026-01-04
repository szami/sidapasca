<?php

namespace App\Controllers;

use App\Models\Setting;
use Leaf\Http\Request;
use App\Utils\View;

class ExamCardSettingController
{
    protected function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            exit();
        }
    }

    public function design()
    {
        $this->checkAuth();

        $letterhead = Setting::get('exam_card_letterhead', '');
        $layout = Setting::get('exam_card_layout', '');

        // Default letterhead if empty
        if (empty($letterhead)) {
            $letterhead = '
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="100px" align="center">
                        <img src="https://simari.ulm.ac.id/logo/ulm.png" width="80px">
                    </td>
                    <td align="center">
                        <b style="font-size:16px;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</b><br>
                        <b style="font-size:18px;">UNIVERSITAS LAMBUNG MANGKURAT</b><br>
                        <b style="font-size:22px;">ADMISI PASCASARJANA</b><br>
                        <span style="font-size:10px;">Jl. Unlam No.12, Pangeran, Banjarmasin Utara, Kota Banjarmasin, 70123</span>
                    </td>
                </tr>
            </table>';
        }

        // Default layout if empty
        if (empty($layout)) {
            $layout = '
            [kop_surat]
            <hr style="border: 1px solid #000; margin: 10px 0;">
            <div align="center" style="font-weight:bold; margin: 15px 0;">
                <div style="font-size:16px;">KARTU PESERTA</div>
                <div style="font-size:16px;">TES UJIAN MASUK PASCASARJANA</div>
            </div>

            <table width="100%" style="margin-top: 20px;">
                <tr>
                    <td width="150px" align="center" style="vertical-align: top;">
                        <div style="width: 3cm; height: 4cm; border: 1px solid #000; line-height: 4cm; text-align: center;">
                            FOTO 3x4
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <table width="100%" style="font-size:14px;">
                            <tr><td width="140px">NOMOR PESERTA</td><td>:</td><td><b>[nomor_peserta]</b></td></tr>
                            <tr><td>NAMA</td><td>:</td><td>[nama_peserta]</td></tr>
                            <tr><td>TGL LAHIR</td><td>:</td><td>[tgl_lahir]</td></tr>
                            <tr><td>PROGRAM STUDI</td><td>:</td><td>[prodi]</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 30px; text-align: center;">
                <div style="font-size:14px;">WAKTU & TEMPAT PELAKSANAAN :</div>
                <div style="font-size:15px; font-weight:bold; margin-top: 5px;">
                    [tanggal_ujian]<br>
                    [waktu_ujian]
                </div>
                <div style="font-size:15px; font-weight:bold; margin-top: 10px;">
                    [ruang_ujian] / [gedung]
                </div>
            </div>

            <div style="margin-top: 40px; font-size: 11px;">
                <b>Catatan:</b>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Kartu ini wajib dibawa saat pelaksanaan ujian.</li>
                    <li>Peserta diharap hadir 30 menit sebelum ujian dimulai.</li>
                </ul>
            </div>

            <div style="margin-top: 50px; border-top: 2px dashed #333; text-align: center; position: relative;">
                <span style="position: absolute; top: -10px; background: white; padding: 0 10px; font-size: 12px;">Gunting Disini</span>
            </div>';
        }

        echo View::render('admin.exam_card.design', [
            'letterhead' => $letterhead,
            'layout' => $layout
        ]);
    }

    public function saveDesign()
    {
        $this->checkAuth();

        // Retrieve raw data without sanitization
        $letterhead = \Leaf\Http\Request::get('letterhead', false);
        $layout = \Leaf\Http\Request::get('layout', false);

        if ($letterhead !== null) {
            Setting::set('exam_card_letterhead', $letterhead);
        }

        if ($layout !== null) {
            Setting::set('exam_card_layout', $layout);
        }

        response()->redirect('/admin/exam-card/design?msg=success');
    }
}
