<?php
try {
    $dbPath = __DIR__ . '/database.sqlite';
    if (!file_exists($dbPath)) {
        die("Database file not found at: $dbPath");
    }

    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->query("PRAGMA table_info(participants)");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasCol = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'rekomendasi_filename') {
            $hasCol = true;
            break;
        }
    }

    if (!$hasCol) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN rekomendasi_filename TEXT DEFAULT NULL");
        echo "Column 'rekomendasi_filename' added successfully.";
    } else {
        echo "Column 'rekomendasi_filename' already exists.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
