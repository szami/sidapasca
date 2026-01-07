<?php
/**
 * SIDA PASCA - Automated Deployment Script
 * 
 * Usage: https://yourdomain.com/deploy.php?token=YOUR_SECRET_TOKEN
 * 
 * This script triggers a Git Pull and Running Database Migrations
 * using the built-in UpdateManager.
 */

// --- CONFIGURATION ---
// IMPORTANT: Change this token to something secure!
define('DEPLOY_TOKEN', 'sidapasca_deploy_2026_xyz');

// --- INITIALIZATION ---
require __DIR__ . '/vendor/autoload.php';

// Bootstrap minimum requirements (Database)
require __DIR__ . '/app/Utils/Database.php';
require __DIR__ . '/config/db.php'; // This connects the DB

use App\Utils\UpdateManager;
use App\Utils\GitHelper;

// Verify Token
$token = $_GET['token'] ?? '';
if (empty($token) || $token !== DEPLOY_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    die('<h1>403 Forbidden</h1><p>Invalid or missing deployment token.</p>');
}

// Background Task Check: Usually we don't want to run this multiple times at once
$lockFile = __DIR__ . '/storage/deploy.lock';
if (file_exists($lockFile)) {
    $lastRun = filemtime($lockFile);
    if ((time() - $lastRun) < 300) { // Lock for 5 minutes
        die('<h1>Update in Progress</h1><p>Another deployment task is already running. Please wait.</p>');
    }
}
touch($lockFile);

$startTime = microtime(true);
$results = [];

try {
    // 1. Initialize Update Manager
    $updateManager = new UpdateManager();

    // 2. Perform Update (Git Pull + Migration)
    // We pass null as userId because it's triggered by system/external
    $results = $updateManager->performUpdate(null);

} catch (Exception $e) {
    $results = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Release Lock
if (file_exists($lockFile)) {
    unlink($lockFile);
}

$duration = round(microtime(true) - $startTime, 2);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SIDA PASCA - Deployment Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 50px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            max-width: 800px;
            margin: 0 auto;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
            background: #007bff;
            color: white;
            padding: 20px;
        }

        .status-badge {
            font-size: 1.2rem;
            padding: 8px 16px;
            border-radius: 50px;
        }

        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .log-item {
            border-left: 4px solid #dee2e6;
            padding-left: 15px;
            margin-bottom: 15px;
        }

        .log-success {
            border-color: #28a745;
        }

        .log-error {
            border-color: #dc3545;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>🚀 SIDA PASCA Deployment</span>
            <span class="badge bg-light text-primary">
                <?php echo date('Y-m-d H:i:s'); ?>
            </span>
        </div>
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <?php if ($results['success']): ?>
                    <div class="status-badge bg-success text-white d-inline-block">
                        <i class="fas fa-check-circle me-2"></i> Update Berhasil!
                    </div>
                    <p class="mt-3 text-muted">Aplikasi telah diperbarui ke versi terbaru di host ini.</p>
                <?php else: ?>
                    <div class="status-badge bg-danger text-white d-inline-block">
                        <i class="fas fa-times-circle me-2"></i> Update Gagal
                    </div>
                    <p class="mt-3 text-danger">
                        <?php echo htmlspecialchars($results['message']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block uppercase mb-1">DURASI</small>
                        <strong>
                            <?php echo $duration; ?> Detik
                        </strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded bg-light">
                        <small class="text-muted d-block uppercase mb-1">STATUS</small>
                        <strong>
                            <?php echo $results['status'] ?? 'Error'; ?>
                        </strong>
                    </div>
                </div>
            </div>

            <h5>Log Output:</h5>
            <div class="p-3 bg-dark rounded text-white font-monospace">
                <?php
                echo "<div class='text-info'>[ENVIRONMENT] Root: " . __DIR__ . "</div>";
                echo "<div class='text-info'>[GIT] Branch: " . (GitHelper::getCurrentBranch() ?: 'N/A') . "</div>";
                echo "<hr style='border-color: #444'>";

                if (isset($results['message'])) {
                    echo "<div class='mb-2'>" . nl2br(htmlspecialchars($results['message'])) . "</div>";
                }
                ?>
            </div>

            <div class="mt-4 text-center">
                <a href="/" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>
        </div>
        <div class="card-footer text-center py-3 text-muted" style="font-size: 0.8rem;">
            &copy; 2026 SIDA PASCA - Advanced Auto-Deployment
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>