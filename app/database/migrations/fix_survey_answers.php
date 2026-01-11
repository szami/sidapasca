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
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10); // PHP Timeout

    // Fix "database is locked" errors
    $pdo->exec("PRAGMA busy_timeout = 10000;"); // SQLite Wait up to 10s
    // Enable WAL for better concurrency
    $pdo->exec("PRAGMA journal_mode = WAL;");
    $pdo->exec("PRAGMA synchronous = NORMAL;");

    echo "Running IKM Data Fix...\n";

    // Retry Logic for Update
    $max_retries = 5;
    $attempt = 0;
    $success = false;
    $count = 0;

    $checkSql = "SELECT COUNT(*) FROM survey_answers WHERE question_id BETWEEN 86 AND 94";

    while ($attempt < $max_retries && !$success) {
        try {
            // Re-check count inside loop (optional but safer)
            $countBefore = $pdo->query($checkSql)->fetchColumn();

            if ($countBefore == 0) {
                echo "No data to fix (0 rows).\n";
                $success = true;
                break;
            }

            echo "Attempt " . ($attempt + 1) . ": Found $countBefore rows to fix.\n";

            $pdo->beginTransaction();

            $sql = "UPDATE survey_answers 
                    SET question_id = question_id + 51 
                    WHERE question_id BETWEEN 86 AND 94";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $count = $stmt->rowCount();

            $pdo->commit();
            $success = true;
            echo "Successfully updated $count rows in survey_answers.\n";

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $attempt++;
            echo "Lock detected (Attempt $attempt/$max_retries). Retrying in 2s...\n";
            sleep(2);

            if ($attempt >= $max_retries) {
                throw $e; // Throw final error
            }
        }
    }

    echo "Successfully updated $count rows in survey_answers.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
