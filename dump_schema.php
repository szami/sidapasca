<?php
require 'vendor/autoload.php';
require 'app/Utils/Database.php';

use App\Utils\Database;

Database::connect([
    'dbtype' => 'sqlite',
    'dbname' => 'storage/database.sqlite'
]);

$db = Database::connection();
echo "--- Tables ---\n";
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
foreach ($tables as $t) {
    echo $t['name'] . "\n";
    $columns = $db->query("PRAGMA table_info(" . $t['name'] . ")")->fetchAll();
    foreach ($columns as $c) {
        echo "  - " . $c['name'] . " (" . $c['type'] . ")\n";
    }
    echo "\n";
}
