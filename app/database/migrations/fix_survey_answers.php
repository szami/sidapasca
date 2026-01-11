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

    // --- DYNAMIC FIX STRATEGY ---
    // Instead of hardcoded offsets, we:
    // 1. Get list of VALID Question IDs for Survey 1 (ordered ASC)
    // 2. Get list of ORPHAN Question IDs in survey_answers (ordered ASC)
    // 3. Map them 1-to-1 (assuming order is preserved, which is true for auto-increment)

    // 1. Get Valid IDs
    $validIds = [];
    $sqlValid = "SELECT id FROM survey_questions WHERE survey_id = 1 ORDER BY id ASC";
    if ($dbType === 'leaf') {
        $res = $db->query($sqlValid)->fetchAll();
        $validIds = array_column($res, 'id');
    } else {
        $validIds = $db->query($sqlValid)->fetchAll(PDO::FETCH_COLUMN);
    }

    echo "Valid Question IDs (Target): " . implode(', ', $validIds) . "\n";

    if (count($validIds) !== 9) {
        // Safety check: IKM usually has 9 questions. If not, auto-mapping might be risky.
        // But maybe the user changed questions. Let's just warn.
        echo "Warning: Found " . count($validIds) . " valid questions (Expected 9).\n";
    }

    // 2. Get Orphan IDs
    // Get all distinct question_ids in answers that are NOT in valid set
    $validList = empty($validIds) ? '0' : implode(',', $validIds);
    $sqlOrphans = "SELECT DISTINCT question_id FROM survey_answers WHERE question_id NOT IN ($validList) ORDER BY question_id ASC";

    $orphanIds = [];
    if ($dbType === 'leaf') {
        $res = $db->query($sqlOrphans)->fetchAll();
        $orphanIds = array_column($res, 'question_id');
    } else {
        $orphanIds = $db->query($sqlOrphans)->fetchAll(PDO::FETCH_COLUMN);
    }

    if (empty($orphanIds)) {
        echo "No orphan answers found. Data is already correct/synced.\n";
        $success = true;
        return;
    }

    echo "Found " . count($orphanIds) . " orphan ID groups: " . implode(', ', $orphanIds) . "\n";

    if (count($orphanIds) !== count($validIds)) {
        echo "Critical Warning: Mismatch between Orphan Groups (" . count($orphanIds) . ") and Valid Questions (" . count($validIds) . ").\n";
        echo "Cannot reliably auto-map. Please fetch fresh restore.\n";
        // Try to map up to the count we have? No, safe abort.
        return;
    }

    // 3. Execute Mapping
    echo "Mapping orphans to valid IDs...\n";

    // Retry Logic
    $max_retries = 5;
    $attempt = 0;
    $success = false;
    $count = 0;

    while ($attempt < $max_retries && !$success) {
        try {
            // BEGIN
            if ($dbType === 'leaf') {
                $db->query("BEGIN IMMEDIATE")->execute();

                foreach ($orphanIds as $idx => $oldId) {
                    if (!isset($validIds[$idx]))
                        continue;
                    $newId = $validIds[$idx];
                    $db->query("UPDATE survey_answers SET question_id = $newId WHERE question_id = $oldId")->execute();
                    echo " - Remapped $oldId -> $newId\n";
                }

                $db->query("COMMIT")->execute();
            } else {
                $db->beginTransaction();

                foreach ($orphanIds as $idx => $oldId) {
                    if (!isset($validIds[$idx]))
                        continue;
                    $newId = $validIds[$idx];
                    $db->exec("UPDATE survey_answers SET question_id = $newId WHERE question_id = $oldId");
                    echo " - Remapped $oldId -> $newId\n";
                }

                $db->commit();
            }

            $success = true;
            echo "Successfully remapped all orphans.\n";

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
