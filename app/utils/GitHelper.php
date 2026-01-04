<?php

namespace App\Utils;

/**
 * GitHelper - Wrapper for Git operations
 * Provides safe git command execution with error handling
 */
class GitHelper
{
    private static $projectRoot;

    public function __construct()
    {
        self::$projectRoot = realpath(__DIR__ . '/../../');
    }

    /**
     * Check if git is installed and accessible
     */
    public static function isGitAvailable(): bool
    {
        exec('git --version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Check if exec() function is enabled
     */
    public static function isExecEnabled(): bool
    {
        return function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    }

    /**
     * Get current git branch
     */
    public static function getCurrentBranch(): ?string
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return null;
        }

        $root = self::getProjectRoot();
        exec("cd {$root} && git rev-parse --abbrev-ref HEAD 2>&1", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Get current commit hash
     */
    public static function getCurrentCommit(): ?string
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return null;
        }

        $root = self::getProjectRoot();
        exec("cd {$root} && git rev-parse HEAD 2>&1", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Get short commit hash (7 chars)
     */
    public static function getShortCommit(): ?string
    {
        $commit = self::getCurrentCommit();
        return $commit ? substr($commit, 0, 7) : null;
    }

    /**
     * Check if there are uncommitted changes
     */
    public static function hasUncommittedChanges(): bool
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return false;
        }

        $root = self::getProjectRoot();
        exec("cd {$root} && git status --porcelain 2>&1", $output, $returnCode);

        return $returnCode === 0 && !empty($output);
    }

    /**
     * Fetch latest changes from remote (doesn't merge)
     */
    public static function fetch(): array
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return ['success' => false, 'message' => 'Git or exec() not available'];
        }

        $root = self::getProjectRoot();
        exec("cd {$root} && git fetch origin 2>&1", $output, $returnCode);

        return [
            'success' => $returnCode === 0,
            'message' => implode("\n", $output),
            'output' => $output
        ];
    }

    /**
     * Check if remote has new commits
     */
    public static function hasRemoteUpdates(): bool
    {
        self::fetch();

        $root = self::getProjectRoot();
        $branch = self::getCurrentBranch() ?? 'main';

        exec("cd {$root} && git rev-list HEAD...origin/{$branch} --count 2>&1", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return (int) $output[0] > 0;
        }

        return false;
    }

    /**
     * Get remote version/commit
     */
    public static function getRemoteCommit(): ?string
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return null;
        }

        self::fetch();

        $root = self::getProjectRoot();
        $branch = self::getCurrentBranch() ?? 'main';

        exec("cd {$root} && git rev-parse origin/{$branch} 2>&1", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Pull latest code from remote
     */
    public static function pullLatestCode(): array
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return [
                'success' => false,
                'message' => 'Git or exec() not available'
            ];
        }

        // Check for uncommitted changes
        if (self::hasUncommittedChanges()) {
            return [
                'success' => false,
                'message' => 'Ada perubahan yang belum di-commit. Tidak dapat melakukan update.'
            ];
        }

        $root = self::getProjectRoot();
        $branch = self::getCurrentBranch() ?? 'main';

        // Execute git pull
        exec("cd {$root} && git pull origin {$branch} 2>&1", $output, $returnCode);

        $message = implode("\n", $output);

        return [
            'success' => $returnCode === 0,
            'message' => $message,
            'output' => $output,
            'already_up_to_date' => strpos($message, 'Already up to date') !== false || strpos($message, 'Already up-to-date') !== false
        ];
    }

    /**
     * Get git status
     */
    public static function getGitStatus(): array
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return [
                'branch' => 'unknown',
                'commit' => 'unknown',
                'clean' => false,
                'available' => false
            ];
        }

        return [
            'branch' => self::getCurrentBranch() ?? 'unknown',
            'commit' => self::getShortCommit() ?? 'unknown',
            'clean' => !self::hasUncommittedChanges(),
            'available' => true
        ];
    }

    /**
     * Reset to specific commit (for rollback)
     */
    public static function resetToCommit(string $commitHash): array
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return [
                'success' => false,
                'message' => 'Git or exec() not available'
            ];
        }

        $root = self::getProjectRoot();

        // Validate commit hash (basic sanitization)
        if (!preg_match('/^[a-f0-9]{7,40}$/i', $commitHash)) {
            return [
                'success' => false,
                'message' => 'Invalid commit hash'
            ];
        }

        exec("cd {$root} && git reset --hard {$commitHash} 2>&1", $output, $returnCode);

        return [
            'success' => $returnCode === 0,
            'message' => implode("\n", $output),
            'output' => $output
        ];
    }

    /**
     * Get remote repository URL
     */
    public static function getRemoteUrl(): ?string
    {
        if (!self::isGitAvailable() || !self::isExecEnabled()) {
            return null;
        }

        $root = self::getProjectRoot();
        exec("cd {$root} && git config --get remote.origin.url 2>&1", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * Get project root directory
     */
    private static function getProjectRoot(): string
    {
        if (!self::$projectRoot) {
            self::$projectRoot = realpath(__DIR__ . '/../../');
        }
        return self::$projectRoot;
    }
}
