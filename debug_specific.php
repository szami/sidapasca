<?php
// debug_specific.php
require __DIR__ . '/vendor/autoload.php';

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
}

// DB Config
require __DIR__ . '/config/db.php';

$nomor = '20252121062';

// Buffer output
ob_start();

echo "Checking Participant: $nomor\n";
echo "------------------------------------------------\n";

// 1. Get Participant Data
$db = \App\Utils\Database::connection();
$p = $db->query("SELECT * FROM participants WHERE nomor_peserta = '$nomor'")->fetchAssoc();

if (!$p) {
    echo "x Participant NOT FOUND.\n";
} else {
    // 2. Check Conditions
    echo "1. Nomor Peserta: [" . ($p['nomor_peserta'] ? 'PASS' : 'FAIL') . "] Value: " . ($p['nomor_peserta'] ?? 'NULL') . "\n";

    $hasSchedule = !empty($p['ruang_ujian']) && !empty($p['tanggal_ujian']) && !empty($p['waktu_ujian']);
    echo "2. Schedule:      [" . ($hasSchedule ? 'PASS' : 'FAIL') . "]\n";
    echo "   - Ruang: " . ($p['ruang_ujian'] ?? 'NULL') . "\n";
    echo "   - Tanggal: " . ($p['tanggal_ujian'] ?? 'NULL') . "\n";
    echo "   - Waktu: " . ($p['waktu_ujian'] ?? 'NULL') . "\n";

    $fisikVal = $p['status_verifikasi_fisik'];
    echo "3. Physic Verify: [" . ($fisikVal === 'lengkap' ? 'PASS' : 'FAIL') . "] Value: " . ($fisikVal ?? 'NULL') . "\n";

    $settingVal = \App\Models\Setting::get('allow_exam_card_download', '0');
    echo "4. Admin Setting: [" . ($settingVal == '1' ? 'PASS' : 'FAIL') . "] Value: " . ($settingVal ?? 'NULL') . "\n";

    echo "------------------------------------------------\n";
    if ($p['nomor_peserta'] && $hasSchedule && $fisikVal === 'lengkap' && $settingVal == '1') {
        echo "RESULT: [OK] SHOULD BE ABLE TO DOWNLOAD\n";
    } else {
        echo "RESULT: [BLOCKED] CANNOT DOWNLOAD\n";
    }
}

$output = ob_get_clean();
file_put_contents('debug_result.log', $output);
echo $output;
