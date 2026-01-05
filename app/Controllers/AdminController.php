<?php

namespace App\Controllers;

use App\Models\Participant;
use App\Utils\RoleHelper;
use Leaf\Blade;

class AdminController
{
    public function dashboard()
    {
        // Render Admin Login View if not logged in
        if (!isset($_SESSION['admin'])) {
            $error = null;
            if (isset($_GET['error'])) {
                if ($_GET['error'] === 'maintenance') {
                    $error = \App\Models\Setting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan. Hanya Superadmin yang dapat login.');
                } else {
                    $error = 'Username atau password salah!';
                }
            }
            echo \App\Utils\View::render('auth.admin_login', ['error' => $error]);
            return;
        }

        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        // Get role info for display
        $role = RoleHelper::getRole();
        $roleDisplayName = RoleHelper::getRoleDisplayName();
        $isAdminProdi = RoleHelper::isAdminProdi();
        $prodiId = RoleHelper::getProdiId();
        $username = RoleHelper::getUsername();

        $db = \App\Utils\Database::connection();

        if (!$semesterId) {
            // Fallback stats if no active semester
            $stats = [
                'total' => 0,
                'lulus' => 0,
                'pending' => 0,
                'gagal' => 0,
                'paid' => 0,
                'unpaid' => 0
            ];
            $prodiStats = [];
            $s2Stats = [];
            $s3Stats = [];
            $semesterName = "Tidak ada semester aktif";
            $recentParticipants = [];
            $scheduledCount = 0;
            $verifiedCount = 0;
            $todaySchedule = [];
        } else {
            $semesterName = $activeSemester['nama'];

            // Build WHERE clause based on role
            $whereClause = "semester_id = '$semesterId'";
            if ($isAdminProdi && $prodiId) {
                $whereClause .= " AND kode_prodi = '$prodiId'";
            }

            // Global Stats
            $sqlGlobal = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_berkas = 'lulus' THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN status_berkas = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_berkas = 'gagal' THEN 1 ELSE 0 END) as gagal,
                SUM(CASE WHEN status_pembayaran = 1 THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status_pembayaran = 0 AND status_berkas = 'lulus' THEN 1 ELSE 0 END) as unpaid
                FROM participants WHERE $whereClause";

            $global = $db->query($sqlGlobal)->fetchAll(\PDO::FETCH_ASSOC)[0] ?? [
                'total' => 0,
                'lulus' => 0,
                'pending' => 0,
                'gagal' => 0,
                'paid' => 0,
                'unpaid' => 0
            ];
            $stats = $global;

            // Prodi Stats Aggregation
            $sqlProdi = "SELECT 
                nama_prodi,
                COUNT(*) as total,
                SUM(CASE WHEN status_berkas = 'lulus' THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN status_berkas = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_berkas = 'gagal' THEN 1 ELSE 0 END) as gagal,
                SUM(CASE WHEN status_pembayaran = 1 THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status_pembayaran = 0 AND status_berkas = 'lulus' THEN 1 ELSE 0 END) as unpaid
                FROM participants 
                WHERE $whereClause
                GROUP BY nama_prodi
                ORDER BY total DESC";

            $prodiStats = $db->query($sqlProdi)->fetchAll(\PDO::FETCH_ASSOC);

            // Split Stats into S2 and S3
            $s2Stats = array_filter($prodiStats, function ($item) {
                $name = strtoupper($item['nama_prodi'] ?? '');
                return strpos($name, 'S2') !== false || strpos($name, 'MAGISTER') !== false;
            });

            $s3Stats = array_filter($prodiStats, function ($item) {
                $name = strtoupper($item['nama_prodi'] ?? '');
                return strpos($name, 'S3') !== false || strpos($name, 'DOKTOR') !== false;
            });

            // Recent Participants (last 5)
            $recentParticipants = $db->query("SELECT id, nama_lengkap, nama_prodi, status_berkas, created_at 
                FROM participants WHERE $whereClause 
                ORDER BY created_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);

            // Scheduled participants count
            $scheduledCount = $db->query("SELECT COUNT(*) as count FROM participants 
                WHERE $whereClause AND ruang_ujian IS NOT NULL")->fetchAll(\PDO::FETCH_ASSOC)[0]['count'] ?? 0;

            // Verified documents count (for UPKH)
            $verifiedCount = 0;
            if (RoleHelper::canValidatePhysical()) {
                try {
                    $verifiedRes = $db->query("SELECT COUNT(*) as count FROM document_verifications 
                        WHERE can_download_card = 1")->fetchAll(\PDO::FETCH_ASSOC);
                    $verifiedCount = $verifiedRes[0]['count'] ?? 0;
                } catch (\Exception $e) {
                    // Table or column doesn't exist - ignore
                    $verifiedCount = 0;
                }
            }

            // Today's exam schedule (for TU)
            $todaySchedule = [];
            if (RoleHelper::canManageSchedule()) {
                $today = date('Y-m-d');
                $todaySchedule = $db->query("SELECT DISTINCT sesi_ujian, ruang_ujian, waktu_ujian, COUNT(*) as peserta_count
                    FROM participants 
                    WHERE tanggal_ujian = '$today' AND ruang_ujian IS NOT NULL
                    GROUP BY sesi_ujian, ruang_ujian, waktu_ujian
                    ORDER BY waktu_ujian")->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        // Quick actions based on role
        $quickActions = $this->getQuickActions($role);

        echo \App\Utils\View::render('admin.dashboard', [
            'stats' => $stats,
            's2Stats' => array_values($s2Stats),
            's3Stats' => array_values($s3Stats),
            'semesterName' => $semesterName,
            'role' => $role,
            'roleDisplayName' => $roleDisplayName,
            'isAdminProdi' => $isAdminProdi,
            'username' => $username,
            'recentParticipants' => $recentParticipants,
            'scheduledCount' => $scheduledCount,
            'verifiedCount' => $verifiedCount,
            'todaySchedule' => $todaySchedule,
            'quickActions' => $quickActions
        ]);
    }

    private function getQuickActions($role)
    {
        $actions = [];

        switch ($role) {
            case 'superadmin':
                $actions = [
                    ['icon' => 'users', 'label' => 'Manajemen User', 'url' => '/admin/users', 'color' => 'indigo'],
                    ['icon' => 'upload', 'label' => 'Import Data', 'url' => '/admin/import', 'color' => 'blue'],
                    ['icon' => 'cog', 'label' => 'Pengaturan', 'url' => '/admin/settings', 'color' => 'gray'],
                    ['icon' => 'chart', 'label' => 'Laporan', 'url' => '/admin/participants', 'color' => 'green'],
                ];
                break;
            case 'admin':
                $actions = [
                    ['icon' => 'upload', 'label' => 'Import Data', 'url' => '/admin/import', 'color' => 'blue'],
                    ['icon' => 'users', 'label' => 'Data Peserta', 'url' => '/admin/participants', 'color' => 'indigo'],
                    ['icon' => 'check', 'label' => 'Verifikasi Berkas', 'url' => '/admin/verification/physical', 'color' => 'green'],
                    ['icon' => 'mail', 'label' => 'Kirim Email', 'url' => '/admin/email/reminders/send', 'color' => 'purple'],
                ];
                break;
            case 'upkh':
                $actions = [
                    ['icon' => 'check', 'label' => 'Verifikasi Berkas', 'url' => '/admin/verification/physical', 'color' => 'green'],
                    ['icon' => 'users', 'label' => 'Data Peserta', 'url' => '/admin/participants', 'color' => 'indigo'],
                    ['icon' => 'document', 'label' => 'Download Dokumen', 'url' => '/admin/participants', 'color' => 'blue'],
                ];
                break;
            case 'tu':
                $actions = [
                    ['icon' => 'calendar', 'label' => 'Penjadwalan', 'url' => '/admin/scheduler', 'color' => 'blue'],
                    ['icon' => 'clipboard', 'label' => 'Daftar Hadir', 'url' => '/admin/attendance', 'color' => 'green'],
                    ['icon' => 'printer', 'label' => 'Cetak Kartu', 'url' => '/admin/exam-card', 'color' => 'purple'],
                    ['icon' => 'document', 'label' => 'Jadwal CAT', 'url' => '/admin/cat-schedule', 'color' => 'indigo'],
                ];
                break;
            case 'admin_prodi':
                $actions = [
                    ['icon' => 'users', 'label' => 'Data Peserta Prodi', 'url' => '/admin/participants', 'color' => 'indigo'],
                    ['icon' => 'chart', 'label' => 'Statistik Prodi', 'url' => '/admin', 'color' => 'green'],
                    ['icon' => 'document', 'label' => 'Download Dokumen', 'url' => '/admin/participants', 'color' => 'blue'],
                ];
                break;
        }

        return $actions;
    }
}

