<?php
require __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

try {
    // $db = Database::getInstance(); 
    // Quick check using basic PDO 
    $baseDir = __DIR__;
    $dbPath = $baseDir . '/storage/database.sqlite';
    $pdo = new PDO("sqlite:$dbPath");

    echo "=== SEMESTERS DATA ===\n";
    $stmt = $pdo->query("SELECT * FROM semesters");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
