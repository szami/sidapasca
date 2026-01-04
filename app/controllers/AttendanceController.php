<?php

namespace App\Controllers;

use Leaf\Http\Request;
use App\Utils\Database;
use App\Utils\View;
use App\Models\Semester;

class AttendanceController
{
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
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
            response()->redirect('/admin/login');
            return;
        }

        $room = $_GET['ruang'] ?? null;
        $sesi = $_GET['sesi'] ?? null;
        $tanggal = $_GET['tanggal'] ?? null;

        if (!$room || !$sesi || !$tanggal) {
            response()->redirect('/admin/attendance');
            return;
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
        if (!isset($_SESSION['admin']))
            return;

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

        response()->redirect('/admin/attendance?msg=success');
    }
}
