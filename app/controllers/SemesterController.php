<?php

namespace App\Controllers;

use App\Models\Semester;
use Leaf\Http\Request;

class SemesterController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin');
            return;
        }

        $db = \App\Utils\Database::connection();
        $semesters = $db->query("SELECT s.*, (SELECT COUNT(*) FROM participants WHERE semester_id = s.id) as participants_count 
                                FROM semesters s 
                                ORDER BY s.kode DESC")->fetchAll();

        echo \App\Utils\View::render('admin.semester.index', ['semesters' => $semesters]);
    }

    public function store()
    {
        if (!isset($_SESSION['admin']))
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
        if (!isset($_SESSION['admin']))
            return;

        Semester::setActive($id);
        response()->redirect('/admin/semesters');
    }

    public function destroy($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        Semester::delete($id);
        response()->redirect('/admin/semesters');
    }

    public function cleanParticipants($id)
    {
        if (!isset($_SESSION['admin']))
            return;

        // Delete all participants associated with this semester
        \App\Utils\Database::connection()->delete('participants')
            ->where('semester_id', $id)
            ->execute();

        // Optional: Flash message (Leaf doesn't have built-in flash in this setup, so maybe query param)
        response()->redirect('/admin/semesters?msg=cleared');
    }
}
