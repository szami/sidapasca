<?php
// Migration: Add nilai_minimum_bidang column to prodi_quotas table
try {
    // Correct path: storage/database.sqlite
    $pdo = new PDO('sqlite:' . __DIR__ . '/../../storage/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS prodi_quotas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        semester_id INTEGER NOT NULL,
        kode_prodi VARCHAR(10) NOT NULL,
        daya_tampung INTEGER DEFAULT 0,
        nilai_minimum_bidang INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(semester_id, kode_prodi)
    )");
    echo "Table 'prodi_quotas' ensured.\n";

    // Check if column exists (for existing tables)
    $result = $pdo->query("PRAGMA table_info(prodi_quotas)");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN, 1);

    if (!in_array('nilai_minimum_bidang', $columns)) {
        $pdo->exec('ALTER TABLE prodi_quotas ADD COLUMN nilai_minimum_bidang INTEGER DEFAULT 0');
        echo "Column 'nilai_minimum_bidang' added successfully.\n";
    } else {
        echo "Column 'nilai_minimum_bidang' already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
