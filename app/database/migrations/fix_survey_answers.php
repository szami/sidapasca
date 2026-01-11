<?php

// Script to Fix Survey Answers Question IDs (Old 86-94 -> New 137-145)
// Run with: php app/database/migrations/fix_survey_answers.php

$baseDir = dirname(__DIR__, 3);
$dbPath = $baseDir . '/storage/database.sqlite';

echo "Target DB: $dbPath\n";

if (!file_exists($dbPath)) {
    echo "Database file not found.\n";
    exit;
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Running IKM Data Fix...\n";

    // Check if we have orphans before updating
    $checkSql = "SELECT COUNT(*) FROM survey_answers WHERE question_id BETWEEN 86 AND 94";
    $countBefore = $pdo->query($checkSql)->fetchColumn();

    if ($countBefore == 0) {
        echo "No data to fix (0 rows with old Question IDs found).\n";
        return;
    }

    echo "Found $countBefore rows to fix.\n";

    // Update orphans: Add 51 to ID to shift from 86-94 to 137-145
    $sql = "UPDATE survey_answers 
            SET question_id = question_id + 51 
            WHERE question_id BETWEEN 86 AND 94";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $count = $stmt->rowCount();

    echo "Successfully updated $count rows in survey_answers.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
