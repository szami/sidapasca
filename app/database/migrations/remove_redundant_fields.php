<?php
/**
 * Migration: Remove Redundant Fields from participants table
 * 
 * Fields to remove:
 * - alamat (duplicate of alamat_ktp)
 * - asal_mk (unused)
 * - s1_universitas (duplicate of s1_perguruan_tinggi)
 * - s1_tahun_lulus (duplicate of s1_tahun_tamat)
 * - asal_s2 (duplicate of s2_perguruan_tinggi)
 * 
 * Run with: php app/database/migrations/remove_redundant_fields.php
 */

$dbPath = __DIR__ . '/../../../storage/database.sqlite';

if (!file_exists($dbPath)) {
    echo "Database not found at: $dbPath\n";
    exit(1);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Removing Redundant Fields from participants table ===\n\n";

    // SQLite doesn't support DROP COLUMN directly, need to recreate the table
    // First, get the current table structure
    $columns = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);

    $fieldsToRemove = ['alamat', 'asal_mk', 's1_universitas', 's1_tahun_lulus', 'asal_s2'];

    // Check which fields exist
    $existingFields = array_column($columns, 'name');
    $fieldsToActuallyRemove = array_intersect($fieldsToRemove, $existingFields);

    if (empty($fieldsToActuallyRemove)) {
        echo "No redundant fields found to remove. Database is already clean.\n";
        exit(0);
    }

    echo "Fields to remove:\n";
    foreach ($fieldsToActuallyRemove as $field) {
        echo "  - $field\n";
    }
    echo "\n";

    // Create backup first
    $backupPath = __DIR__ . '/../../../storage/backup_before_cleanup_' . date('Y-m-d_His') . '.sqlite';
    copy($dbPath, $backupPath);
    echo "✓ Created backup at: $backupPath\n\n";

    // Build new column list (excluding removed fields)
    $newColumns = [];
    $columnDefs = [];
    foreach ($columns as $col) {
        if (!in_array($col['name'], $fieldsToRemove)) {
            $newColumns[] = $col['name'];

            // Build column definition
            $def = $col['name'] . ' ' . ($col['type'] ?: 'TEXT');
            if ($col['pk']) {
                $def .= ' PRIMARY KEY';
                if ($col['name'] === 'id') {
                    $def = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
                }
            }
            if ($col['notnull']) {
                $def .= ' NOT NULL';
            }
            if ($col['dflt_value'] !== null) {
                $def .= ' DEFAULT ' . $col['dflt_value'];
            }
            $columnDefs[] = $def;
        }
    }

    $pdo->beginTransaction();

    try {
        // 1. Create new table
        $createSql = "CREATE TABLE participants_new (\n    " . implode(",\n    ", $columnDefs) . "\n)";
        $pdo->exec($createSql);
        echo "✓ Created new table structure\n";

        // 2. Copy data
        $columnList = implode(', ', $newColumns);
        $pdo->exec("INSERT INTO participants_new ($columnList) SELECT $columnList FROM participants");
        echo "✓ Copied data to new table\n";

        // 3. Drop old table
        $pdo->exec("DROP TABLE participants");
        echo "✓ Dropped old table\n";

        // 4. Rename new table
        $pdo->exec("ALTER TABLE participants_new RENAME TO participants");
        echo "✓ Renamed new table to participants\n";

        // 5. Recreate indexes if any
        // (Add index recreation here if needed)

        $pdo->commit();
        echo "\n✅ Migration completed successfully!\n";
        echo "Removed " . count($fieldsToActuallyRemove) . " redundant fields.\n";

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
