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
            header('Location: /admin/login');
            exit;
        }

        // Only Superadmin, Admin, TU can manage schedules
        // Admin Prodi can VIEW only
        if (!\App\Utils\RoleHelper::canViewSchedule()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        $db = Database::connection();

        // Support semester_id from request or fallback to active
        $semesterId = Request::get('semester_id') ?: (Semester::getActive()['id'] ?? null);
        $activeSemester = $db->query("SELECT * FROM semesters WHERE id = ?")->bind($semesterId)->fetchAssoc();

        if (!$activeSemester) {
            echo "Semester tidak ditemukan.";
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

        // 3. Admin Prodi Restriction
        $isReadOnly = !\App\Utils\RoleHelper::canManageSchedule();
        $adminProdiId = \App\Utils\RoleHelper::isAdminProdi() ? \App\Utils\RoleHelper::getProdiId() : null;

        // Force filter if Admin Prodi
        // We will assume prodi list is still useful for context, but selection might be locked in View
        // Actually, we should probably filter the "Prodis" dropdown to only show THEIR prodi if they are locked.

        if ($adminProdiId) {
            // Find Prodi Name from code (since apiData filters by name or code, we pass code to filterProdi)
            // However, the view expects filterProdi to be the value in the Select. 
            // apiData will handle the logic. 
            // Let's pass the code as filterProdi if set.
            if (empty($filterProdi)) {
                $filterProdi = $adminProdiId;
            }
        }

        echo View::render('admin.scheduler.index', [
            'sessions' => $sessions,
            'filterStatus' => $filterStatus,
            'filterProdi' => $filterProdi,
            'prodis' => $prodis,
            'isReadOnly' => $isReadOnly,
            'adminProdiCode' => $adminProdiId
        ]);
    }

    public function assign()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        $data = Request::body();
        $participantIds = $data['participant_ids'] ?? [];
        $sessionId = $data['session_id'] ?? null;

        if (empty($participantIds) || empty($sessionId)) {
            header('Location: /admin/scheduler');
            exit;
        }

        $db = Database::connection();

        // 1. Fetch Session Details & Room Capacity
        $sqlSession = "SELECT s.*, r.nama_ruang, r.kapasitas 
                       FROM exam_sessions s
                       JOIN exam_rooms r ON s.exam_room_id = r.id
                       WHERE s.id = ?";
        $session = $db->query($sqlSession)->bind($sessionId)->fetchAssoc();

        if (!$session) {
            header('Location: /admin/scheduler');
            exit;
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
            // Redirect with error
            header('Location: /admin/scheduler?status=unscheduled&msg=full');
            exit;
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

        header('Location: /admin/scheduler?status=scheduled&msg=' . urlencode($msg));
        exit;
    }

    public function unassign()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        $data = Request::body();
        $participantIds = $data['participant_ids'] ?? [];

        if (empty($participantIds)) {
            header('Location: /admin/scheduler');
            exit;
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

        header('Location: /admin/scheduler?status=unscheduled');
        exit;
    }

    public function roomView()
    {
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canManageSchedule()) {
            header('Location: /admin?error=unauthorized');
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

        // Support semester_id from request
        $semesterId = Request::get('semester_id') ?: (Semester::getActive()['id'] ?? null);

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 2;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        // Column Mapping
        // If ReadOnly (Admin Prodi), Checkbox column (0) is removed in View.
        // We need to shift indices or define different map.
        $isReadOnly = !\App\Utils\RoleHelper::canManageSchedule();

        if ($isReadOnly) {
            // View Columns: 0=NoPeserta, 1=Nama, 2=Prodi, 3=Ruang
            $columns = [
                0 => 'p.nomor_peserta',
                1 => 'p.nama_lengkap',
                2 => 'p.nama_prodi',
                3 => 'p.ruang_ujian',
            ];
        } else {
            // View Columns: 0=Check/ID, 1=NoPeserta, 2=Nama, 3=Prodi, 4=Ruang
            $columns = [
                0 => 'p.id',
                1 => 'p.nomor_peserta',
                2 => 'p.nama_lengkap',
                3 => 'p.nama_prodi',
                4 => 'p.ruang_ujian',
            ];
        }
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
        // Prodi Filter
        // If Admin Prodi, FORCE filter by their Prodi Code
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $myProdiCode = \App\Utils\RoleHelper::getProdiId();
            if ($myProdiCode) {
                $whereClause .= " AND p.kode_prodi = '$myProdiCode'";
            }
        } else {
            // Normal filter for others (by Name usually, from Dropdown)
            if (!empty($filterProdi)) {
                // Check if filterProdi looks like a code (numeric) or name
                // The dropdown usually has Names. 
                // Let's handle Name match.
                $prodiEscaped = str_replace("'", "''", $filterProdi);
                $whereClause .= " AND p.nama_prodi = '$prodiEscaped'";
            }
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

        // Ensure no stray output breaks JSON
        if (ob_get_length())
            ob_clean();

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    /**
     * Export Exam Schedule for CAT System
     */
    public function exportCat()
    {
        // Allow View capability (Admin Prodi included)
        if (!isset($_SESSION['admin']) || !\App\Utils\RoleHelper::canViewSchedule()) {
            header('Location: /admin?error=unauthorized');
            exit;
        }

        $db = Database::connection();

        // Support semester_id from request
        $semesterId = Request::get('semester_id') ?: (Semester::getActive()['id'] ?? null);
        $activeSemester = $db->query("SELECT * FROM semesters WHERE id = ?")->bind($semesterId)->fetchAssoc();

        if (!$activeSemester) {
            die("Semester tidak ditemukan.");
        }

        // Base Query
        $sql = "SELECT 
                    p.nomor_peserta, 
                    p.nama_lengkap, 
                    p.nama_prodi,
                    p.ruang_ujian,
                    p.tanggal_ujian,
                    p.waktu_ujian,
                    p.sesi_ujian
                FROM participants p
                WHERE p.semester_id = ? 
                AND p.ruang_ujian IS NOT NULL 
                AND p.ruang_ujian != ''";

        // Admin Prodi Filter
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $myProdiCode = \App\Utils\RoleHelper::getProdiId();
            if ($myProdiCode) {
                // Determine if code is string or int, safely bind it?
                // Or just append. Codes are usually safe if from session.
                $sql .= " AND p.kode_prodi = '$myProdiCode'";
            }
        }

        $sql .= " ORDER BY p.tanggal_ujian ASC, p.sesi_ujian ASC, p.nama_prodi ASC, p.nama_lengkap ASC";

        $participants = $db->query($sql)->bind($activeSemester['id'])->fetchAll();

        if (empty($participants)) {
            header('Location: /admin/scheduler?msg=' . urlencode('Tidak ada data peserta yang sudah dijadwalkan.'));
            exit;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['No', 'Nomor Peserta', 'Nama Lengkap', 'Program Studi', 'Gedung/Ruangan', 'Tanggal Ujian', 'Waktu Ujian', 'Sesi'];
        foreach ($headers as $col => $text) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $text);
        }

        // Style header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // Data
        $rowIdx = 2;
        foreach ($participants as $i => $p) {
            $sheet->setCellValue('A' . $rowIdx, $i + 1);
            $sheet->setCellValue('B' . $rowIdx, $p['nomor_peserta']);
            $sheet->setCellValue('C' . $rowIdx, $p['nama_lengkap']);
            $sheet->setCellValue('D' . $rowIdx, $p['nama_prodi']);
            $sheet->setCellValue('E' . $rowIdx, $p['ruang_ujian']);
            $dateFormatted = $p['tanggal_ujian'] ? date('d-m-Y', strtotime($p['tanggal_ujian'])) : '';
            $sheet->setCellValue('F' . $rowIdx, $dateFormatted);
            $sheet->setCellValue('G' . $rowIdx, $p['waktu_ujian']);
            $sheet->setCellValue('H' . $rowIdx, $p['sesi_ujian']);
            $rowIdx++;
        }

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output file
        // Add prefix if Prodi
        $filenamePrefix = "Jadwal_CAT_PASCA_";
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $filenamePrefix .= "PRODI_";
        }

        $filename = $filenamePrefix . str_replace(' ', '_', $activeSemester['nama']) . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
