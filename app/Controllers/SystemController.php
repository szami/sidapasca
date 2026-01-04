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
            response()->redirect('/admin/login');
            return;
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
            response()->redirect('/admin/login');
            return;
        }

        // Get user ID from session if available
        $userId = $_SESSION['admin'] ?? null;

        // Perform update
        $result = UpdateManager::performUpdate($userId);

        if ($result['success']) {
            if ($result['status'] === 'no_update') {
                response()->redirect('/admin/system/update?status=no_update&message=' . urlencode($result['message']));
            } else {
                response()->redirect('/admin/system/update?status=success&message=' . urlencode($result['message']));
            }
        } else {
            response()->redirect('/admin/system/update?status=error&message=' . urlencode($result['message']));
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
}
