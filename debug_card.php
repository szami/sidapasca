<?php
// public/debug_card.php

session_start();
require __DIR__ . '/vendor/autoload.php';

// Load Env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

date_default_timezone_set('Asia/Makassar');

echo "<h1>Debug Exam Card Logic</h1>";

if (!isset($_SESSION['user'])) {
    die("<h3>Error: Not Logged In</h3><p>Please login as participant first in another tab, then refresh this page.</p>");
}

$id = $_SESSION['user'];
echo "<p>User ID: $id</p>";

$db = \App\Utils\Database::connection();
$query = "SELECT p.*, r.fakultas as gedung 
          FROM participants p 
          LEFT JOIN exam_rooms r ON p.ruang_ujian = r.nama_ruang 
          WHERE p.id = ?";
$participant = $db->query($query)->bind($id)->first();

if (!$participant) {
    die("<h3>Error: Participant not found in DB</h3>");
}

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Condition</th><th>Value in DB</th><th>Pass/Fail</th></tr>";

// 1. Nomor Peserta
$nomorVal = $participant['nomor_peserta'];
$nomorPass = !empty($nomorVal);
echo "<tr>
    <td>1. Nomor Peserta</td>
    <td>" . htmlspecialchars($nomorVal ?? 'NULL') . "</td>
    <td style='" . ($nomorPass ? 'color:green' : 'color:red') . "'>" . ($nomorPass ? 'PASS' : 'FAIL') . "</td>
</tr>";

// 2. Schedule
$ruang = $participant['ruang_ujian'];
$tgl = $participant['tanggal_ujian'];
$waktu = $participant['waktu_ujian'];
$schedulePass = !empty($ruang) && !empty($tgl) && !empty($waktu);
echo "<tr>
    <td>2. Schedule<br><small>Ruang: " . ($ruang ?? '-') . "<br>Tgl: " . ($tgl ?? '-') . "<br>Waktu: " . ($waktu ?? '-') . "</small></td>
    <td>Has Schedule</td>
    <td style='" . ($schedulePass ? 'color:green' : 'color:red') . "'>" . ($schedulePass ? 'PASS' : 'FAIL') . "</td>
</tr>";

// 3. Physical Verification
$fisikVal = $participant['status_verifikasi_fisik'];
$fisikPass = ($fisikVal === 'lengkap');
echo "<tr>
    <td>3. Physical Verification<br><small>Must be 'lengkap'</small></td>
    <td>" . htmlspecialchars($fisikVal ?? 'NULL') . "</td>
    <td style='" . ($fisikPass ? 'color:green' : 'color:red') . "'>" . ($fisikPass ? 'PASS' : 'FAIL') . "</td>
</tr>";

// 4. Admin Setting
$settingVal = \App\Models\Setting::get('allow_exam_card_download', '0');
$settingPass = ($settingVal == '1');
echo "<tr>
    <td>4. Admin Setting<br><small>allow_exam_card_download</small></td>
    <td>" . htmlspecialchars($settingVal ?? 'NULL') . "</td>
    <td style='" . ($settingPass ? 'color:green' : 'color:red') . "'>" . ($settingPass ? 'PASS' : 'FAIL') . "</td>
</tr>";

echo "</table>";

$allPass = $nomorPass && $schedulePass && $fisikPass && $settingPass;

echo "<h2>Final Result: " . ($allPass ? "<span style='color:green'>CAN DOWNLOAD</span>" : "<span style='color:red'>BLOCKED</span>") . "</h2>";
