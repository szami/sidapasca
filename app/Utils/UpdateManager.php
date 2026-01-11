<?php

namespace App\Utils;

use App\Utils\Database;
use PDO;

/**
 * UpdateManager - Orchestrates system updates
 * Handles version checking, backup, update execution, and logging
 */
class UpdateManager
{
    private static $versionFile;
    private static $backupDir;

    public function __construct()
    {
        self::$versionFile = realpath(__DIR__ . '/../../') . '/version.json';
        self::$backupDir = realpath(__DIR__ . '/../../storage/backups');
    }

    /**
     * Get current version from VERSION file
     */
    public static function getCurrentVersion(): array
    {
        $versionFile = realpath(__DIR__ . '/../../') . '/VERSION';

        if (!file_exists($versionFile)) {
            return [
                'version' => '1.0.0',
                'updated_at' => date('Y-m-d H:i:s'),
                'git_commit' => null,
                'git_branch' => 'main',
                'history' => []
            ];
        }

        $version = trim(file_get_contents($versionFile));
        return [
            'version' => $version,
            'updated_at' => date('Y-m-d H:i:s', filemtime($versionFile)),
            'git_commit' => GitHelper::getCurrentCommit(),
            'git_branch' => GitHelper::getCurrentBranch() ?? 'main',
            'history' => []
        ];
    }

    /**
     * Update version.json with new version
     */
    public static function updateVersionFile(string $newVersion, string $user = 'admin'): bool
    {
        $file = self::getVersionFile();
        $current = self::getCurrentVersion();

        $newData = [
            'version' => $newVersion,
            'updated_at' => date('Y-m-d H:i:s'),
            'git_commit' => GitHelper::getCurrentCommit(),
            'git_branch' => GitHelper::getCurrentBranch() ?? 'main',
            'history' => array_slice(array_merge([
                [
                    'version' => $newVersion,
                    'date' => date('Y-m-d H:i:s'),
                    'user' => $user,
                    'status' => 'success'
                ]
            ], $current['history'] ?? []), 0, 10) // Keep last 10
        ];

        return file_put_contents($file, json_encode($newData, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Check for available updates
     */
    public static function checkForUpdates(): array
    {
        if (!GitHelper::isGitAvailable() || !GitHelper::isExecEnabled()) {
            return [
                'has_update' => false,
                'current_version' => self::getCurrentVersion()['version'],
                'latest_version' => null,
                'message' => 'Git tidak tersedia atau exec() disabled',
                'error' => true
            ];
        }

        $current = self::getCurrentVersion();
        $hasRemoteUpdates = GitHelper::hasRemoteUpdates();

        return [
            'has_update' => $hasRemoteUpdates,
            'current_version' => $current['version'],
            'current_commit' => GitHelper::getShortCommit(),
            'latest_commit' => $hasRemoteUpdates ? substr(GitHelper::getRemoteCommit() ?? '', 0, 7) : GitHelper::getShortCommit(),
            'git_status' => GitHelper::getGitStatus(),
            'message' => $hasRemoteUpdates ? 'Update tersedia!' : 'Sistem sudah up-to-date',
            'error' => false
        ];
    }

    /**
     * Perform complete update process
     */
    public static function performUpdate(int $userId = null): array
    {
        $startTime = microtime(true);
        $backupFile = null;
        $status = 'failed';
        $message = '';

        try {
            // Step 1: Validate prerequisites
            if (!GitHelper::isGitAvailable()) {
                throw new \Exception('Git tidak terinstall di server');
            }

            if (!GitHelper::isExecEnabled()) {
                throw new \Exception('PHP exec() function tidak diaktifkan');
            }

            if (GitHelper::hasUncommittedChanges()) {
                throw new \Exception('Ada perubahan lokal yang belum di-commit. Update dibatalkan.');
            }

            // Step 2: Create database backup
            $backupFile = self::createDatabaseBackup();
            if (!$backupFile) {
                throw new \Exception('Gagal membuat backup database');
            }

            // Step 3: Get current version
            $currentVersion = self::getCurrentVersion();
            $oldVersion = $currentVersion['version'];
            $oldCommit = GitHelper::getCurrentCommit();

            // Step 4: Execute git pull
            $pullResult = GitHelper::pullLatestCode();

            if (!$pullResult['success']) {
                throw new \Exception('Git pull gagal: ' . $pullResult['message']);
            }

            // Check if already up to date
            if ($pullResult['already_up_to_date']) {
                $status = 'no_update';
                $message = 'Sistem sudah up-to-date. Tidak ada perubahan.';
            } else {
                // Step 5: Run migrations if exists
                self::runMigrations();

                // Step 6: Increment version (minor version bump)
                $newVersion = self::incrementVersion($oldVersion);

                // Step 7: Update version.json
                $username = self::getUsername($userId);
                if (!self::updateVersionFile($newVersion, $username)) {
                    throw new \Exception('Gagal update version.json');
                }

                $status = 'success';
                $message = "Update berhasil dari v{$oldVersion} ke v{$newVersion}";
            }

            // Step 8: Log success
            self::logUpdate($oldVersion, $currentVersion['version'], $status, $message, $userId, $backupFile);

            return [
                'success' => true,
                'status' => $status,
                'message' => $message,
                'old_version' => $oldVersion,
                'new_version' => $currentVersion['version'],
                'backup_file' => $backupFile,
                'duration' => round(microtime(true) - $startTime, 2)
            ];

        } catch (\Exception $e) {
            // Log failure
            $errorMessage = $e->getMessage();
            self::logUpdate(
                $currentVersion['version'] ?? '1.0.0',
                $currentVersion['version'] ?? '1.0.0',
                'failed',
                $errorMessage,
                $userId,
                $backupFile
            );

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMessage,
                'backup_file' => $backupFile,
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }

    /**
     * Create database backup before update
     */
    private static function createDatabaseBackup(): ?string
    {
        $backupDir = self::getBackupDir();

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $dbPath = realpath(__DIR__ . '/../../storage/database.sqlite');

        if (!file_exists($dbPath)) {
            return null;
        }

        $timestamp = date('Y-m-d_His');
        $backupFile = $backupDir . "/backup_before_update_{$timestamp}.sqlite";

        if (copy($dbPath, $backupFile)) {
            return basename($backupFile);
        }

        return null;
    }

    /**
     * Run database migrations
     */
    private static function runMigrations(): void
    {
        $migrationFile = realpath(__DIR__ . '/../database/migrations/migrate.php');

        if (file_exists($migrationFile)) {
            // Include and run migration (silent execution)
            // Note: This assumes migrate.php can be run safely multiple times (idempotent)
            ob_start();
            include $migrationFile;
            ob_end_clean();
        }
    }

    /**
     * Increment version number (minor bump)
     */
    private static function incrementVersion(string $currentVersion): string
    {
        // Parse semantic version: X.Y.Z
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $currentVersion, $matches)) {
            $major = (int) $matches[1];
            $minor = (int) $matches[2];
            $patch = (int) $matches[3];

            // Increment patch version
            $patch++;

            return "{$major}.{$minor}.{$patch}";
        }

        // Default fallback
        return $currentVersion;
    }

    /**
     * Log update attempt to database
     */
    private static function logUpdate(
        string $versionFrom,
        string $versionTo,
        string $status,
        string $message,
        ?int $userId,
        ?string $backupFile
    ): void {
        try {
            $db = Database::connection();
            $db->insert('update_logs')->params([
                'version_from' => $versionFrom,
                'version_to' => $versionTo,
                'status' => $status,
                'message' => $message,
                'performed_by' => $userId,
                'backup_file' => $backupFile
            ])->execute();
        } catch (\Exception $e) {
            // Silent fail - logging shouldn't break the update
            error_log("Failed to log update: " . $e->getMessage());
        }
    }

    /**
     * Get update history
     */
    public static function getUpdateHistory(int $limit = 10): array
    {
        try {
            $db = Database::connection();
            $history = $db->query("
                SELECT 
                    ul.*,
                    u.username
                FROM update_logs ul
                LEFT JOIN users u ON ul.performed_by = u.id
                ORDER BY ul.created_at DESC
                LIMIT {$limit}
            ")->fetchAll();

            return $history ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get username from session or database
     */
    private static function getUsername(?int $userId): string
    {
        if (isset($_SESSION['admin_username'])) {
            return $_SESSION['admin_username'];
        }

        if ($userId) {
            try {
                $db = Database::connection();
                $user = $db->select('users')
                    ->where('id', $userId)
                    ->first();

                if ($user) {
                    return $user['username'];
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return 'admin';
    }

    /**
     * Get version file path
     */
    private static function getVersionFile(): string
    {
        if (!self::$versionFile) {
            self::$versionFile = realpath(__DIR__ . '/../../') . '/version.json';
        }
        return self::$versionFile;
    }

    /**
     * Get backup directory path
     */
    private static function getBackupDir(): string
    {
        if (!self::$backupDir) {
            self::$backupDir = realpath(__DIR__ . '/../../storage/backups');
        }
        return self::$backupDir;
    }

    /**
     * Check system requirements
     */
    public static function checkRequirements(): array
    {
        return [
            'git_installed' => GitHelper::isGitAvailable(),
            'exec_enabled' => GitHelper::isExecEnabled(),
            'version_file_writable' => is_writable(dirname(self::getVersionFile())),
            'backup_dir_writable' => is_writable(self::getBackupDir()),
            'git_status' => GitHelper::getGitStatus()
        ];
    }
}
