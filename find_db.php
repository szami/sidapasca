<?php
$files = [
    __DIR__ . '/database.sqlite',
    __DIR__ . '/app/database/database.sqlite',
    __DIR__ . '/storage/database.sqlite'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }

    echo "Checking $file ... ";
    try {
        $pdo = new PDO("sqlite:$file");
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='participants'")->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            echo "FOUND 'participants' table!\n";
            // Check columns
            $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
            echo "Columns: " . implode(', ', array_column($cols, 'name')) . "\n";
        } else {
            echo "Table 'participants' NOT found.\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "--------------------------------------------------\n";
}
