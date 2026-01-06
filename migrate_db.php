<?php
$files = [
    __DIR__ . '/database.sqlite',
    __DIR__ . '/app/database/database.sqlite',
    __DIR__ . '/storage/database.sqlite'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;

    echo "Checking $file ... ";
    try {
        $pdo = new PDO("sqlite:$file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='participants'")->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            echo "FOUND 'participants' table! Attempting migration...\n";

            // Check if column exists
            $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
            $hasCol = false;
            foreach ($cols as $col) {
                if ($col['name'] === 'rekomendasi_filename') {
                    $hasCol = true;
                    break;
                }
            }

            if (!$hasCol) {
                $pdo->exec("ALTER TABLE participants ADD COLUMN rekomendasi_filename TEXT DEFAULT NULL");
                echo "SUCCESS: Column 'rekomendasi_filename' added.\n";
            } else {
                echo "SKIPPING: Column 'rekomendasi_filename' already exists.\n";
            }
            exit(0); // Stop after successful migration on correct DB
        } else {
            echo "Table 'participants' NOT found.\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "Migration failed: Could not find valid database with participants table.\n";
