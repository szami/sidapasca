<?php
require __DIR__ . '/vendor/autoload.php';

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
}

// Connect DB
require __DIR__ . '/config/db.php';

use App\Utils\Database;

$db = Database::connection();

// Check Survey 3
$survey = $db->query("SELECT * FROM surveys WHERE id = 3")->first();

if (!$survey) {
    echo "Survey ID 3 NOT FOUND!\n";
    // Create it if missing?
    echo "Creating Survey 3...\n";
    $db->query("INSERT INTO surveys (id, title, description, target_role, is_active, created_at) VALUES (3, 'Survei Kepuasan Masyarakat', 'Survei Indeks Kepuasan Masyarakat terhadap Layanan Pascasarjana ULM', 'participant', 1, datetime('now'))")->execute();
    $survey = $db->query("SELECT * FROM surveys WHERE id = 3")->first();
}

echo "Target Survey: " . $survey['title'] . "\n";

// Count before
$count = $db->query("SELECT count(*) as c FROM survey_questions WHERE survey_id = 3")->first()['c'];
echo "Questions before delete: $count\n";

// Clear existing data (Responses first due to FK)
try {
    $db->query("DELETE FROM survey_answers WHERE response_id IN (SELECT id FROM survey_responses WHERE survey_id = 3)")->execute();
    $db->query("DELETE FROM survey_responses WHERE survey_id = 3")->execute();
    $db->query("DELETE FROM survey_questions WHERE survey_id = 3")->execute();
} catch (\Throwable $e) {
    echo "DELETE FAILED: " . $e->getMessage() . "\n";
}

// Count after
$count = $db->query("SELECT count(*) as c FROM survey_questions WHERE survey_id = 3")->first()['c'];
echo "Questions after delete: $count\n";

echo "Cleared existing questions and responses.\n";

// Suffix with time to force unique
$suffix = " (SKM " . uniqid() . ")";

// Suffix with time to force unique
$suffix = " (SKM " . uniqid() . ")";

// Questions List (PermenPAN-RB 14/2017)
$questions = [
    // U1: Persyaratan
    ['U1-1', 'Persyaratan', 'Bagaimana kemudahan persyaratan pelayanan yang harus dipenuhi?'],
    ['U1-2', 'Persyaratan', 'Apakah informasi persyaratan pelayanan disampaikan dengan jelas dan transparan?'],

    // U2: Prosedur
    ['U2-1', 'Prosedur', 'Bagaimana kemudahan prosedur/alur pelayanan di Pascasarjana ULM?'],
    ['U2-2', 'Prosedur', 'Apakah prosedur pelayanan yang ditetapkan sederhana dan tidak berbelit-belit?'],

    // U3: Waktu
    ['U3-1', 'Waktu Penyelesaian', 'Bagaimana kecepatan waktu dalam memberikan pelayanan kepada Anda?'],
    ['U3-2', 'Waktu Penyelesaian', 'Apakah penyelesaian pelayanan sesuai dengan standar waktu yang dijanjikan?'],

    // U4: Biaya
    ['U4-1', 'Biaya/Tarif', 'Bagaimana kewajaran biaya/tarif dalam pelayanan? (Sesuai ketentuan/Gratis jika memang tidak dipungut biaya)'],
    ['U4-2', 'Biaya/Tarif', 'Apakah ada pungutan liar (pungli) atau biaya tambahan di luar ketentuan resmi?'],

    // U5: Produk
    ['U5-1', 'Produk Layanan', 'Bagaimana kesesuaian produk/hasil pelayanan dengan yang dijanjikan? (Misal: SK, Ijazah, Transkrip, Jadwal)'],
    ['U5-2', 'Produk Layanan', 'Bagaimana kualitas hasil pelayanan yang Anda terima?'],

    // U6: Kompetensi
    ['U6-1', 'Kompetensi Pelaksana', 'Bagaimana kemampuan/kompetensi petugas dalam memberikan pelayanan?'],
    ['U6-2', 'Kompetensi Pelaksana', 'Apakah petugas cekatan dan memahami tugasnya dalam melayani Anda?'],

    // U7: Perilaku
    ['U7-1', 'Perilaku Pelaksana', 'Bagaimana kesopanan dan keramahan petugas dalam memberikan pelayanan?'],
    ['U7-2', 'Perilaku Pelaksana', 'Apakah petugas bersikap disiplin dan bertanggung jawab?'],

    // U8: Penanganan Pengaduan
    ['U8-1', 'Penanganan Pengaduan', 'Bagaimana kualitas penanganan pengaduan, saran, dan masukan?'],
    ['U8-2', 'Penanganan Pengaduan', 'Apakah tersedia sarana pengaduan yang mudah diakses dan ditindaklanjuti?'],

    // U9: Sarana Prasarana
    ['U9-1', 'Sarana dan Prasarana', 'Bagaimana kualitas sarana dan prasarana pendukung pelayanan? (Ruang tunggu, Toilet, Web/Aplikasi, dll)'],
    ['U9-2', 'Sarana dan Prasarana', 'Apakah lingkungan pelayanan terasa nyaman, bersih, dan aman?'],
];

echo "QUEUED " . count($questions) . " entries with unique codes\n";

$order = 1;
foreach ($questions as $q) {
    echo "Inserting " . $q[0] . "... ";
    try {
        $db->query("INSERT OR IGNORE INTO survey_questions (survey_id, code, category, question_text, order_num, created_at) VALUES (?, ?, ?, ?, ?, ?)")
            ->bind([3, $q[0], $q[1], $q[2], $order++, date('Y-m-d H:i:s')])
            ->execute();
        echo "OK\n";
    } catch (\Throwable $e) {
        echo "FAIL: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
