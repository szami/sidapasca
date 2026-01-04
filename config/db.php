<?php

use Leaf\Db;

$connection = 'sqlite'; // $_ENV['DB_CONNECTION'] ?? 'sqlite';
$dbName = 'pmb_pps_ulm'; // $_ENV['DB_DATABASE'] ?? 'pmb_pps_ulm';

$config = [
    'dbtype' => $connection,
    'host' => '127.0.0.1',
    'dbname' => $dbName,
    'user' => 'root',
    'password' => '',
];

if ($connection === 'sqlite') {
    // __DIR__ is .../config
    // We want .../storage/database.sqlite
    $sqlitePath = dirname(__DIR__, 1) . '/' . ($_ENV['DB_SQLITE_PATH'] ?? 'storage/database.sqlite');

    // Check dir
    if (!file_exists(dirname($sqlitePath))) {
        mkdir(dirname($sqlitePath), 0755, true);
    }
    // Check file
    if (!file_exists($sqlitePath)) {
        touch($sqlitePath);
    }
    $config = [
        'dbtype' => 'sqlite',
        // Realpath can return false if file doesn't exist, but we touched it.
        // If touch failed (permissions), this fails.
        // Use absolute path directly.
        'dbname' => $sqlitePath,
        'host' => '',
        'user' => '',
        'password' => ''
    ];
}

// Use instance via Helper
\App\Utils\Database::connect($config);
$db = \App\Utils\Database::connection();
