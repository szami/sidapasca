<?php
$dbPath = __DIR__ . '/storage/database.sqlite';
try {
    $pdo = new PDO("sqlite:$dbPath");
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "\n[TABLE] $table\n";
        $columns = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo " - {$col['name']} ({$col['type']})\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
