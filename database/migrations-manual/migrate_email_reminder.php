<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Direct database path
$sqlitePath = __DIR__ . '/' . ($_ENV['DB_SQLITE_PATH'] ?? 'storage/database.sqlite');

try {
    $pdo = new PDO(
        "sqlite:" . $sqlitePath,
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = file_get_contents(__DIR__ . '/database/migrations/create_email_reminder_tables.sql');

    $pdo->exec($sql);

    echo "✅ Email reminder tables created successfully!\n";
    echo "Tables: email_configurations, email_templates, email_reminders, email_logs\n";
    echo "Sample templates inserted.\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
