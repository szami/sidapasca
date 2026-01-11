<?php
require 'vendor/autoload.php';

echo "Current Directory: " . getcwd() . "\n";

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "Loaded .env\n";
} catch (\Throwable $e) {
    echo "Failed to load .env: " . $e->getMessage() . "\n";
}

$dbPath = $_ENV['DB_SQLITE_PATH'] ?? 'Not Set';
echo "ENV DB_SQLITE_PATH: " . $dbPath . "\n";

$realPath = realpath($dbPath);
echo "Real Path: " . ($realPath ?: 'FALSE') . "\n";

if ($realPath && file_exists($realPath)) {
    echo "File size: " . filesize($realPath) . "\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($realPath)), -4) . "\n";

    try {
        $pdo = new PDO("sqlite:" . $realPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected to PDO!\n";

        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);

        $result = [
            'status' => 'success',
            'db_path' => $realPath,
            'file_size' => filesize($realPath),
            'tables' => $tables,
            'participants_exists' => in_array('participants', $tables),
            'participants_count' => in_array('participants', $tables) ? $pdo->query("SELECT count(*) FROM participants")->fetchColumn() : 0,
            'user_count' => in_array('users', $tables) ? $pdo->query("SELECT count(*) FROM users")->fetchColumn() : 0,
            'semesters' => in_array('semesters', $tables) ? $pdo->query("SELECT * FROM semesters")->fetchAll(PDO::FETCH_ASSOC) : [],
            'active_semester' => in_array('semesters', $tables) ? $pdo->query("SELECT * FROM semesters WHERE is_active=1")->fetch(PDO::FETCH_ASSOC) : null,
            'participants_by_semester' => in_array('participants', $tables) ? $pdo->query("SELECT semester_id, count(*) as total FROM participants GROUP BY semester_id")->fetchAll(PDO::FETCH_ASSOC) : [],
            'integrity' => $pdo->query("PRAGMA integrity_check")->fetchColumn()
        ];
        file_put_contents('debug_result.json', json_encode($result, JSON_PRETTY_PRINT));
        echo "RESULT_WRITTEN"; // signal to us

    } catch (\Throwable $e) {
        echo "JSON_START" . json_encode(['status' => 'error', 'message' => $e->getMessage()]) . "JSON_END";
    }
} else {
    echo "JSON_START" . json_encode(['status' => 'error', 'message' => "File not found at $dbPath"]) . "JSON_END";
}
