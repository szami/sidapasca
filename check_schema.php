<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
    // ignore
}

require __DIR__ . '/config/db.php';
use App\Utils\Database;

try {
    $db = Database::connection();
    $columns = $db->query("PRAGMA table_info(participants)")->fetchAll();

    echo "<h1>Participants Columns</h1><ul>";
    foreach ($columns as $col) {
        echo "<li>" . $col['name'] . " (" . $col['type'] . ")</li>";
    }
    echo "</ul>";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
