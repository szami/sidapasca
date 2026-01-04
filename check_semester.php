<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db.php';

$db = \App\Utils\Database::connection();

echo "=== Struktur Tabel Semesters ===\n\n";

$semesters = $db->query('SELECT * FROM semesters ORDER BY id DESC LIMIT 5')->fetchAll();

foreach ($semesters as $sem) {
    print_r($sem);
    echo "\n---\n";
}
