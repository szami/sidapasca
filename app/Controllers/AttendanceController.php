<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;
use App\Models\Semester;

class AttendanceController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
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

        // For each session, calculate assigned and attended count
        foreach ($sessions as &$session) {
            // Assigned
            $sqlAssigned = "SELECT COUNT(*) as total FROM participants 
                            WHERE sesi_ujian = ? AND ruang_ujian = ? AND tanggal_ujian = ? AND semester_id = ?";
            $resAssigned = $db->query($sqlAssigned)->bind(
                $session['nama_sesi'],
                $session['nama_ruang'],
                $session['tanggal'],
                $activeSemester['id']
            )->fetchAll();
            $resAssigned = $resAssigned[0] ?? ['total' => 0];
            $session['assigned_count'] = $resAssigned['total'] ?? 0;

            // Attended
            $sqlAttended = "SELECT COUNT(*) as total FROM exam_attendances a
                            JOIN participants p ON a.participant_id = p.id
                            WHERE p.sesi_ujian = ? AND p.ruang_ujian = ? AND p.tanggal_ujian = ? 
                            AND a.semester_id = ? AND a.is_present = 1";
            $resAttended = $db->query($sqlAttended)->bind(
                $session['nama_sesi'],
                $session['nama_ruang'],
                $session['tanggal'],
                $activeSemester['id']
            )->fetchAll();
            $resAttended = $resAttended[0] ?? ['total' => 0];
            $session['attended_count'] = $resAttended['total'] ?? 0;
        }

        echo View::render('admin.attendance.index', [
            'sessions' => $sessions,
            'activeSemester' => $activeSemester
        ]);
    }

    public function entry()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $room = $_GET['ruang'] ?? null;
        $sesi = $_GET['sesi'] ?? null;
        $tanggal = $_GET['tanggal'] ?? null;

        if (!$room || !$sesi || !$tanggal) {
            header('Location: /admin/attendance');
            exit;
        }

        $db = Database::connection();
        $activeSemester = Semester::getActive();

        // Fetch Participants
        $sqlParticipants = "SELECT p.*, a.is_present 
                            FROM participants p
                            LEFT JOIN exam_attendances a ON p.id = a.participant_id AND a.semester_id = ?
                            WHERE p.ruang_ujian = ? AND p.sesi_ujian = ? AND p.tanggal_ujian = ? AND p.semester_id = ?
                            ORDER BY p.nama_lengkap ASC";
        $participants = $db->query($sqlParticipants)->bind(
            $activeSemester['id'],
            $room,
            $sesi,
            $tanggal,
            $activeSemester['id']
        )->fetchAll();

        echo View::render('admin.attendance.entry', [
            'participants' => $participants,
            'room' => $room,
            'sesi' => $sesi,
            'tanggal' => $tanggal
        ]);
    }

    public function save()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $db = Database::connection();
        $activeSemester = Semester::getActive();
        $data = Request::body();

        $room = $data['room'] ?? null;
        $sesi = $data['sesi'] ?? null;
        $tanggal = $data['tanggal'] ?? null;
        $presentIds = $data['present'] ?? []; // Array of participant IDs who are present

        // Get all participants for this session to handle those UNCHECKED
        $sqlAll = "SELECT id FROM participants 
                   WHERE ruang_ujian = ? AND sesi_ujian = ? AND tanggal_ujian = ? AND semester_id = ?";
        $allInSession = $db->query($sqlAll)->bind($room, $sesi, $tanggal, $activeSemester['id'])->fetchAll();

        foreach ($allInSession as $p) {
            $pid = $p['id'];
            $isPresent = in_array($pid, $presentIds) ? 1 : 0;

            // Check if record exists
            $existing = $db->select('exam_attendances')
                ->where('participant_id', $pid)
                ->where('semester_id', $activeSemester['id'])
                ->first();

            if ($existing) {
                $db->update('exam_attendances')
                    ->params(['is_present' => $isPresent, 'updated_at' => date('Y-m-d H:i:s')])
                    ->where('id', $existing['id'])
                    ->execute();
            } else {
                $db->insert('exam_attendances')
                    ->params([
                        'participant_id' => $pid,
                        'semester_id' => $activeSemester['id'],
                        'is_present' => $isPresent
                    ])
                    ->execute();
            }
        }

        header('Location: /admin/attendance?msg=success');
        exit;
    }
    public function apiData()
    {
        $this->checkAuth();

        $db = Database::connection();
        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 's.tanggal',
            1 => 's.waktu_mulai',
            2 => 's.nama_sesi',
            3 => 'r.nama_ruang',
            4 => 'r.kapasitas',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 's.tanggal';

        // Base WHERE
        $whereClause = "WHERE s.is_active = 1 AND s.semester_id = '$semesterId'";

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (s.nama_sesi LIKE '%$searchEscaped%' 
                             OR r.nama_ruang LIKE '%$searchEscaped%'
                             OR r.fakultas LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM exam_sessions s WHERE s.is_active = 1 AND s.semester_id = '$semesterId'";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM exam_sessions s JOIN exam_rooms r ON s.exam_room_id = r.id $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT s.*, r.nama_ruang, r.fakultas, r.kapasitas 
                FROM exam_sessions s 
                JOIN exam_rooms r ON s.exam_room_id = r.id 
                $whereClause 
                ORDER BY $orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $sessions = $db->query($sql)->fetchAll();

        // Calculate counts for returned sessions
        foreach ($sessions as &$session) {
            $sqlAssigned = "SELECT COUNT(*) as total FROM participants 
                            WHERE sesi_ujian = ? AND ruang_ujian = ? AND tanggal_ujian = ? AND semester_id = ?";
            $assignedRes = $db->query($sqlAssigned)->bind(
                $session['nama_sesi'],
                $session['nama_ruang'],
                $session['tanggal'],
                $semesterId
            )->fetchAssoc();
            $session['assigned_count'] = $assignedRes['total'] ?? 0;

            $sqlAttended = "SELECT COUNT(*) as total FROM exam_attendances a
                            JOIN participants p ON a.participant_id = p.id
                            WHERE p.sesi_ujian = ? AND p.ruang_ujian = ? AND p.tanggal_ujian = ? 
                            AND a.semester_id = ? AND a.is_present = 1";
            $attendedRes = $db->query($sqlAttended)->bind(
                $session['nama_sesi'],
                $session['nama_ruang'],
                $session['tanggal'],
                $semesterId
            )->fetchAssoc();
            $session['attended_count'] = $attendedRes['total'] ?? 0;
        }

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $sessions
        ]);
    }

    public function printBeritaAcara()
    {
        $this->checkAuth();

        $filterSesi = $_GET['sesi'] ?? 'all';
        $filterRuang = $_GET['ruang'] ?? 'all';
        $forcedTanggal = $_GET['tanggal'] ?? null;

        $db = Database::connection();
        $activeSemester = Semester::getActive();

        // 1. Find all target (Ruang, Sesi, Tanggal) groups matching the filter
        $sqlGroups = "SELECT DISTINCT ruang_ujian, sesi_ujian, tanggal_ujian 
                      FROM participants 
                      WHERE semester_id = ? 
                      AND ruang_ujian IS NOT NULL 
                      AND sesi_ujian IS NOT NULL 
                      AND tanggal_ujian IS NOT NULL";

        $params = [$activeSemester['id']];

        if ($filterSesi !== 'all') {
            $sqlGroups .= " AND sesi_ujian = ?";
            $params[] = $filterSesi;
        }
        if ($filterRuang !== 'all') {
            $sqlGroups .= " AND ruang_ujian = ?";
            $params[] = $filterRuang;
        }
        if ($forcedTanggal) {
            $sqlGroups .= " AND tanggal_ujian = ?";
            $params[] = $forcedTanggal;
        }

        $sqlGroups .= " ORDER BY tanggal_ujian ASC, sesi_ujian ASC, ruang_ujian ASC";

        $groups = $db->query($sqlGroups)->bind(...$params)->fetchAll();

        if (empty($groups)) {
            echo "<h1>Tidak ada data jadwal yang ditemukan untuk filter ini.</h1>";
            return;
        }

        // 2. Prepare data for each group
        $baData = [];
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        foreach ($groups as $g) {
            $room = $g['ruang_ujian'];
            $sesi = $g['sesi_ujian'];
            $tanggal = $g['tanggal_ujian'];

            // Count Assigned
            $sqlAssigned = "SELECT COUNT(*) as total FROM participants 
                            WHERE sesi_ujian = ? AND ruang_ujian = ? AND tanggal_ujian = ? AND semester_id = ?";
            $resAssigned = $db->query($sqlAssigned)->bind($sesi, $room, $tanggal, $activeSemester['id'])->fetchAssoc();
            $total_assigned = $resAssigned['total'] ?? 0;

            // Get Building Info
            $sqlRoom = "SELECT fakultas FROM exam_rooms WHERE nama_ruang = ?";
            $resRoom = $db->query($sqlRoom)->bind($room)->fetchAssoc();
            $gedung = $resRoom['fakultas'] ?? '-';

            // Get Time Info
            $sqlSession = "SELECT waktu_mulai, waktu_selesai FROM exam_sessions 
                           WHERE nama_sesi = ? AND tanggal = ? AND semester_id = ? AND is_active = 1 LIMIT 1";
            $resSession = $db->query($sqlSession)->bind($sesi, $tanggal, $activeSemester['id'])->fetchAssoc();

            $waktu = '00:00';
            if ($resSession) {
                // If invalid date format, handle gracefully
                try {
                    $waktu = date('H:i', strtotime($resSession['waktu_mulai'])) . ' - ' . date('H:i', strtotime($resSession['waktu_selesai']));
                } catch (\Exception $e) {
                    $waktu = $resSession['waktu_mulai'] . ' - ' . $resSession['waktu_selesai'];
                }
            }

            $baData[] = [
                'ruang' => $room,
                'sesi' => $sesi,
                'tanggal' => $tanggal,
                'gedung' => $gedung,
                'waktu' => $waktu,
                'total_assigned' => $total_assigned
            ];
        }

        echo View::render('admin.attendance.print_berita_acara', [
            'activeSemester' => $activeSemester,
            'baData' => $baData,
            'letterhead' => $letterhead
        ]);
    }
    public function printAttendanceList()
    {
        $this->checkAuth();

        $type = $_GET['type'] ?? 'present'; // present | absent
        $filterSesi = $_GET['sesi'] ?? 'all';
        $filterRuang = $_GET['ruang'] ?? 'all';
        $forcedTanggal = $_GET['tanggal'] ?? null;

        $db = Database::connection();
        $activeSemester = Semester::getActive();

        // 1. Find all target (Ruang, Sesi, Tanggal) groups matching the filter
        $sqlGroups = "SELECT DISTINCT ruang_ujian, sesi_ujian, tanggal_ujian 
                      FROM participants 
                      WHERE semester_id = ? 
                      AND ruang_ujian IS NOT NULL 
                      AND sesi_ujian IS NOT NULL 
                      AND tanggal_ujian IS NOT NULL";

        $params = [$activeSemester['id']];

        if ($filterSesi !== 'all') {
            $sqlGroups .= " AND sesi_ujian = ?";
            $params[] = $filterSesi;
        }
        if ($filterRuang !== 'all') {
            $sqlGroups .= " AND ruang_ujian = ?";
            $params[] = $filterRuang;
        }
        if ($forcedTanggal) {
            $sqlGroups .= " AND tanggal_ujian = ?";
            $params[] = $forcedTanggal;
        }

        $sqlGroups .= " ORDER BY tanggal_ujian ASC, sesi_ujian ASC, ruang_ujian ASC";
        $groups = $db->query($sqlGroups)->bind(...$params)->fetchAll();

        if (empty($groups)) {
            echo "<h1>Tidak ada data jadwal yang ditemukan.</h1>";
            return;
        }

        $listData = [];
        $letterhead = \App\Models\Setting::get('exam_card_letterhead', '');

        foreach ($groups as $g) {
            $room = $g['ruang_ujian'];
            $sesi = $g['sesi_ujian'];
            $tanggal = $g['tanggal_ujian'];

            // Get Participants based on Type
            // Join exam_attendances to check presence
            $sqlParts = "SELECT p.*, a.is_present 
                         FROM participants p
                         LEFT JOIN exam_attendances a ON p.id = a.participant_id AND a.semester_id = p.semester_id
                         WHERE p.semester_id = ? 
                         AND p.ruang_ujian = ? 
                         AND p.sesi_ujian = ? 
                         AND p.tanggal_ujian = ?";

            if ($type === 'present') {
                $sqlParts .= " AND a.is_present = 1";
            } else {
                // Absent means is_present is specificially 0 or NULL (record doesn't exist)
                // However, usually we create records only when saving. 
                // If we want 'absent' to include those not yet marked, use IS NULL OR 0.
                // But strictly 'Absent' implies we marked them as absent or they didn't show up.
                // Any participant without is_present=1 is effectively absent from the 'Present' list.
                $sqlParts .= " AND (a.is_present = 0 OR a.is_present IS NULL)";
            }

            $sqlParts .= " ORDER BY p.nama_lengkap ASC";

            $participants = $db->query($sqlParts)->bind($activeSemester['id'], $room, $sesi, $tanggal)->fetchAll();

            // Get Building Info
            $sqlRoom = "SELECT fakultas FROM exam_rooms WHERE nama_ruang = ?";
            $resRoom = $db->query($sqlRoom)->bind($room)->fetchAssoc();
            $gedung = $resRoom['fakultas'] ?? '-';

            // Get Time Info
            $sqlSession = "SELECT waktu_mulai, waktu_selesai FROM exam_sessions 
                           WHERE nama_sesi = ? AND tanggal = ? AND semester_id = ? AND is_active = 1 LIMIT 1";
            $resSession = $db->query($sqlSession)->bind($sesi, $tanggal, $activeSemester['id'])->fetchAssoc();

            $waktu = '00:00';
            if ($resSession) {
                try {
                    $waktu = date('H:i', strtotime($resSession['waktu_mulai'])) . ' - ' . date('H:i', strtotime($resSession['waktu_selesai']));
                } catch (\Exception $e) {
                    $waktu = $resSession['waktu_mulai'] . ' - ' . $resSession['waktu_selesai'];
                }
            }

            $listData[] = [
                'ruang' => $room,
                'sesi' => $sesi,
                'tanggal' => $tanggal,
                'gedung' => $gedung,
                'waktu' => $waktu,
                'participants' => $participants
            ];
        }

        echo View::render('admin.attendance.print_attendance_list', [
            'activeSemester' => $activeSemester,
            'listData' => $listData,
            'type' => $type,
            'letterhead' => $letterhead
        ]);
    }
}
