<?php

namespace App\Controllers;

use App\Models\Participant;
use App\Models\Semester;
use Leaf\Http\Request;

class DocumentImportController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }

        // Get active semester
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        // Get stats
        $db = \App\Utils\Database::connection();
        $stats = [
            'total' => 0,
            'with_photo' => 0,
            'without_photo' => 0,
            'scheduled' => 0,
            'scheduled_without_photo' => 0
        ];

        if ($semesterId) {
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN photo_filename IS NOT NULL THEN 1 ELSE 0 END) as with_photo,
                    SUM(CASE WHEN photo_filename IS NULL THEN 1 ELSE 0 END) as without_photo,
                    SUM(CASE WHEN nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL AND photo_filename IS NULL THEN 1 ELSE 0 END) as scheduled_without_photo
                    FROM participants WHERE semester_id = '$semesterId'";
            $result = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            $stats = $result[0] ?? $stats;
        }

        // Get session cookie status
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        $cookieStatus = !empty($sessionCookie) ? 'configured' : 'not_configured';

        echo \App\Utils\View::render('admin.document_import.index', [
            'activeSemester' => $activeSemester,
            'stats' => $stats,
            'cookieStatus' => $cookieStatus
        ]);
    }

    public function bulkDownload()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $mode = Request::get('mode') ?? 'without_photo';
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        if (!$semesterId) {
            response()->json(['success' => false, 'message' => 'Tidak ada semester aktif'], 400);
            return;
        }

        // Get session cookie
        $sessionCookie = \App\Models\Setting::get('admisipasca_session_cookie', '');
        if (empty($sessionCookie)) {
            response()->json(['success' => false, 'message' => 'Session cookie belum dikonfigurasi'], 400);
            return;
        }

        // Build query based on mode
        $db = \App\Utils\Database::connection();
        $whereClause = "WHERE semester_id = '$semesterId' AND email IS NOT NULL";

        switch ($mode) {
            case 'all':
                // All participants with email
                break;
            case 'without_photo':
                $whereClause .= " AND photo_filename IS NULL";
                break;
            case 'scheduled':
                $whereClause .= " AND nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL";
                break;
            case 'scheduled_without_photo':
                $whereClause .= " AND nomor_peserta IS NOT NULL AND ruang_ujian IS NOT NULL AND photo_filename IS NULL";
                break;
            default:
                response()->json(['success' => false, 'message' => 'Mode tidak valid'], 400);
                return;
        }

        $sql = "SELECT id, email, nomor_peserta, nama_lengkap, photo_filename FROM participants $whereClause ORDER BY id ASC";
        $participants = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($participants)) {
            response()->json(['success' => false, 'message' => 'Tidak ada peserta yang memenuhi kriteria'], 404);
            return;
        }

        // Return list for client-side processing
        response()->json([
            'success' => true,
            'message' => 'Daftar peserta berhasil diambil',
            'total' => count($participants),
            'participants' => array_map(function ($p) {
                return [
                    'id' => $p['id'],
                    'email' => $p['email'],
                    'nama' => $p['nama_lengkap'],
                    'nomor_peserta' => $p['nomor_peserta']
                ];
            }, $participants)
        ]);
    }

    public function saveSessionCookie()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $cookie = Request::get('session_cookie') ?? '';

        if (empty($cookie)) {
            response()->json(['success' => false, 'message' => 'Session cookie tidak boleh kosong'], 400);
            return;
        }

        // Auto-detect and convert JSON format to Header String
        $cookie = trim($cookie);

        // Check if it's JSON format (starts with [ or {)
        if (substr($cookie, 0, 1) === '[' || substr($cookie, 0, 1) === '{') {
            try {
                $cookieArray = json_decode($cookie, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    response()->json(['success' => false, 'message' => 'Format JSON tidak valid'], 400);
                    return;
                }

                // Convert JSON array to Header String format
                $headerParts = [];
                foreach ($cookieArray as $cookieObj) {
                    if (isset($cookieObj['name']) && isset($cookieObj['value'])) {
                        $headerParts[] = $cookieObj['name'] . '=' . $cookieObj['value'];
                    }
                }

                $cookie = implode('; ', $headerParts);

            } catch (\Exception $e) {
                response()->json(['success' => false, 'message' => 'Gagal convert JSON: ' . $e->getMessage()], 400);
                return;
            }
        }

        // Validate that we have at least one cookie
        if (empty($cookie) || strpos($cookie, '=') === false) {
            response()->json(['success' => false, 'message' => 'Format cookie tidak valid. Pastikan ada minimal satu cookie dengan format name=value'], 400);
            return;
        }

        \App\Models\Setting::set('admisipasca_session_cookie', $cookie);

        response()->json([
            'success' => true,
            'message' => 'Session cookie berhasil disimpan',
            'format_detected' => (substr(trim(Request::get('session_cookie')), 0, 1) === '[' || substr(trim(Request::get('session_cookie')), 0, 1) === '{') ? 'JSON (auto-converted)' : 'Header String'
        ]);
    }
}
