<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;

use App\Models\Semester;

class ExamSchedulerController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        // Only Superadmin, Admin, TU can manage schedules
        if (!\App\Utils\RoleHelper::canManageSchedule()) {
            response()->redirect('/admin?error=unauthorized');
            return;
        }

        $db = Database::connection();

        // Active Semester Only
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            echo "Belum ada Semester Aktif.";
            return;
        }

        // Filter Param
        $filterStatus = $_GET['status'] ?? 'unscheduled'; // 'all', 'scheduled', 'unscheduled'

        // 1. Get Active Sessions for Dropdown (Filtered by Semester)
        $sqlSessions = "SELECT s.*, r.nama_ruang, r.fakultas, r.kapasitas 
                        FROM exam_sessions s
                        JOIN exam_rooms r ON s.exam_room_id = r.id
                        WHERE s.is_active = 1 AND s.semester_id = ?
                        ORDER BY s.tanggal ASC, s.waktu_mulai ASC";
        $sessions = $db->query($sqlSessions)->bind($activeSemester['id'])->fetchAll();

        // 2. Get Distinct Prodis for Filter (filtered by active semester only)
        $prodis = $db->query("SELECT DISTINCT nama_prodi FROM participants 
                              WHERE semester_id = ? 
                              AND nama_prodi IS NOT NULL 
                              AND nama_prodi != '' 
                              ORDER BY nama_prodi ASC")
            ->bind($activeSemester['id'])
            ->fetchAll();
        $filterProdi = $_GET['prodi'] ?? '';

        // The previous logic for fetching participants is now handled by the apiData method
        // and DataTables on the client-side.
        // The view will now only receive sessions, filter status, filter prodi, and prodis.

        echo View::render('admin.scheduler.index', [
            'sessions' => $sessions,
            'filterStatus' => $filterStatus,
            'filterProdi' => $filterProdi,
            'prodis' => $prodis
        ]);
    }

    public function assign()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            response()->redirect('/admin?error=unauthorized');
            return;
        }

        $data = Request::body();
        $participantIds = $data['participant_ids'] ?? [];
        $sessionId = $data['session_id'] ?? null;

        if (empty($participantIds) || empty($sessionId)) {
            response()->redirect('/admin/scheduler');
            return;
        }

        $db = Database::connection();

        // 1. Fetch Session Details & Room Capacity
        $sqlSession = "SELECT s.*, r.nama_ruang, r.kapasitas 
                       FROM exam_sessions s
                       JOIN exam_rooms r ON s.exam_room_id = r.id
                       WHERE s.id = ?";
        $session = $db->query($sqlSession)->bind($sessionId)->fetchAssoc();

        if (!$session) {
            response()->redirect('/admin/scheduler');
            return;
        }

        // 2. Count Current Participants in this Session
        // Matching by sesi_ujian name AND tanggal/waktu OR just sesi_ujian string?
        // Current logic stores 'sesi_ujian' as string name in participants table.
        // It's safer if we could link by ID, but strict schema wasn't requested.
        // Let's match by distinct attributes we set: sesi_ujian name AND ruang_ujian name.
        $count = $db->select('participants')
            ->where('sesi_ujian', $session['nama_sesi'])
            ->where('ruang_ujian', $session['nama_ruang'])
            ->where('tanggal_ujian', $session['tanggal'])
            ->count();
        // Note: Leaf v3 count() might not work on query builder chain directly in all versions, 
        // if it fails we use fetchAll() count. But let's try standard way or raw query for safety.

        // Safer Count Query
        $sqlCount = "SELECT COUNT(*) as total FROM participants 
                     WHERE sesi_ujian = ? AND ruang_ujian = ? AND tanggal_ujian = ?";
        $currentCountRes = $db->query($sqlCount)->bind($session['nama_sesi'], $session['nama_ruang'], $session['tanggal'])->fetchAssoc();
        $currentCount = $currentCountRes['total'] ?? 0;

        $capacity = (int) $session['kapasitas'];
        $remainingSlots = $capacity - $currentCount;

        if ($remainingSlots <= 0) {
            // Room Full
            // Redirect with error
            response()->redirect('/admin/scheduler?status=unscheduled&msg=full');
            return;
        }

        // 3. Assign up to limit
        $assignedCount = 0;
        $failedCount = 0;

        $idsToAssign = [];
        foreach ($participantIds as $pid) {
            if ($assignedCount < $remainingSlots) {
                $idsToAssign[] = (int) $pid;
                $assignedCount++;
            } else {
                $failedCount++;
            }
        }

        if (!empty($idsToAssign)) {
            // Construct Update Data
            $updateData = [
                'ruang_ujian' => $session['nama_ruang'],
                'tanggal_ujian' => $session['tanggal'],
                'waktu_ujian' => $session['waktu_mulai'] . ' - ' . $session['waktu_selesai'] . ' WITA',
                'sesi_ujian' => $session['nama_sesi']
            ];

            // Bulk Update
            $idsStr = implode(',', $idsToAssign);
            $sqlInfo = "UPDATE participants SET 
                        ruang_ujian = ?, 
                        tanggal_ujian = ?, 
                        waktu_ujian = ?, 
                        sesi_ujian = ? 
                        WHERE id IN ($idsStr)";

            $db->query($sqlInfo)->bind(
                $updateData['ruang_ujian'],
                $updateData['tanggal_ujian'],
                $updateData['waktu_ujian'],
                $updateData['sesi_ujian']
            )->execute();
        }

        $msg = "Berhasil: $assignedCount. ";
        if ($failedCount > 0) {
            $msg .= "Gagal (Penuh): $failedCount.";
        }

        response()->redirect('/admin/scheduler?status=scheduled&msg=' . urlencode($msg));
    }

    public function unassign()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            response()->redirect('/admin?error=unauthorized');
            return;
        }

        $data = Request::body();
        $participantIds = $data['participant_ids'] ?? [];

        if (empty($participantIds)) {
            response()->redirect('/admin/scheduler');
            return;
        }

        $db = Database::connection();
        $idsStr = implode(',', array_map('intval', $participantIds));

        $sql = "UPDATE participants SET 
                ruang_ujian = NULL, 
                tanggal_ujian = NULL, 
                waktu_ujian = NULL, 
                sesi_ujian = NULL 
                WHERE id IN ($idsStr)";

        $db->query($sql)->execute();

        response()->redirect('/admin/scheduler?status=unscheduled');
    }

    public function roomView()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            response()->redirect('/admin?error=unauthorized');
            return;
        }

        $db = Database::connection();
        $activeSemester = Semester::getActive();

        if (!$activeSemester) {
            echo "Belum ada semester aktif.";
            return;
        }

        // Get Sessions with Room info
        $sqlSessions = "SELECT s.*, r.nama_ruang, r.fakultas, r.kapasitas 
                        FROM exam_sessions s
                        JOIN exam_rooms r ON s.exam_room_id = r.id
                        WHERE s.is_active = 1 AND s.semester_id = ?
                        ORDER BY s.tanggal ASC, s.waktu_mulai ASC, r.nama_ruang ASC";
        $sessions = $db->query($sqlSessions)->bind($activeSemester['id'])->fetchAll();

        // For each session, get assigned participants
        foreach ($sessions as &$session) {
            // Similar logic to assign count: match by exact string attributes
            $sqlParticipants = "SELECT * FROM participants 
                                WHERE sesi_ujian = ? AND ruang_ujian = ? AND tanggal_ujian = ?
                                ORDER BY nama_lengkap ASC";
            $session['participants'] = $db->query($sqlParticipants)->bind(
                $session['nama_sesi'],
                $session['nama_ruang'],
                $session['tanggal']
            )->fetchAll();
        }

        echo View::render('admin.scheduler.rooms', [
            'sessions' => $sessions
        ]);
    }
    public function apiData()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::connection();
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 2;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 'p.id',
            1 => 'p.nomor_peserta',
            2 => 'p.nama_lengkap',
            3 => 'p.nama_prodi',
            4 => 'p.ruang_ujian',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'p.nama_prodi';

        // Filter Param
        $filterStatus = $_GET['status'] ?? 'unscheduled';
        $filterProdi = $_GET['prodi'] ?? '';

        // Base WHERE
        $whereClause = "WHERE p.semester_id = '$semesterId' AND p.nomor_peserta IS NOT NULL AND p.nomor_peserta != ''";

        // Status Filter
        if ($filterStatus === 'unscheduled') {
            $whereClause .= " AND (p.ruang_ujian IS NULL OR p.ruang_ujian = '')";
        } elseif ($filterStatus === 'scheduled') {
            $whereClause .= " AND p.ruang_ujian IS NOT NULL AND p.ruang_ujian != ''";
        }

        // Prodi Filter
        if (!empty($filterProdi)) {
            $prodiEscaped = str_replace("'", "''", $filterProdi);
            $whereClause .= " AND p.nama_prodi = '$prodiEscaped'";
        }

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (p.nama_lengkap LIKE '%$searchEscaped%' 
                             OR p.nomor_peserta LIKE '%$searchEscaped%'
                             OR p.nama_prodi LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM participants p WHERE p.semester_id = '$semesterId' AND p.nomor_peserta IS NOT NULL AND p.nomor_peserta != ''";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM participants p $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT p.* FROM participants p 
                $whereClause 
                ORDER BY $orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $data = $db->query($sql)->fetchAll();

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
}
