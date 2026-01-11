<?php
require __DIR__ . '/vendor/autoload.php';

// Load Env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (\Throwable $e) {
}

require __DIR__ . '/config/db.php';
use App\Utils\Database;

$db = Database::connection();

echo "<h1>Fixing Schema...</h1>";

function addColumnIfNotExists($db, $table, $column, $definition)
{
    try {
        $cols = $db->query("PRAGMA table_info($table)")->fetchAll();
        $exists = false;
        foreach ($cols as $c) {
            if ($c['name'] === $column) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            echo "Adding $column to $table... ";
            $db->query("ALTER TABLE $table ADD COLUMN $column $definition")->execute();
            echo "Done.<br>";
        } else {
            echo "$column already exists in $table.<br>";
        }
    } catch (\Throwable $e) {
        echo "Error adding $column: " . $e->getMessage() . "<br>";
    }
}

addColumnIfNotExists($db, 'participants', 'tpa_provider', "VARCHAR(100) DEFAULT 'PPKPP ULM'");
addColumnIfNotExists($db, 'participants', 'tpa_certificate_url', "VARCHAR(255) NULL");
addColumnIfNotExists($db, 'exam_rooms', 'google_map_link', "TEXT NULL");

echo "<h1>Schema Fix Complete</h1>";
echo "<a href='/admin/assessment/tpa'>Go to TPA Input</a>";
