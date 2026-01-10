<?php
require __DIR__ . '/vendor/autoload.php';

// Load Env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require __DIR__ . '/config/db.php';

use App\Utils\Database;
use App\Models\Semester;

$db = Database::connection();

echo "<h1>Debug Survey Logic</h1>";

// 1. Active Semester
$active = Semester::getActive();
echo "<h2>1. Active Semester</h2>";
if ($active) {
    echo "ID: " . $active['id'] . "<br>";
    echo "Nama: " . $active['nama'] . "<br>";
} else {
    echo "No Active Semester!<br>";
}

// 2. Active Survey
echo "<h2>2. Active Participant Survey</h2>";
$survey = $db->query("SELECT * FROM surveys WHERE target_role = 'participant' AND is_active = 1 LIMIT 1")->fetchAssoc();
if ($survey) {
    echo "Found Survey ID: " . $survey['id'] . "<br>";
    echo "Title: " . $survey['title'] . "<br>";
} else {
    echo "<strong style='color:red'>No Active Survey for Participants Found!</strong><br>";
    echo "Please create a survey in Admin > Survey Kepuasan and make sure Target = Peserta and Active = Yes.<br>";
}

// 3. Exam Attendances
echo "<h2>3. Exam Attendances (Present)</h2>";
$attendances = $db->query("SELECT * FROM exam_attendances WHERE is_present = 1 AND semester_id = ?", [$active['id']])->fetchAll();
echo "Count Present: " . count($attendances) . "<br>";

if (count($attendances) > 0) {
    echo "<h3>Sample Present Participants:</h3>";
    foreach (array_slice($attendances, 0, 5) as $att) {
        $p = $db->query("SELECT id, nama_lengkap, nomor_peserta FROM participants WHERE id = ?", [$att['participant_id']])->first();
        echo "Participant ID: " . $att['participant_id'] . " | Name: " . ($p['nama_lengkap'] ?? 'Unknown') . " | Nomor: " . ($p['nomor_peserta'] ?? '-') . "<br>";

        // Check Response
        if ($survey) {
            $resp = $db->query("SELECT * FROM survey_responses WHERE survey_id = ? AND user_id = ?", [$survey['id'], $att['participant_id']])->first();
            echo " - Survey Response: " . ($resp ? "Submitted (ID: {$resp['id']})" : "<strong style='color:green'>Not Submitted (Should see survey)</strong>") . "<br>";
        }
    }
} else {
    echo "No participants marked as present yet.<br>";
}
