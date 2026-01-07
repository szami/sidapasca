<?php
/**
 * Standalone Migration Script for New Tables
 * Run this to ensure news, guides, and document_verifications tables exist.
 */

$dbPath = __DIR__ . '/storage/database.sqlite';

try {
    if (!file_exists($dbPath)) {
        die("Error: Database file not found at $dbPath\n");
    }

    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting migration...\n";

    // 1. News Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS news (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        content TEXT,
        content_type TEXT,
        image_url TEXT,
        category TEXT,
        is_published BOOLEAN DEFAULT 0,
        published_at DATETIME,
        created_by TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Table 'news' checked/created.\n";

    // 2. Guides Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS guides (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        content TEXT,
        role TEXT,
        order_index INTEGER DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_by TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Table 'guides' checked/created.\n";

    // 3. Document Verifications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS document_verifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        participant_id INTEGER,
        formulir_pendaftaran INTEGER DEFAULT 0,
        formulir_pendaftaran_jumlah INTEGER DEFAULT 0,
        ijazah_s1_legalisir INTEGER DEFAULT 0,
        ijazah_s1_jumlah INTEGER DEFAULT 0,
        transkrip_s1_legalisir INTEGER DEFAULT 0,
        transkrip_s1_jumlah INTEGER DEFAULT 0,
        bukti_pembayaran INTEGER DEFAULT 0,
        bukti_pembayaran_jumlah INTEGER DEFAULT 0,
        surat_rekomendasi INTEGER DEFAULT 0,
        surat_rekomendasi_jumlah INTEGER DEFAULT 0,
        ijazah_s2_legalisir INTEGER DEFAULT 0,
        ijazah_s2_jumlah INTEGER DEFAULT 0,
        transkrip_s2_legalisir INTEGER DEFAULT 0,
        transkrip_s2_jumlah INTEGER DEFAULT 0,
        status_verifikasi_fisik VARCHAR(20) DEFAULT 'pending',
        catatan_admin TEXT,
        verified_by INTEGER,
        verified_at DATETIME,
        bypass_verification INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Table 'document_verifications' checked/created.\n";

    echo "\nMigration finished successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
