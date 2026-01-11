<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\View;
use App\Utils\GitHelper;
use App\Utils\UpdateManager;

class SystemController
{
    public function update()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get current version info
        $currentVersion = UpdateManager::getCurrentVersion();
        $gitStatus = GitHelper::getGitStatus();
        $requirements = UpdateManager::checkRequirements();
        $updateHistory = UpdateManager::getUpdateHistory(10);

        // Check for updates
        $updateCheck = UpdateManager::checkForUpdates();

        echo View::render('admin.system.update', [
            'currentVersion' => $currentVersion,
            'gitStatus' => $gitStatus,
            'requirements' => $requirements,
            'updateCheck' => $updateCheck,
            'updateHistory' => $updateHistory,
            'remoteUrl' => GitHelper::getRemoteUrl()
        ]);
    }

    public function performUpdate()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get user ID from session if available
        $userId = $_SESSION['admin'] ?? null;

        // Perform update
        $result = UpdateManager::performUpdate($userId);

        if ($result['success']) {
            if ($result['status'] === 'no_update') {
                header('Location: /admin/system/update?status=no_update&message=' . urlencode($result['message']));
                exit;
            } else {
                header('Location: /admin/system/update?status=success&message=' . urlencode($result['message']));
                exit;
            }
        } else {
            header('Location: /admin/system/update?status=error&message=' . urlencode($result['message']));
            exit;
        }
    }

    /**
     * AJAX endpoint to check for updates
     */
    public function checkUpdate()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
            return;
        }

        $updateCheck = UpdateManager::checkForUpdates();

        response()->json([
            'success' => true,
            'data' => $updateCheck
        ]);
    }

    /**
     * Display the External Synchronization Guide
     */
    public function syncGuide()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Only Superadmin or Admin can access sync guide
        if (!\App\Utils\RoleHelper::isAdmin()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        echo View::render('admin.system.sync_guide');
    }

    /**
     * Deploy from Dev Folder - For Hostinger (No Git)
     * Shows the folder sync deployment page
     */
    public function deployFromDev()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Only Superadmin can deploy
        if (!\App\Utils\RoleHelper::isSuperadmin()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        // Get current version
        $currentVersion = UpdateManager::getCurrentVersion();

        // Get dev folder info
        $devFolder = $this->getDevFolder();
        $devVersion = null;
        $devFolderExists = false;

        if ($devFolder && is_dir($devFolder)) {
            $devFolderExists = true;
            $devVersion = $this->getVersionFromFolder($devFolder);
        }

        // Get deployment history
        $deployHistory = $this->getDeployHistory();

        // Environment URLs
        $prodUrl = 'https://sidapasca-ulm.inovasidigital.link';
        $devUrl = 'https://devsida.inovasidigital.link';

        echo View::render('admin.system.deploy_from_dev', [
            'currentVersion' => $currentVersion,
            'devVersion' => $devVersion,
            'devFolder' => $devFolder,
            'devFolderExists' => $devFolderExists,
            'deployHistory' => $deployHistory,
            'prodUrl' => $prodUrl,
            'devUrl' => $devUrl
        ]);
    }

    /**
     * AJAX: Perform folder sync deployment
     */
    public function performFolderSync()
    {
        // Start buffer to capture ANY output (warnings, migration logs, echo)
        ob_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin()) {
            ob_end_clean(); // Clear buffer
            echo json_encode(['success' => false, 'message' => 'Unauthorized - Superadmin only']);
            return;
        }

        $devFolder = $this->getDevFolder();

        if (!$devFolder || !is_dir($devFolder)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Dev folder not found. Expected: ../devsida/']);
            return;
        }

        $targetFolder = dirname(__DIR__, 2) . '/';
        $stats = ['files' => 0, 'dirs' => 0, 'errors' => 0, 'skipped' => 0];
        $logs = [];

        // Files/folders to NEVER overwrite
        $excludeList = [
            '.git',
            '.env',
            'storage/database.sqlite',
            'storage/database.db',
            'storage/backups',
            'storage/photos',
            'storage/documents',
            'storage/20',
            'storage/deploy_history.json',
            'config/db.php',
            'sync.php',
            'deploy.php'
        ];

        try {
            $startTime = microtime(true);

            // Perform sync
            $this->syncFoldersRecursive($devFolder, $targetFolder, $excludeList, $stats, $logs);

            // Capture accumulated output so far (if any)
            $syncOutput = ob_get_contents();
            // We don't clean yet, we keep accumulating

            // Run migration
            $migrationFile = $targetFolder . 'app/database/migrations/migrate.php';
            $migrationStatus = 'Not found';

            if (file_exists($migrationFile)) {
                try {
                    // include will output to the current buffer (started at top of function)
                    include $migrationFile;
                    $migrationStatus = 'Success';
                } catch (\Exception $e) {
                    $migrationStatus = 'Failed: ' . $e->getMessage();
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            // Log deployment
            $this->logDeployment($stats, $migrationStatus, $duration);

            // Get ALL output (sync warnings + migration output)
            $fullOutput = ob_get_clean();

            echo json_encode([
                'success' => true,
                'message' => "Deployment completed in {$duration}s",
                'stats' => $stats,
                'migration' => $migrationStatus,
                'duration' => $duration,
                'errors' => $logs,
                'debug_output' => $fullOutput // Send captured output safely in JSON
            ]);

        } catch (\Exception $e) {
            $errorOutput = ob_get_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Deployment failed: ' . $e->getMessage(),
                'debug_output' => $errorOutput
            ]);
        }
    }

    /**
     * Recursive folder sync with exclusions
     */
    private function syncFoldersRecursive($src, $dst, $excludeList, &$stats, &$logs)
    {
        $dir = opendir($src);
        if (!$dir) {
            throw new \Exception("Cannot open source folder: $src");
        }

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
            $stats['dirs']++;
        }

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..')
                continue;

            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            // Calculate relative path for exclusion check
            $devFolderParent = dirname($this->getDevFolder());
            $relativePath = str_replace($devFolderParent . '/', '', $srcFile);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Remove dev folder name from path
            $parts = explode('/', $relativePath);
            if (count($parts) > 1) {
                array_shift($parts);
                $relativePath = implode('/', $parts);
            }

            // Check exclusions
            $skip = false;
            foreach ($excludeList as $exclude) {
                if (
                    $relativePath === $exclude ||
                    strpos($relativePath, $exclude . '/') === 0 ||
                    strpos($relativePath, $exclude) === 0
                ) {
                    $stats['skipped']++;
                    $skip = true;
                    break;
                }
            }
            if ($skip)
                continue;

            if (is_dir($srcFile)) {
                $this->syncFoldersRecursive($srcFile, $dstFile, $excludeList, $stats, $logs);
            } else {
                if (@copy($srcFile, $dstFile)) {
                    $stats['files']++;
                } else {
                    $stats['errors']++;
                    $error = error_get_last();
                    $reason = $error ? $error['message'] : 'Unknown error';
                    $logs[] = "Failed: $relativePath ($reason)";
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get development folder path
     */
    private function getDevFolder()
    {
        $baseDir = dirname(__DIR__, 2); // Root of current app
        $parentDir = dirname($baseDir); // Parent directory (usually html or public_html)

        // Priority list of possible dev folder locations
        $possiblePaths = [
            $parentDir . '/devsida',                      // Standard sibling: ../devsida
            $parentDir . '/devsida.inovasidigital.link',  // Subdomain folder: ../devsida.inovasidigital.link
            $baseDir . '/devsida',                        // Subfolder: ./devsida
            $baseDir . '/../devsida'                      // Explicit sibling traversal
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                return realpath($path);
            }
        }

        return null;
    }

    /**
     * Get version from a folder
     */
    private function getVersionFromFolder($folder)
    {
        $versionFile = rtrim($folder, '/\\') . '/VERSION';
        if (file_exists($versionFile)) {
            $version = trim(file_get_contents($versionFile));
            return [
                'version' => $version,
                'updated_at' => date('Y-m-d H:i:s', filemtime($versionFile))
            ];
        }

        // Fallback
        return [
            'version' => 'Unknown',
            'updated_at' => 'Unknown'
        ];
    }

    /**
     * Get deployment history
     */
    private function getDeployHistory()
    {
        $logFile = dirname(__DIR__, 2) . '/storage/deploy_history.json';
        if (file_exists($logFile)) {
            $data = json_decode(file_get_contents($logFile), true);
            return array_slice(array_reverse($data ?? []), 0, 10);
        }
        return [];
    }

    /**
     * Log a deployment
     */
    private function logDeployment($stats, $migrationStatus, $duration)
    {
        $logFile = dirname(__DIR__, 2) . '/storage/deploy_history.json';
        $history = [];

        if (file_exists($logFile)) {
            $history = json_decode(file_get_contents($logFile), true) ?? [];
        }

        $history[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $_SESSION['admin'] ?? 'Unknown',
            'role' => $_SESSION['admin_role'] ?? 'Unknown',
            'stats' => $stats,
            'migration' => $migrationStatus,
            'duration' => $duration
        ];

        // Keep last 50
        $history = array_slice($history, -50);

        file_put_contents($logFile, json_encode($history, JSON_PRETTY_PRINT));
    }
}
