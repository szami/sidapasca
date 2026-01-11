<?php
// debug_check_after_fix.php

// Autoload is usually already handled if run via MigrationController
// require __DIR__ . '/vendor/autoload.php';

$baseDir = dirname(__DIR__, 3);
$dbPath = $baseDir . '/storage/database.sqlite';

if (!file_exists($dbPath)) {
    echo "Database not found.\n";
    exit;
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Survey Questions (Survey ID 1) ===\n";
    $stmt = $pdo->query("SELECT id, question_text FROM survey_questions WHERE survey_id = 1");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $qIds = [];
    foreach ($questions as $q) {
        $qIds[] = $q['id'];
        echo "ID: " . $q['id'] . " | Text: " . substr($q['question_text'], 0, 30) . "...\n";
    }

    echo "\n=== Survey Answers Distribution ===\n";
    $stmt = $pdo->query("SELECT question_id, COUNT(*) as count FROM survey_answers GROUP BY question_id");
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matched = 0;
    $unmatched = 0;

    foreach ($answers as $a) {
        $isMatch = in_array($a['question_id'], $qIds);
        if ($isMatch)
            $matched += $a['count'];
        else
            $unmatched += $a['count'];

        echo "Q_ID: " . $a['question_id'] . " | Count: " . $a['count'] . " | Status: " . ($isMatch ? "MATCH" : "ORPHAN") . "\n";
    }

    echo "\nSummary:\n";
    echo "Matched Answers: $matched\n";
    echo "Orphan Answers: $unmatched\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
