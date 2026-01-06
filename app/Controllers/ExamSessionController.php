<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;

use App\Models\Semester; // Import Semester model

class ExamSessionController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get Active Semester
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            echo "Belum ada Semester Aktif. Silahkan aktifkan semester terlebih dahulu.";
            return;
        }

        // Join with exam_rooms to get Room Name
        // Filter by Active Semester
        $sql = "SELECT s.*, r.nama_ruang, r.fakultas 
                FROM exam_sessions s
                LEFT JOIN exam_rooms r ON s.exam_room_id = r.id
                WHERE s.semester_id = ?
                ORDER BY s.nama_sesi ASC, s.tanggal ASC, s.waktu_mulai ASC, r.nama_ruang ASC";

        $sessions = Database::connection()->query($sql)->bind($activeSemester['id'])->fetchAll();
        echo View::render('admin.master.sessions.index', [
            'sessions' => $sessions,
            'semester' => $activeSemester
        ]);
    }

    public function create()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        // Get Rooms for Dropdown
        $rooms = Database::connection()->select('exam_rooms')->orderBy('nama_ruang', 'asc')->fetchAll();
        echo View::render('admin.master.sessions.create', ['rooms' => $rooms]);
    }

    public function store()
    {
        if (!isset($_SESSION['admin']))
            return;

        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            // Should handle error gracefully
            header('Location: /admin/master/sessions');
            exit;
        }

        $data = Request::body();
        $roomIds = $data['exam_room_ids'] ?? []; // Changed from single ID to Array

        // Validation handles
        if (empty($roomIds)) {
            // flash error
            header('Location: /admin/master/sessions/create');
            exit;
        }

        if (!is_array($roomIds)) {
            $roomIds = [$roomIds];
        }

        foreach ($roomIds as $roomId) {
            $insertData = [
                'semester_id' => $activeSemester['id'],
                'exam_room_id' => $roomId,
                'nama_sesi' => $data['nama_sesi'],
                'tanggal' => $data['tanggal'],
                'waktu_mulai' => $data['waktu_mulai'],
                'waktu_selesai' => $data['waktu_selesai'],
                'is_active' => 1
            ];
            Database::connection()->insert('exam_sessions')->params($insertData)->execute();
        }

        header('Location: /admin/master/sessions');
        exit;
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $session = Database::connection()->select('exam_sessions')->where('id', $id)->first();
        $rooms = Database::connection()->select('exam_rooms')->orderBy('nama_ruang', 'asc')->fetchAll();

        echo View::render('admin.master.sessions.edit', [
            's' => $session,
            'rooms' => $rooms
        ]);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        $updateData = [
            'exam_room_id' => $data['exam_room_id'],
            'nama_sesi' => $data['nama_sesi'],
            'tanggal' => $data['tanggal'],
            'waktu_mulai' => $data['waktu_mulai'],
            'waktu_selesai' => $data['waktu_selesai']
        ];

        Database::connection()->update('exam_sessions')->params($updateData)->where('id', $id)->execute();
        header('Location: /admin/master/sessions');
        exit;
    }

    public function destroy($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        Database::connection()->delete('exam_sessions')->where('id', $id)->execute();
        header('Location: /admin/master/sessions');
        exit;
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
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 's.id',
            1 => 's.nama_sesi',
            2 => 's.tanggal',
            3 => 's.waktu_mulai',
            4 => 'r.nama_ruang',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 's.id';

        // Base WHERE
        $whereClause = "WHERE s.semester_id = '$semesterId'";

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (s.nama_sesi LIKE '%$searchEscaped%' 
                             OR r.nama_ruang LIKE '%$searchEscaped%'
                             OR r.fakultas LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM exam_sessions s WHERE s.semester_id = '$semesterId'";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM exam_sessions s LEFT JOIN exam_rooms r ON s.exam_room_id = r.id $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT s.*, r.nama_ruang, r.fakultas 
                FROM exam_sessions s 
                LEFT JOIN exam_rooms r ON s.exam_room_id = r.id 
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
