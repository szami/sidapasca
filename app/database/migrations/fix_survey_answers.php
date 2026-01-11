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

// function to handle query execution via Leaf or PDO
function executeQuery($dbType, $conn, $sql)
{
    if ($dbType === 'leaf') {
        $conn->query($sql)->execute();
        return true;
    } else {
        return $conn->exec($sql);
    }
}

function fetchOne($dbType, $conn, $sql)
{
    if ($dbType === 'leaf') {
        $res = $conn->query($sql)->fetchAssoc();
        return $res ? array_values($res)[0] : 0;
    } else {
        return $conn->query($sql)->fetchColumn();
    }
}

try {
    $dbType = 'pdo';
    $db = null;

    if (class_exists('App\Utils\Database')) {
        $leafDb = \App\Utils\Database::connection();
        if ($leafDb) {
            $db = $leafDb;
            $dbType = 'leaf';
            echo "Using shared Leaf connection.\n";
        }
    }

    if (!$db) {
        $db = new PDO("sqlite:$dbPath");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_TIMEOUT, 20);
        echo "Created new PDO connection.\n";
    }

    // Settings
    if ($dbType === 'leaf') {
        $db->query("PRAGMA busy_timeout = 20000")->execute();
        $db->query("PRAGMA journal_mode = WAL")->execute();
        $db->query("PRAGMA synchronous = NORMAL")->execute();
    } else {
        $db->exec("PRAGMA busy_timeout = 20000;");
        $db->exec("PRAGMA journal_mode = WAL;");
        $db->exec("PRAGMA synchronous = NORMAL;");
    }

    echo "Running IKM Data Fix...\n";

    // Retry Logic
    $max_retries = 5;
    $attempt = 0;
    $success = false;
    $count = 0;

    // We need to handle three cases now to be absolutely sure:
    // 1. Original Restored Data: IDs 86-94 -> Target 290-298 (+204)
    // 2. Fix v1 Data: IDs 137-145 -> Target 290-298 (+153)
    // 3. Fix v2 Data: IDs 273-281 -> Target 290-298 (+17)

    $checkSqlA = "SELECT COUNT(*) FROM survey_answers WHERE question_id BETWEEN 86 AND 94";
    $updateSqlA = "UPDATE survey_answers SET question_id = question_id + 204 WHERE question_id BETWEEN 86 AND 94";

    $checkSqlB = "SELECT COUNT(*) FROM survey_answers WHERE question_id BETWEEN 137 AND 145";
    $updateSqlB = "UPDATE survey_answers SET question_id = question_id + 153 WHERE question_id BETWEEN 137 AND 145";

    $checkSqlC = "SELECT COUNT(*) FROM survey_answers WHERE question_id BETWEEN 273 AND 281";
    $updateSqlC = "UPDATE survey_answers SET question_id = question_id + 17 WHERE question_id BETWEEN 273 AND 281";

    while ($attempt < $max_retries && !$success) {
        try {
            $countA = fetchOne($dbType, $db, $checkSqlA);
            $countB = fetchOne($dbType, $db, $checkSqlB);
            $countC = fetchOne($dbType, $db, $checkSqlC);

            if ($countA == 0 && $countB == 0 && $countC == 0) {
                echo "No data to fix (0 rows in old ranges).\n";
                $success = true;
                break;
            }

            echo "Attempt " . ($attempt + 1) . ":\n";
            echo " - Found $countA rows in range 86-94 (Original)\n";
            echo " - Found $countB rows in range 137-145 (Fix v1)\n";
            echo " - Found $countC rows in range 273-281 (Fix v2)\n";

            // BEGIN
            if ($dbType === 'leaf') {
                $db->query("BEGIN IMMEDIATE")->execute();
                if ($countA > 0)
                    $db->query($updateSqlA)->execute();
                if ($countB > 0)
                    $db->query($updateSqlB)->execute();
                if ($countC > 0)
                    $db->query($updateSqlC)->execute();
                $db->query("COMMIT")->execute();
            } else {
                $db->beginTransaction();
                if ($countA > 0)
                    $db->exec($updateSqlA);
                if ($countB > 0)
                    $db->exec($updateSqlB);
                if ($countC > 0)
                    $db->exec($updateSqlC);
                $db->commit();
            }

            $success = true;
            echo "Successfully updated rows to target range 290-298.\n";

        } catch (Exception $e) {
            // Rollback
            try {
                if ($dbType === 'leaf') {
                    $db->query("ROLLBACK")->execute();
                } else {
                    if ($db->inTransaction())
                        $db->rollBack();
                }
            } catch (Exception $rbError) { /* ignore rollback error */
            }

            $attempt++;
            echo "Lock/Error detected (Attempt $attempt/$max_retries): " . $e->getMessage() . "\nRetrying in 2s...\n";
            sleep(2);

            if ($attempt >= $max_retries) {
                echo "Final Error: " . $e->getMessage() . "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
