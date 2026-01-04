<?php

namespace App\Controllers;

use App\Models\Participant;
use Leaf\Blade;

class AdminController
{
    public function dashboard()
    {
        // Render Admin Login View if not logged in
        if (!isset($_SESSION['admin'])) {
            $error = isset($_GET['error']) ? 'Username atau password salah!' : null;
            echo \App\Utils\View::render('auth.admin_login', ['error' => $error]);
            return;
        }

        $activeSemester = \App\Models\Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

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
            $semesterName = "Tidak ada semester aktif";
        } else {
            $semesterName = $activeSemester['nama'];

            // Global Stats for Active Semester
            $db = \App\Utils\Database::connection();
            $total = $db->select('participants')->where('semester_id', $semesterId)->count(); // Leaf DB v3 count issue fallback? Using fetchAll count
            // Actually let's use raw query for speed and aggregation

            $sqlGlobal = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_berkas = 'lulus' THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN status_berkas = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_berkas = 'gagal' THEN 1 ELSE 0 END) as gagal,
                SUM(CASE WHEN status_pembayaran = 1 THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status_pembayaran = 0 AND status_berkas = 'lulus' THEN 1 ELSE 0 END) as unpaid
                FROM participants WHERE semester_id = '$semesterId'";

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
                WHERE semester_id = '$semesterId'
                GROUP BY nama_prodi
                ORDER BY total DESC";

            $prodiStats = $db->query($sqlProdi)->fetchAll(\PDO::FETCH_ASSOC);

            // Split Stats into S2 and S3
            $s2Stats = array_filter($prodiStats, function ($item) {
                $name = strtoupper($item['nama_prodi']);
                return strpos($name, 'S2') !== false || strpos($name, 'MAGISTER') !== false;
            });

            $s3Stats = array_filter($prodiStats, function ($item) {
                $name = strtoupper($item['nama_prodi']);
                return strpos($name, 'S3') !== false || strpos($name, 'DOKTOR') !== false;
            });
        }

        echo \App\Utils\View::render('admin.dashboard', [
            'stats' => $stats,
            's2Stats' => array_values($s2Stats), // Re-index array
            's3Stats' => array_values($s3Stats),
            'semesterName' => $semesterName
        ]);
    }
}
