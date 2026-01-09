<?php
require __DIR__ . '/vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
}
require __DIR__ . '/config/db.php';
use App\Utils\Database;

$db = Database::connection();
$rows = $db->query("SELECT id, survey_id, code, SUBSTR(question_text, 1, 30) as txt FROM survey_questions")->fetchAll();
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Survey: {$r['survey_id']} | Code: {$r['code']} | Text: {$r['txt']}...\n";
}
