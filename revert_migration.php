<?php
$baseDir = __DIR__;
$dbPath = $baseDir . '/storage/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Attempting to drop 'nim' column from 'participants'...\n";

    // Try modern SQLite syntax
    try {
        $pdo->exec("DROP INDEX IF EXISTS idx_participants_nim");
        echo "Index 'idx_participants_nim' dropped.\n";

        $pdo->exec("ALTER TABLE participants DROP COLUMN nim");
        echo "Success: 'nim' column dropped.\n";
    } catch (PDOException $e) {
        // Fallback for older SQLite: Create new table, copy, drop old, rename
        echo "Direct DROP failed ({$e->getMessage()}). Using fallback method...\n";

        $pdo->beginTransaction();

        // 1. Get CREATE statement (without nim)
        // Since we don't know exact schema, parsing schema is hard.
        // But we know 'nim' was the last added.
        // Actually, if we are in this block, preserving data is critical.
        // Simpler approach: List all columns EXCEPT nim
        $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
        $colNames = [];
        foreach ($cols as $col) {
            if ($col['name'] !== 'nim') {
                $colNames[] = $col['name'];
            }
        }
        $colList = implode(', ', $colNames);

        // 2. Create temp table
        // We can rename current table to _old
        $pdo->exec("ALTER TABLE participants RENAME TO participants_old");

        // 3. Re-create participants table (Use migrate logic? Or copy schema?)
        // Copying schema from _old is tricky because it HAS nim.
        // We must construct CREATE statement manually or use the one from `migrate.php`.
        // Let's use a generic subset selection if possible.
        // Actually, safer to just notify user "Cannot simpler revert" if direct drop fails.
        // But let's try to be helpful.

        // Re-read migrate.php? No.
        // Let's abort fallback for now to avoid data loss risk if code is complex.
        $pdo->rollBack();
        echo "Fallback aborted. Please use SQLite 3.35+ or manual GUI.\n";
        exit(1);
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
