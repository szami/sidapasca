<?php

namespace App\Controllers;

use App\Models\Setting;
use Leaf\Http\Request;
use App\Utils\View;

class SettingsController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        // Ensure table exists
        Setting::ensureTableExists();

        // Get current settings
        $allow_download = Setting::get('allow_exam_card_download', '0');

        // Get all semesters for clean database feature
        $semesters = \App\Models\Semester::all();

        echo View::render('admin.settings.index', [
            'allow_download' => $allow_download,
            'semesters' => $semesters
        ]);
    }

    public function save()
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();

        // Handle Checkbox
        $allow_download = isset($data['allow_exam_card_download']) ? '1' : '0';

        Setting::ensureTableExists();
        Setting::set('allow_exam_card_download', $allow_download);

        // Save App Name & Timezone
        if (isset($data['app_name'])) {
            Setting::set('app_name', $data['app_name']);
        }
        if (isset($data['timezone'])) {
            Setting::set('timezone', $data['timezone']);
        }

        // Handle File Uploads (Logo & Favicon)
        $assetDir = __DIR__ . '/../../storage/assets';
        if (!is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
        }

        if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $assetDir . '/' . $filename)) {
                Setting::set('app_logo', '/storage/assets/' . $filename);
            }
        }

        if (isset($_FILES['app_favicon']) && $_FILES['app_favicon']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['app_favicon']['name'], PATHINFO_EXTENSION);
            $filename = 'favicon_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['app_favicon']['tmp_name'], $assetDir . '/' . $filename)) {
                Setting::set('app_favicon', '/storage/assets/' . $filename);
            }
        }

        response()->redirect('/admin/settings');
    }

    public function cleanSemester()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        $semesterId = Request::get('semester_id');

        if (!$semesterId) {
            response()->redirect('/admin/settings');
            return;
        }

        // Delete all participants for this semester
        $db = \App\Utils\Database::connection();
        $db->query("DELETE FROM participants WHERE semester_id = ?")
            ->bind($semesterId)
            ->execute();

        response()->redirect('/admin/settings');
    }

    public function backupDatabase()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        // Path to database
        $dbPath = __DIR__ . '/../../storage/database.sqlite';

        if (!file_exists($dbPath)) {
            echo "Database file not found!";
            return;
        }

        // Generate backup filename with timestamp
        $timestamp = date('Y-m-d_His');
        $backupFilename = "backup_sida_pasca_{$timestamp}.sqlite";

        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backupFilename . '"');
        header('Content-Length: ' . filesize($dbPath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        // Read and output file
        readfile($dbPath);
        exit;
    }

    public function restoreDatabase()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Error uploading file!'); window.location='/admin/settings';</script>";
            return;
        }

        $uploadedFile = $_FILES['backup_file'];
        $dbPath = __DIR__ . '/../../storage/database.sqlite';

        // Validate file extension
        $fileExt = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'sqlite') {
            echo "<script>alert('File harus berekstensi .sqlite!'); window.location='/admin/settings';</script>";
            return;
        }

        // Create automatic backup of current database before restore
        if (file_exists($dbPath)) {
            $autoBackupPath = __DIR__ . '/../../storage/backup_before_restore_' . date('Y-m-d_His') . '.sqlite';
            copy($dbPath, $autoBackupPath);
        }

        // Replace current database with uploaded backup
        if (move_uploaded_file($uploadedFile['tmp_name'], $dbPath)) {
            echo "<script>
                alert('✅ Database berhasil di-restore!\\n\\nDatabase lama sudah di-backup otomatis di folder storage.');
                window.location='/admin/settings';
            </script>";
        } else {
            echo "<script>
                alert('❌ Gagal me-restore database!\\n\\nSilakan coba lagi atau hubungi administrator.');
                window.location='/admin/settings';
            </script>";
        }
    }

    public function optimizeDB()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin');
            return;
        }

        try {
            $db = \App\Utils\Database::connection();
            // VACUUM: Rebuilds the database file, reclaiming unused space
            $db->query("VACUUM")->execute();
            // ANALYZE: Updates statistics for the query planner
            $db->query("ANALYZE")->execute();

            response()->redirect('/admin/settings?msg=optimized');
        } catch (\Exception $e) {
            response()->redirect('/admin/settings?error=' . urlencode($e->getMessage()));
        }
    }
}
