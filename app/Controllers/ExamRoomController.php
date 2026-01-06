<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;

class ExamRoomController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $rooms = Database::connection()->select('exam_rooms')->orderBy('id', 'desc')->fetchAll();
        echo View::render('admin.master.rooms.index', ['rooms' => $rooms]);
    }

    public function apiData()
    {
        if (!isset($_SESSION['admin'])) {
            response()->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::connection();

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 'id',
            1 => 'fakultas',
            2 => 'nama_ruang',
            3 => 'kapasitas',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'id';

        // Base WHERE
        $whereClause = "WHERE 1=1";

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (nama_ruang LIKE '%$searchEscaped%' 
                             OR fakultas LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM exam_rooms";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM exam_rooms $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT * FROM exam_rooms $whereClause 
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

    public function create()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }
        echo View::render('admin.master.rooms.create');
    }

    public function store()
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        $insertData = [
            'fakultas' => $data['fakultas'],
            'nama_ruang' => $data['nama_ruang'],
            'kapasitas' => $data['kapasitas']
        ];

        Database::connection()->insert('exam_rooms')->params($insertData)->execute();
        header('Location: /admin/master/rooms');
        exit;
    }

    public function edit($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $room = Database::connection()->select('exam_rooms')->where('id', $id)->first();
        echo View::render('admin.master.rooms.edit', ['room' => $room]);
    }

    public function update($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        $data = Request::body();
        $updateData = [
            'fakultas' => $data['fakultas'],
            'nama_ruang' => $data['nama_ruang'],
            'kapasitas' => $data['kapasitas']
        ];

        Database::connection()->update('exam_rooms')->params($updateData)->where('id', $id)->execute();
        header('Location: /admin/master/rooms');
        exit;
    }

    public function destroy($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        Database::connection()->delete('exam_rooms')->where('id', $id)->execute();
        header('Location: /admin/master/rooms');
        exit;
    }
}
