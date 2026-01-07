<?php
/**
 * SIDA PASCA - Native PHP Folder Synchronizer (Hostinger Friendly)
 * 
 * Usage: https://sidapasca-ulm.inovasidigital.link/sync.php?token=sidapasca_deploy_2026_xyz
 * 
 * This script copies updated code from 'devsida' folder to current folder
 * WITHOUT using Git or exec(). Safe for Hostinger shared hosting.
 */

// --- CONFIGURATION ---
define('SYNC_TOKEN', 'sidapasca_deploy_2026_xyz');
define('SOURCE_FOLDER', '../devsida'); // Path to development folder
define('TARGET_FOLDER', __DIR__);      // Current folder (production)

// Files and folders to NEVER overwrite or copy
define('EXCLUDE_LIST', [
    '.git',
    '.env',
    'storage/database.sqlite',
    'storage/database.db',
    'storage/backups',
    'storage/photos',
    'storage/documents',
    'storage/20', // Skip semester folders (storage/20241, etc)
    'config/db.php', // Keep production database config
    'version.json',  // Let sync handle versioning
    'sync.php',      // Don't overwrite the script itself
    'deploy.php'
]);

// --- INITIALIZATION ---
set_time_limit(300); // 5 minutes max
$token = $_GET['token'] ?? '';

if (empty($token) || $token !== SYNC_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>Invalid token.</p>');
}

if (!is_dir(SOURCE_FOLDER)) {
    die("<h1>Error</h1><p>Source folder '" . SOURCE_FOLDER . "' not found.</p>");
}

$startTime = microtime(true);
$stats = ['files' => 0, 'dirs' => 0, 'errors' => 0, 'skipped' => 0];
$logs = [];

/**
 * Recursive Copy function with Exclusion
 */
function syncFolders($src, $dst, &$stats, &$logs)
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
        $stats['dirs']++;
    }

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            // Check exclusion
            $relativePath = ltrim(str_replace(SOURCE_FOLDER, '', $srcFile), '/');
            foreach (EXCLUDE_LIST as $exclude) {
                if ($relativePath === $exclude || strpos($relativePath, $exclude . '/') === 0) {
                    $stats['skipped']++;
                    continue 2;
                }
            }

            if (is_dir($srcFile)) {
                syncFolders($srcFile, $dstFile, $stats, $logs);
            } else {
                if (copy($srcFile, $dstFile)) {
                    $stats['files']++;
                } else {
                    $stats['errors']++;
                    $logs[] = "Error copying: $relativePath";
                }
            }
        }
    }
    closedir($dir);
}

// --- EXECUTE SYNC ---
syncFolders(SOURCE_FOLDER, TARGET_FOLDER, $stats, $logs);

// --- TRIGGER MIGRATION ---
// We include the migration file directly to run it within the same process
$migrationFile = TARGET_FOLDER . '/app/database/migrations/migrate.php';
$migrationStatus = "Not found";
if (file_exists($migrationFile)) {
    try {
        ob_start();
        include $migrationFile;
        $migrationOutput = ob_get_clean();
        $migrationStatus = "Success";
    } catch (Exception $e) {
        $migrationStatus = "Failed: " . $e->getMessage();
    }
}

$duration = round(microtime(true) - $startTime, 2);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SIDA PASCA - Native Sync Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            padding: 40px 10px;
            font-family: sans-serif;
        }

        .card {
            max-width: 700px;
            margin: 0 auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: #6610f2;
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: bold;
        }

        .stat-box {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
            text-align: center;
        }

        pre {
            background: #212529;
            color: #39FF14;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="card-header p-3">
            <i class="fas fa-sync-alt me-2"></i> Hostinger Native Sync (Dev &rarr; Prod)
        </div>
        <div class="card-body p-4">

            <div class="alert alert-success text-center mb-4">
                <h4 class="alert-heading">Sinkronisasi Selesai!</h4>
                <p class="mb-0">Kode dari folder <strong>devsida</strong> telah berhasil dipindahkan ke folder ini.</p>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-3">
                    <div class="stat-box"><small class="text-muted">FILES</small>
                        <h5>
                            <?php echo $stats['files']; ?>
                        </h5>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box"><small class="text-muted">DIRS</small>
                        <h5>
                            <?php echo $stats['dirs']; ?>
                        </h5>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box"><small class="text-muted">SKIPPED</small>
                        <h5>
                            <?php echo $stats['skipped']; ?>
                        </h5>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-box"><small class="text-muted">TIME</small>
                        <h5>
                            <?php echo $duration; ?>s
                        </h5>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h6>Status Database Migration:</h6>
                <div
                    class="p-2 border rounded <?php echo (strpos($migrationStatus, 'Success') !== false) ? 'bg-success-subtle' : 'bg-warning-subtle'; ?>">
                    <strong>
                        <?php echo $migrationStatus; ?>
                    </strong>
                </div>
            </div>

            <?php if (!empty($logs)): ?>
                <div class="mb-4">
                    <h6>Errors:</h6>
                    <ul class="text-danger small">
                        <?php foreach ($logs as $log)
                            echo "<li>$log</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="mt-4 text-center">
                <a href="/" class="btn btn-primary px-4">Buka Aplikasi</a>
            </div>
        </div>
        <div class="card-footer text-center text-muted small py-2">
            SIDA PASCA Sync Utility v1.0
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>