<?php

namespace App\Controllers;

use App\Models\Semester;
use Leaf\Http\Request;

class SemesterController
{
    public function index()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin()) {
            response()->redirect('/admin');
            return;
        }

        echo \App\Utils\View::render('admin.semester.index');
    }

    public function apiData()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $db = \App\Utils\Database::connection();

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'desc';

        $columns = [
            0 => 'kode',
            1 => 'nama',
            2 => 'periode',
            3 => 'is_active'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'kode';

        // Base query
        $whereClause = "WHERE 1=1";
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (nama LIKE '%$searchEscaped%' OR kode LIKE '%$searchEscaped%')";
        }

        $totalRes = $db->query("SELECT COUNT(*) as total FROM semesters")->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;
        $filteredRes = $db->query("SELECT COUNT(*) as total FROM semesters $whereClause")->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT s.*, (SELECT COUNT(*) FROM participants WHERE semester_id = s.id) as participants_count 
                FROM semesters s 
                $whereClause 
                ORDER BY s.$orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $data = $db->query($sql)->fetchAll();

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function store()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin())
            return;

        $kode_base = Request::get('kode');
        $nama = Request::get('nama');
        $periode = Request::get('periode') ?? 0;

        // Generate full code: 20251-1, 20252-0 etc.
        $kode = $kode_base . '-' . $periode;

        Semester::create([
            'kode' => $kode,
            'nama' => $nama,
            'periode' => $periode,
            'is_active' => 0
        ]);

        response()->redirect('/admin/semesters');
    }

    public function setActive($id)
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin())
            return;

        Semester::setActive($id);
        response()->redirect('/admin/semesters');
    }

    public function destroy($id)
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin())
            return;

        Semester::delete($id);
        response()->redirect('/admin/semesters');
    }

    public function cleanParticipants($id)
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::isSuperadmin())
            return;

        // Delete all participants associated with this semester
        \App\Utils\Database::connection()->delete('participants')
            ->where('semester_id', $id)
            ->execute();

        // Optional: Flash message (Leaf doesn't have built-in flash in this setup, so maybe query param)
        response()->redirect('/admin/semesters?msg=cleared');
    }
}
