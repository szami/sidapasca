<?php
// Migration runner for add_bypass_verification.sql

require __DIR__ . '/vendor/autoload.php';

// Load Env matches index.php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Load DB Config
require __DIR__ . '/config/db.php';

$migrationPath = __DIR__ . '/database/migrations/add_bypass_verification.sql';

try {
    $db = \App\Utils\Database::connection();

    $sql = file_get_contents($migrationPath);

    $db->query($sql)->execute();

    echo "✅ Migration completed successfully!\n";
    echo "Added column: bypass_verification to document_verifications table\n";
    echo "Target Database: " . ($_ENV['DB_DATABASE'] ?? 'unknown') . "\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
