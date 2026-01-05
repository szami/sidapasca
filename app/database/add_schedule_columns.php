<?php
// Migration: Add schedule columns to prodi_quotas table
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../../storage/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if columns exist
    $cols = $pdo->query("PRAGMA table_info(prodi_quotas)")->fetchAll(PDO::FETCH_ASSOC);
    $hasJadwalMulai = false;
    $hasJadwalSelesai = false;
    $hasJamMulai = false;
    $hasJamSelesai = false;

    foreach ($cols as $col) {
        if ($col['name'] === 'jadwal_mulai')
            $hasJadwalMulai = true;
        if ($col['name'] === 'jadwal_selesai')
            $hasJadwalSelesai = true;
        if ($col['name'] === 'jam_mulai')
            $hasJamMulai = true;
        if ($col['name'] === 'jam_selesai')
            $hasJamSelesai = true;
    }

    if (!$hasJadwalMulai) {
        $pdo->exec("ALTER TABLE prodi_quotas ADD COLUMN jadwal_mulai DATE NULL");
        echo "Column 'jadwal_mulai' added.\n";
    } else {
        echo "Column 'jadwal_mulai' already exists.\n";
    }

    if (!$hasJadwalSelesai) {
        $pdo->exec("ALTER TABLE prodi_quotas ADD COLUMN jadwal_selesai DATE NULL");
        echo "Column 'jadwal_selesai' added.\n";
    } else {
        echo "Column 'jadwal_selesai' already exists.\n";
    }

    if (!$hasJamMulai) {
        $pdo->exec("ALTER TABLE prodi_quotas ADD COLUMN jam_mulai VARCHAR(5) DEFAULT '00:00'");
        echo "Column 'jam_mulai' added.\n";
    } else {
        echo "Column 'jam_mulai' already exists.\n";
    }

    if (!$hasJamSelesai) {
        $pdo->exec("ALTER TABLE prodi_quotas ADD COLUMN jam_selesai VARCHAR(5) DEFAULT '23:59'");
        echo "Column 'jam_selesai' added.\n";
    } else {
        echo "Column 'jam_selesai' already exists.\n";
    }

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
