<?php
// Migration runner for document_verifications table

require __DIR__ . '/vendor/autoload.php';

// Load Env matches index.php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Load DB Config
require __DIR__ . '/config/db.php';

$migrationPath = __DIR__ . '/database/migrations/create_document_verifications.sql';

try {
    $db = \App\Utils\Database::connection();

    // Check if we need to get PDO or uses Leaf DB methods
    // Leaf DB exec/query wrapper?
    // Let's use PDO instance if possible, or simple read content and split

    // Leaf DB doesn't expose clean getPdo() easily in the static wrapper? 
    // Utils\Database::connection() returns the Leaf\Db instance.
    // Leaf\Db methods: connect, query, select, etc.
    // We can use $db->query($sql)->execute(); (as validated in Step 368)

    // BUT migration script is raw SQL.
    // Let's use the file_get_contents approach but using the Leaf DB connection

    $sql = file_get_contents($migrationPath);

    $db->query($sql)->execute();

    echo "✅ Migration completed successfully!\n";
    echo "Created table: document_verifications\n";
    echo "Target Database: " . ($_ENV['DB_DATABASE'] ?? 'unknown') . "\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
