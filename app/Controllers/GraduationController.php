<?php

namespace App\Controllers;

use App\Utils\View;
use Leaf\Http\Request;
use App\Utils\Database;
use App\Models\Semester;

class GraduationController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }
        // Graduation is high-level. Superadmin or Admin only?
        // Admin Prodi might want to see their board?
        // Let's allow access but restrict actions if needed.
    }

    // --- Quota Management ---

    public function quotas()
    {
        $this->checkAuth();

        if (\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $semesters = Semester::all();

        echo View::render('admin.graduation.quotas', [
            'semesters' => $semesters,
            'currentSemester' => $semesterId
        ]);
    }

    public function apiData()
    {
        $this->checkAuth();
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $db = Database::connection();

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 'kode_prodi',
            1 => 'nama_prodi'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'nama_prodi';

        // Base query - get prodi from participants filtered by semester
        $whereClause = "WHERE p.nama_prodi IS NOT NULL AND p.semester_id = '$semesterId'";
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (p.nama_prodi LIKE '%$searchEscaped%' OR p.kode_prodi LIKE '%$searchEscaped%')";
        }

        $totalRes = $db->query("SELECT COUNT(DISTINCT nama_prodi) as total FROM participants WHERE semester_id = '$semesterId'")->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;
        $filteredRes = $db->query("SELECT COUNT(DISTINCT nama_prodi) as total FROM participants p $whereClause")->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT DISTINCT p.nama_prodi, p.kode_prodi, q.daya_tampung 
                FROM participants p 
                LEFT JOIN prodi_quotas q ON (q.kode_prodi = p.kode_prodi OR q.kode_prodi = p.nama_prodi) AND q.semester_id = ?
                $whereClause 
                ORDER BY p.$orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $data = $db->query($sql)->bind($semesterId)->fetchAll();

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function saveQuotas()
    {
        $this->checkAuth();
        if (\App\Utils\RoleHelper::isAdminProdi())
            exit;

        $semesterId = Request::get('semester_id');
        $inputs = Request::body();

        $db = Database::connection();

        $quotas = $inputs['quotas'] ?? [];

        foreach ($quotas as $kodeProdi => $val) {
            $amount = intval($val);
            // Decode potential issues? Array keys from POST are usually strings.
            // If code has spaces, PHP keys preserve them.

            // Upsert
            $exist = $db->query("SELECT * FROM prodi_quotas WHERE semester_id = ? AND kode_prodi = ?")->bind($semesterId, $kodeProdi)->fetchAssoc();

            if ($exist) {
                $db->query("UPDATE prodi_quotas SET daya_tampung = ? WHERE id = ?")->bind($amount, $exist['id'])->execute();
            } else {
                $db->query("INSERT INTO prodi_quotas (semester_id, kode_prodi, daya_tampung) VALUES (?, ?, ?)")->bind($semesterId, $kodeProdi, $amount)->execute();
            }
        }


        header("Location: /admin/graduation/quotas?semester_id=$semesterId&success=saved");
        exit;
    }

}
