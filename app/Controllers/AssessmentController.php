<?php

namespace App\Controllers;

use App\Utils\View;
use Leaf\Http\Request;
use App\Utils\Database;
use App\Models\Semester;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class AssessmentController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    // --- Component Management ---

    public function components()
    {
        $this->checkAuth();

        // Only Superadmin or Admin can manage components
        // Admin Prodi can theoretically manage Bidang components?
        // Implementation plan says: Admin/Superadmin.
        if (!\App\Utils\RoleHelper::isSuperadmin() && !\App\Utils\RoleHelper::isAdmin()) {
            // Maybe Admin Prodi allowed? Let's restrict to Admin/Super for now to keep standard.
            // If Admin Prodi needs to define their own test components, we can open it.
            // For now, assume Centralized config.
            if (!\App\Utils\RoleHelper::isAdminProdi()) { // Allow Admin Prodi
                header('Location: /admin?error=unauthorized');
                exit;
            }
        }

        $db = Database::connection();

        // Filter: Admin Prodi only sees their own components + Global TPA
        $whereClause = "WHERE 1=1";
        $params = [];

        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $prodiId = $_SESSION['admin_prodi_id'];
            $whereClause .= " AND (prodi_id IS NULL OR prodi_id = ?)";
            $params[] = $prodiId;
        }

        $components = $db->query("SELECT * FROM assessment_components $whereClause ORDER BY type DESC, prodi_id ASC, nama_komponen ASC")->bind(...$params)->fetchAll();

        // Get Prodi List for Dropdown (Superadmin/Admin only)
        $prodiList = [];
        if (!\App\Utils\RoleHelper::isAdminProdi()) {
            $activeSemester = Semester::getActive();
            $semesterId = $activeSemester['id'] ?? null;

            $sql = "SELECT DISTINCT kode_prodi as code, nama_prodi as name FROM participants WHERE nama_prodi IS NOT NULL";
            $params = [];

            if ($semesterId) {
                $sql .= " AND semester_id = ?";
                $params[] = $semesterId;
            }

            $sql .= " ORDER BY nama_prodi ASC";

            $prodiList = $db->query($sql)->bind(...$params)->fetchAll();
        }

        // Get current minimum threshold for Admin Prodi
        $minimumThreshold = 0;
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
            $activeSemester = Semester::getActive();
            $semesterId = $activeSemester['id'] ?? null;
            if ($kodeProdi && $semesterId) {
                $quota = $db->query("SELECT nilai_minimum_bidang FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
                    ->bind($kodeProdi, $semesterId)->fetchAssoc();
                $minimumThreshold = $quota['nilai_minimum_bidang'] ?? 0;
            }
        }

        echo View::render('admin.assessment.components', [
            'components' => $components,
            'prodiList' => $prodiList,
            'isAdminProdi' => \App\Utils\RoleHelper::isAdminProdi(),
            'minimumThreshold' => $minimumThreshold
        ]);
    }

    public function bidangScores()
    {
        $this->checkAuth();
        $db = Database::connection();

        // 1. Determine Scope (Semester Active Only)
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            throw new \Exception("Tidak ada semester aktif.");
        }
        $semesterId = $activeSemester['id'];

        // Check Schedule for Admin Prodi
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
            $scheduleStart = null;
            $scheduleEnd = null;
            $timeStart = '00:00';
            $timeEnd = '23:59';

            // First check prodi-specific schedule
            if ($kodeProdi) {
                $prodiSchedule = $db->query("SELECT jadwal_mulai, jadwal_selesai, jam_mulai, jam_selesai FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
                    ->bind($kodeProdi, $semesterId)->fetchAssoc();

                if ($prodiSchedule && !empty($prodiSchedule['jadwal_mulai']) && !empty($prodiSchedule['jadwal_selesai'])) {
                    $scheduleStart = $prodiSchedule['jadwal_mulai'];
                    $scheduleEnd = $prodiSchedule['jadwal_selesai'];
                    $timeStart = $prodiSchedule['jam_mulai'] ?: '00:00';
                    $timeEnd = $prodiSchedule['jam_selesai'] ?: '23:59';
                }
            }

            // If no prodi-specific, check global schedule
            if (!$scheduleStart || !$scheduleEnd) {
                $scheduleStart = \App\Models\Setting::get('bidang_schedule_start_date', '');
                $scheduleEnd = \App\Models\Setting::get('bidang_schedule_end_date', '');
                $timeStart = \App\Models\Setting::get('bidang_schedule_start_time', '00:00');
                $timeEnd = \App\Models\Setting::get('bidang_schedule_end_time', '23:59');
            }

            // Enforce schedule if set
            if ($scheduleStart && $scheduleEnd) {
                $now = new \DateTime();
                $startDateTime = new \DateTime($scheduleStart . ' ' . $timeStart);
                $endDateTime = new \DateTime($scheduleEnd . ' ' . $timeEnd);

                if ($now < $startDateTime || $now > $endDateTime) {
                    // Outside schedule - show error view
                    echo View::render('admin.assessment.bidang_closed', [
                        'scheduleStart' => $scheduleStart,
                        'scheduleEnd' => $scheduleEnd,
                        'timeStart' => $timeStart,
                        'timeEnd' => $timeEnd
                    ]);
                    return;
                }
            }
        }

        // 2. Determine Prodi
        $prodiFilter = null;
        $prodiName = 'Semua Prodi';

        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
            if ($kodeProdi) {
                // Look up nama_prodi from participants using kode_prodi
                $prodiData = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE kode_prodi = ? AND nama_prodi IS NOT NULL LIMIT 1")->bind($kodeProdi)->fetchAssoc();
                if ($prodiData && isset($prodiData['nama_prodi'])) {
                    $prodiFilter = $prodiData['nama_prodi'];
                    $prodiName = $prodiFilter;
                }
            }
        } else {
            // If Superadmin, require ?prodi=Name or show all
            $prodiFilter = Request::get('prodi');
            if ($prodiFilter) {
                $prodiName = $prodiFilter;
            }
        }

        if (!$prodiFilter) {
            if (\App\Utils\RoleHelper::isAdminProdi()) {
                die("Error: Identitas Prodi tidak ditemukan. Pastikan kode prodi Anda terdaftar.");
            }
            // For Superadmin without filter, show prodi selection page or redirect
            header('Location: /admin/assessment/scores');
            exit;
        }

        // 3. Get Participants
        $participants = $db->query("SELECT p.*, 
            (SELECT SUM(score) FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = p.id AND c.type = 'BIDANG') as nilai_bidang_total
            FROM participants p 
            WHERE p.semester_id = ? AND p.status_berkas = 'lulus' AND p.status_pembayaran = 1 AND p.nama_prodi = ?
            ORDER BY p.nama_lengkap ASC")
            ->bind($semesterId, $prodiFilter)
            ->fetchAll();

        // 4. Get Components for this Prodi
        // We need components where prodi_id matches Session ID?
        // Or Global Bidang?
        // Component has 'prodi_id'.
        // Participants: 'nama_prodi' string.
        // We ideally need the 'prodi_id' of the filtered prodi.

        $prodiId = null;
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $prodiId = $_SESSION['admin_prodi_id'];
        } else {
            // Try to find ID from Name (Reverse lookup via participants or just ignore specific ID components if mismatch)
            // This is tricky. Let's rely on Admin Prodi session for now.
            // If Superadmin, maybe they can't see the specific components in this view unless we fix mapping.
        }

        $compParams = [];
        $compSql = "SELECT * FROM assessment_components WHERE type = 'BIDANG'";
        if ($prodiId) {
            $compSql .= " AND (prodi_id IS NULL OR prodi_id = ?)";
            $compParams[] = $prodiId;
        } else {
            $compSql .= " AND prodi_id IS NULL"; // Only Global if no ID
        }
        $compSql .= " ORDER BY nama_komponen ASC";

        $bidangComponents = $db->query($compSql)->bind(...$compParams)->fetchAll();

        echo View::render('admin.assessment.bidang_scores', [
            'participants' => $participants,
            'currentSemester' => $activeSemester,
            'prodiName' => $prodiName,
            'bidangComponents' => $bidangComponents
        ]);
    }

    /**
     * Export Bidang Assessment Report as Printable PDF
     */
    public function exportBidangReport()
    {
        $this->checkAuth();
        $db = Database::connection();

        // Get active semester
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            die("Error: Tidak ada semester aktif.");
        }
        $semesterId = $activeSemester['id'];

        // Determine Prodi
        $prodiFilter = null;
        $prodiName = 'Semua Prodi';

        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
            if ($kodeProdi) {
                $prodiData = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE kode_prodi = ? AND nama_prodi IS NOT NULL LIMIT 1")->bind($kodeProdi)->fetchAssoc();
                if ($prodiData && isset($prodiData['nama_prodi'])) {
                    $prodiFilter = $prodiData['nama_prodi'];
                    $prodiName = $prodiFilter;
                }
            }
        } else {
            $prodiFilter = Request::get('prodi');
            if ($prodiFilter) {
                $prodiName = $prodiFilter;
            }
        }

        if (!$prodiFilter) {
            die("Error: Prodi tidak ditemukan.");
        }

        // Get minimum threshold
        $minimumThreshold = 0;
        $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
        if ($kodeProdi) {
            $quota = $db->query("SELECT nilai_minimum_bidang FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
                ->bind($kodeProdi, $semesterId)->fetchAssoc();
            $minimumThreshold = $quota['nilai_minimum_bidang'] ?? 0;
        }

        // Get Bidang Components
        $compSql = "SELECT * FROM assessment_components WHERE type = 'BIDANG' AND (prodi_id IS NULL OR prodi_id = ?) ORDER BY nama_komponen ASC";
        $components = $db->query($compSql)->bind($kodeProdi)->fetchAll();

        // Get Participants with scores
        $participants = $db->query("SELECT p.* FROM participants p 
            WHERE p.semester_id = ? AND p.status_berkas = 'lulus' AND p.status_pembayaran = 1 AND p.nama_prodi = ?
            ORDER BY p.nama_lengkap ASC")
            ->bind($semesterId, $prodiFilter)->fetchAll();

        // Fetch scores for each participant
        foreach ($participants as &$p) {
            $p['scores'] = [];
            $scores = $db->query("SELECT component_id, score FROM assessment_scores WHERE participant_id = ?")
                ->bind($p['id'])->fetchAll();
            foreach ($scores as $s) {
                $p['scores'][$s['component_id']] = $s['score'];
            }
        }

        // Render the report view
        echo View::render('admin.assessment.bidang_report', [
            'participants' => $participants,
            'components' => $components,
            'semester' => $activeSemester,
            'prodiName' => $prodiName,
            'minimumThreshold' => $minimumThreshold
        ]);
    }

    /**
     * Reset all Bidang scores for Admin Prodi's prodi
     */
    public function resetBidangScores()
    {
        $this->checkAuth();

        // Only Admin Prodi can use this
        if (!\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin/assessment/scores?error=unauthorized');
            exit;
        }

        $db = Database::connection();
        $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;

        if (!$kodeProdi) {
            header('Location: /admin/assessment/bidang?error=no_prodi');
            exit;
        }

        // Get active semester
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            header('Location: /admin/assessment/bidang?error=no_semester');
            exit;
        }
        $semesterId = $activeSemester['id'];

        // Get prodi name from participants
        $prodiData = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE kode_prodi = ? AND nama_prodi IS NOT NULL LIMIT 1")->bind($kodeProdi)->fetchAssoc();
        $prodiName = $prodiData['nama_prodi'] ?? null;

        if (!$prodiName) {
            header('Location: /admin/assessment/bidang?error=no_prodi');
            exit;
        }

        // Get all participants for this prodi in this semester
        $participants = $db->query("SELECT id FROM participants WHERE semester_id = ? AND nama_prodi = ?")
            ->bind($semesterId, $prodiName)->fetchAll();

        // Get Bidang component IDs for this prodi
        $bidangComponents = $db->query("SELECT id FROM assessment_components WHERE type = 'BIDANG' AND (prodi_id IS NULL OR prodi_id = ?)")
            ->bind($kodeProdi)->fetchAll();
        $componentIds = array_column($bidangComponents, 'id');

        // Delete all Bidang scores for these participants
        if (!empty($participants) && !empty($componentIds)) {
            $participantIds = array_column($participants, 'id');
            foreach ($participantIds as $pid) {
                foreach ($componentIds as $cid) {
                    $db->query("DELETE FROM assessment_scores WHERE participant_id = ? AND component_id = ?")
                        ->bind($pid, $cid)->execute();
                }
            }
        }

        // Reset nilai_bidang_total and status_tes_bidang for ALL participants in this prodi
        $db->query("UPDATE participants SET nilai_bidang_total = 0, status_tes_bidang = NULL WHERE semester_id = ? AND nama_prodi = ?")
            ->bind($semesterId, $prodiName)->execute();

        header('Location: /admin/assessment/bidang?success=reset');
        exit;
    }

    public function saveMinimumThreshold()
    {
        $this->checkAuth();

        // Only Admin Prodi or Admin/Superadmin can set threshold
        $db = Database::connection();
        $threshold = intval(Request::get('nilai_minimum'));

        $kodeProdi = null;
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $kodeProdi = $_SESSION['admin_prodi_id'] ?? null;
        } else {
            $kodeProdi = Request::get('kode_prodi');
        }

        $activeSemester = Semester::getActive();
        $semesterId = $activeSemester['id'] ?? null;

        if (!$kodeProdi || !$semesterId) {
            header('Location: /admin/assessment/components?error=invalid_prodi');
            exit;
        }

        // Upsert into prodi_quotas
        $exist = $db->query("SELECT id FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
            ->bind($kodeProdi, $semesterId)->fetchAssoc();

        if ($exist) {
            $db->query("UPDATE prodi_quotas SET nilai_minimum_bidang = ? WHERE id = ?")
                ->bind($threshold, $exist['id'])->execute();
        } else {
            $db->query("INSERT INTO prodi_quotas (semester_id, kode_prodi, daya_tampung, nilai_minimum_bidang) VALUES (?, ?, 0, ?)")
                ->bind($semesterId, $kodeProdi, $threshold)->execute();
        }

        header('Location: /admin/assessment/components?success=threshold_saved');
        exit;
    }



    /**
     * Save Bidang Assessment Schedule (Admin/Superadmin only)
     * Supports per-prodi or global schedule
     */
    public function saveBidangSchedule()
    {
        $this->checkAuth();

        // Only Admin/Superadmin can set schedule
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin/assessment/bidang?error=unauthorized');
            exit;
        }

        $scheduleProdi = Request::get('schedule_prodi');
        $startDate = Request::get('start_date');
        $endDate = Request::get('end_date');
        $startTime = Request::get('start_time') ?: '00:00';
        $endTime = Request::get('end_time') ?: '23:59';

        if ($scheduleProdi === 'global' || empty($scheduleProdi)) {
            // Save to global settings
            \App\Models\Setting::set('bidang_schedule_start_date', $startDate);
            \App\Models\Setting::set('bidang_schedule_end_date', $endDate);
            \App\Models\Setting::set('bidang_schedule_start_time', $startTime);
            \App\Models\Setting::set('bidang_schedule_end_time', $endTime);
        } else {
            // Save to prodi_quotas for specific prodi
            $db = Database::connection();
            $activeSemester = Semester::getActive();
            $semesterId = $activeSemester['id'] ?? null;

            if ($semesterId) {
                // Check if prodi_quotas entry exists
                $exist = $db->query("SELECT id FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
                    ->bind($scheduleProdi, $semesterId)->fetchAssoc();

                if ($exist) {
                    $db->query("UPDATE prodi_quotas SET jadwal_mulai = ?, jadwal_selesai = ?, jam_mulai = ?, jam_selesai = ? WHERE id = ?")
                        ->bind($startDate ?: null, $endDate ?: null, $startTime, $endTime, $exist['id'])->execute();
                } else {
                    $db->query("INSERT INTO prodi_quotas (semester_id, kode_prodi, daya_tampung, jadwal_mulai, jadwal_selesai, jam_mulai, jam_selesai) VALUES (?, ?, 0, ?, ?, ?, ?)")
                        ->bind($semesterId, $scheduleProdi, $startDate ?: null, $endDate ?: null, $startTime, $endTime)->execute();
                }
            }
        }

        header('Location: /admin/assessment/scores?success=schedule_saved');
        exit;
    }

    public function storeComponent()
    {
        $this->checkAuth();

        $type = Request::get('type');
        $nama = Request::get('nama_komponen');
        $bobot = Request::get('bobot_persen');
        $prodiId = Request::get('prodi_id'); // Optional, mainly for Bidang

        if (empty($prodiId))
            $prodiId = null;

        // Enforce Logic: TPA is usually Global (prodi_id = null)
        if ($type === 'TPA') {
            $prodiId = null;
        }

        // If Admin Prodi, force prodi_id
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            $prodiId = $_SESSION['admin_prodi_id'];
            $type = 'BIDANG'; // Admin Prodi can only create BIDANG components
        }

        $db = Database::connection();
        $db->query("INSERT INTO assessment_components (type, nama_komponen, bobot_persen, prodi_id) VALUES (?, ?, ?, ?)")
            ->bind($type, $nama, $bobot, $prodiId)
            ->execute();

        header('Location: /admin/assessment/components?success=created');
        exit;
    }

    public function deleteComponent($id)
    {
        $this->checkAuth();

        $db = Database::connection();

        // Get component info before deleting (to know prodi for recalc)
        $comp = $db->query("SELECT * FROM assessment_components WHERE id = ? LIMIT 1")->bind($id)->fetchAssoc();
        if (!$comp) {
            header('Location: /admin/assessment/components?error=not_found');
            exit;
        }

        // Logic: Admin Prodi can only delete their own
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            if ($comp['prodi_id'] != $_SESSION['admin_prodi_id']) {
                header('Location: /admin/assessment/components?error=unauthorized');
                exit;
            }
        }

        // PROTECT TPA COMPONENTS: Only Superadmin can delete TPA components
        if ($comp['type'] === 'TPA') {
            if (!\App\Utils\RoleHelper::isSuperadmin()) {
                header('Location: /admin/assessment/components?error=unauthorized_tpa_deletion&msg=Hanya Superadmin yang dapat menghapus komponen TPA');
                exit;
            }
        }

        // Get affected participants (those who have scores for this component)
        $affectedParticipants = $db->query("SELECT DISTINCT participant_id FROM assessment_scores WHERE component_id = ?")->bind($id)->fetchAll();

        // Delete scores first
        $db->query("DELETE FROM assessment_scores WHERE component_id = ?")->bind($id)->execute();

        // Delete component
        $db->query("DELETE FROM assessment_components WHERE id = ?")->bind($id)->execute();

        // Recalculate totals for affected participants
        foreach ($affectedParticipants as $ap) {
            $participantId = $ap['participant_id'];

            // Recalculate Bidang Total
            $bidangScores = $db->query("SELECT s.score, c.bobot_persen FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = ? AND c.type = 'BIDANG'")->bind($participantId)->fetchAll();

            $bidangSum = 0;
            $hasWeights = false;

            foreach ($bidangScores as $s) {
                if ($s['bobot_persen'] > 0)
                    $hasWeights = true;
            }

            foreach ($bidangScores as $s) {
                if ($hasWeights && $s['bobot_persen'] > 0) {
                    $bidangSum += ($s['score'] * $s['bobot_persen'] / 100);
                } else if (!$hasWeights) {
                    $bidangSum += $s['score'];
                }
            }

            // If no more scores, reset total and status
            if (empty($bidangScores)) {
                $db->query("UPDATE participants SET nilai_bidang_total = 0, status_tes_bidang = NULL WHERE id = ?")->bind($participantId)->execute();
            } else {
                $db->query("UPDATE participants SET nilai_bidang_total = ? WHERE id = ?")->bind($bidangSum, $participantId)->execute();
            }
        }

        header('Location: /admin/assessment/components?success=deleted');
        exit;
    }

    // --- Score Input ---

    public function tpaScores()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $activeSemester = $semesterId ? (new Semester())->find($semesterId) : Semester::getActive();

        // 1. Get Participants for TPA Input (Filter: Exam Ready / Scheduled)
        // We want participants who have a schedule (ruang_ujian IS NOT NULL) or at least paid?
        // Usually TPA input happens after Exam.
        // Let's filter by semester.
        $db = Database::connection();

        // Fetch components for TPA (usually global, prodi_id IS NULL)
        $tpaComponents = $db->query("SELECT * FROM assessment_components WHERE type = 'TPA' ORDER BY id ASC")->fetchAll();

        // 2. Get Prodi List (Only needed if NOT Admin Prodi)
        $prodiList = [];
        $isAdminProdi = \App\Utils\RoleHelper::isAdminProdi();

        if (!$isAdminProdi) {
            $prodiList = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE semester_id = ? AND nama_prodi IS NOT NULL ORDER BY nama_prodi ASC")->bind($semesterId)->fetchAll();
        }

        // Count Participants
        // $total = $db->query("SELECT COUNT(*) as count FROM participants WHERE semester_id = ? AND status_berkas = 'lulus' AND status_pembayaran = 1")->bind($semesterId)->fetchAssoc()['count'];

        // DataTables API will handle the heavy lifting for list, 
        // but we need to pass TPA Components to the View for the Modal.

        echo View::render('admin.assessment.tpa', [
            'currentSemester' => $activeSemester['id'] ?? null,
            'semesterName' => $activeSemester['nama'] ?? '-',
            'tpaComponents' => $tpaComponents,
            'prodiList' => $prodiList,
            'isAdminProdi' => $isAdminProdi
        ]);
    }

    public function scores()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $prodiFilter = Request::get('prodi') ?? 'all';

        // Admin Prodi restriction: Redirect to specific Bidang page
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin/assessment/bidang');
            exit;
        }

        $db = Database::connection();
        $semesters = Semester::all();

        // Get Components for Columns
        $tpaComponents = $db->query("SELECT * FROM assessment_components WHERE type = 'TPA'")->fetchAll();
        $bidangComponents = $db->query("SELECT * FROM assessment_components WHERE type = 'BIDANG'")->fetchAll();
        $prodiList = $db->query("SELECT DISTINCT nama_prodi FROM participants WHERE semester_id = ? AND nama_prodi IS NOT NULL ORDER BY nama_prodi ASC")->bind($semesterId)->fetchAll();

        echo View::render('admin.assessment.scores', [
            'semesters' => $semesters,
            'currentSemester' => $semesterId,
            'prodiFilter' => $prodiFilter,
            'prodiList' => $prodiList,
            'tpaComponents' => $tpaComponents,
            'bidangComponents' => $bidangComponents
        ]);
    }

    public function apiData()
    {
        $this->checkAuth();

        $db = Database::connection();

        // DataTables parameters
        $draw = intval(Request::get('draw') ?? 1);
        $start = intval(Request::get('start') ?? 0);
        $length = intval(Request::get('length') ?? 10);
        $search = Request::get('search')['value'] ?? '';
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 0;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        // Filters
        $activeSemester = Semester::getActive();
        $semesterId = Request::get('semester_id') ?: ($activeSemester['id'] ?? null);
        $prodiFilter = Request::get('prodi') ?? 'all';

        // Enforce Prodi Filter if Admin Prodi
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            // We need to know the 'nama_prodi' string that corresponds to the admin's prodi_id (code)
            // Because participants table stores 'nama_prodi'.
            // Best way: Query 'prodi_quotas' or just use LIKE?
            // Or maybe participants table has 'kode_prodi'? Yes it does.

            // Let's use kode_prodi filter instead!
            // BUT DataTables View currently sends 'nama_prodi' in prompt.
            // Ideally we filter by kode_prodi = $_SESSION['admin_prodi_id']

            $prodiFilter = 'RESTRICTED_BY_SESSION';
        }

        $tpaFilter = Request::get('tpa_filter') ?? 'all';

        $columns = [
            0 => 'id',
            1 => 'nomor_peserta',
            2 => 'nama_lengkap',
            3 => 'nama_prodi',
            4 => 'nilai_tpa_total',
            5 => 'nilai_bidang_total',
            7 => 'status_kelulusan_akhir'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'id';

        // Base WHERE
        $whereClause = "WHERE p.semester_id = '$semesterId' AND p.status_berkas = 'lulus' AND p.status_pembayaran = 1";

        // Prodi Filter Logic
        if ($prodiFilter === 'RESTRICTED_BY_SESSION') {
            $adminProdiCode = $_SESSION['admin_prodi_id'];
            $whereClause .= " AND p.kode_prodi = '$adminProdiCode'";
        } elseif ($prodiFilter !== 'all') {
            $prodiFilterEscaped = str_replace("'", "''", $prodiFilter);
            $whereClause .= " AND p.nama_prodi = '$prodiFilterEscaped'";
        }

        // TPA Filter
        // Get TPA thresholds first for Filter usage
        $thresholdS2 = floatval(\App\Models\Setting::get('tpa_threshold_s2', 450));
        $thresholdS3 = floatval(\App\Models\Setting::get('tpa_threshold_s3', 500));

        // Provider Filter
        $providerFilter = Request::get('provider_filter') ?? 'all';
        if ($providerFilter !== 'all') {
            $providerEscaped = str_replace("'", "''", $providerFilter);
            if ($providerEscaped === 'PPKPP ULM') {
                // Include NULL as PPKPP ULM is default?
                $whereClause .= " AND (p.tpa_provider = 'PPKPP ULM' OR p.tpa_provider IS NULL)";
            } else {
                $whereClause .= " AND p.tpa_provider = '$providerEscaped'";
            }
        }

        if ($tpaFilter === 'empty') {
            $whereClause .= " AND (p.nilai_tpa_total IS NULL OR p.nilai_tpa_total = 0)";
        } elseif ($tpaFilter === 'below_min') {
            // Complex logic for S2 vs S3 threshold
            // S3 detection: 'S3' or 'Doktor' in nama_prodi or 'S3' in kode_prodi
            $s3Condition = "(p.nama_prodi LIKE '%S3%' OR p.nama_prodi LIKE '%Doktor%' OR p.kode_prodi LIKE '%S3%')";

            $whereClause .= " AND p.nilai_tpa_total > 0 AND (
                ($s3Condition AND p.nilai_tpa_total < $thresholdS3)
                OR
                (NOT $s3Condition AND p.nilai_tpa_total < $thresholdS2)
            )";
        }

        // Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (p.nama_lengkap LIKE '%$searchEscaped%' 
                             OR p.nomor_peserta LIKE '%$searchEscaped%'
                             OR p.nama_prodi LIKE '%$searchEscaped%')";
        }

        $totalRecordsSql = "SELECT COUNT(*) as total FROM participants p WHERE p.semester_id = '$semesterId' AND p.status_berkas = 'lulus' AND p.status_pembayaran = 1";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM participants p $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        // Get TPA thresholds
        $thresholdS2 = floatval(\App\Models\Setting::get('tpa_threshold_s2', 450));
        $thresholdS3 = floatval(\App\Models\Setting::get('tpa_threshold_s3', 500));

        $sql = "SELECT p.*, 
                p.tpa_provider, p.tpa_certificate_url,
                (SELECT SUM(score) FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = p.id AND c.type = 'TPA') as tpa_score_saved,
                (SELECT SUM(score) FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = p.id AND c.type = 'BIDANG') as bidang_score_saved
                FROM participants p $whereClause 
                ORDER BY $orderBy $orderDir 
                LIMIT $length OFFSET $start";
        $data = $db->query($sql)->fetchAll();

        // Process each row for threshold logic and default keputusan
        foreach ($data as &$p) {
            // Detect jenjang from prodi name (S2/S3)
            $jenjang = 'S2';
            if (
                stripos($p['nama_prodi'] ?? '', 'Doktor') !== false ||
                stripos($p['nama_prodi'] ?? '', 'S3') !== false ||
                stripos($p['kode_prodi'] ?? '', 'S3') !== false
            ) {
                $jenjang = 'S3';
            }
            $p['jenjang'] = $jenjang;

            // Calculate rekomendasi_tpa based on threshold
            $tpaValue = floatval($p['nilai_tpa_total'] ?? 0);
            $threshold = ($jenjang === 'S3') ? $thresholdS3 : $thresholdS2;
            $p['tpa_threshold'] = $threshold;
            $p['rekomendasi_tpa'] = ($tpaValue >= $threshold) ? 'L' : (($tpaValue > 0) ? 'TL' : '-');

            // Default keputusan_akhir based on status_tes_bidang
            if (empty($p['keputusan_akhir'])) {
                if ($p['status_tes_bidang'] === 'tidak_lulus') {
                    $p['keputusan_akhir'] = 'TL';
                } elseif ($p['status_tes_bidang'] === 'lulus') {
                    $p['keputusan_akhir'] = 'L';
                } else {
                    $p['keputusan_akhir'] = null;
                }
            }
        }

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
            "thresholdS2" => $thresholdS2,
            "thresholdS3" => $thresholdS3
        ]);
    }

    public function saveScore($participantId)
    {
        $this->checkAuth();

        // POST data: [component_id] => score
        $scores = Request::body(); // or $_POST
        unset($scores['csrf_token']); // if exists

        $db = Database::connection();

        // Handle Status Tes Bidang
        // Only Admin Prodi or Superadmin can set this.
        // If passed in request, update it.
        if (isset($scores['status_tes_bidang'])) {
            $status = in_array($scores['status_tes_bidang'], ['lulus', 'tidak_lulus']) ? $scores['status_tes_bidang'] : null;
            // Update immediately
            $db->query("UPDATE participants SET status_tes_bidang = ? WHERE id = ?")->bind($status, $participantId)->execute();
        }

        // --- Handle TPA Provider & Certificate ---
        if (isset($scores['tpa_provider'])) {
            // SECURITY: Block Admin Prodi from changing TPA
            if (\App\Utils\RoleHelper::isAdminProdi()) {
                http_response_code(403);
                echo "Unauthorized: Admin Prodi cannot change TPA scores.";
                exit;
            }

            $provider = $scores['tpa_provider'];
            $db->query("UPDATE participants SET tpa_provider = ? WHERE id = ?")->bind($provider, $participantId)->execute();

            // Handle Certificate Upload
            if (isset($_FILES['tpa_certificate']) && $_FILES['tpa_certificate']['error'] === UPLOAD_ERR_OK) {
                // Get Participant Semester for Storage Organization
                $pSem = $db->query("SELECT semester_id FROM participants WHERE id = ?")->bind($participantId)->fetchAssoc();
                $semId = $pSem['semester_id'] ?? 'unknown'; // Fallback if missing

                $file = $_FILES['tpa_certificate'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'tpa_cert_' . $participantId . '_' . time() . '.' . $ext;

                // Storage Path: storage/{semester_id}/documents/tpa/
                // Note: __DIR__ is app/Controllers, so storage is at ../../storage
                $uploadDir = __DIR__ . '/../../storage/' . $semId . '/documents/tpa/';

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $db->query("UPDATE participants SET tpa_certificate_url = ? WHERE id = ?")->bind($filename, $participantId)->execute();
                }
            }

            // If Provider is NOT PPKPP ULM, we take the manual final score
            if ($provider !== 'PPKPP ULM' && isset($scores['manual_tpa_score'])) {
                $manualScore = floatval($scores['manual_tpa_score']);
                $db->query("UPDATE participants SET nilai_tpa_total = ? WHERE id = ?")->bind($manualScore, $participantId)->execute();

                // We should also clear any specific TPA component scores to avoid double counting or confusion
                // But keeping them might be safer as history? 
                // Let's decided: If using external, we assume NO component scores matter. 
                // But to reset components, we'd need to fetch TPA components and delete scores.
                // For now, let's just rely on nilai_tpa_total being authoritative.
            }
        }

        $totalTPA = 0;
        $totalBidang = 0;

        foreach ($scores as $key => $val) {
            if (strpos($key, 'comp_') === 0) {
                $compId = substr($key, 5);
                $score = floatval($val);

                // Get Component Type
                $comp = $db->query("SELECT * FROM assessment_components WHERE id = ?")->bind($compId)->fetchAssoc();
                if (!$comp)
                    continue;

                // Permission Check:
                // TPA: Admin/Superadmin ONLY.
                // Bidang: Admin/Superadmin/Prodi.
                if ($comp['type'] === 'TPA') {
                    if (\App\Utils\RoleHelper::isAdminProdi())
                        continue; // Skip if prodi admin

                    // If TPA Provider is External, we SKIP saving component scores to avoid overriding the manual total
                    // (Assuming the UI hides them, but backend should verify)
                    if (isset($scores['tpa_provider']) && $scores['tpa_provider'] !== 'PPKPP ULM') {
                        continue;
                    }
                }

                // Save Score (Upsert logic or Delete-Insert)
                // Check existing
                $exist = $db->query("SELECT * FROM assessment_scores WHERE participant_id = ? AND component_id = ?")->bind($participantId, $compId)->fetchAssoc();

                if ($exist) {
                    $db->query("UPDATE assessment_scores SET score = ?, created_by = ? WHERE id = ?")
                        ->bind($score, $_SESSION['admin_username'] ?? 'admin', $exist['id'])->execute();
                } else {
                    $db->query("INSERT INTO assessment_scores (participant_id, component_id, score, created_by) VALUES (?, ?, ?, ?)")
                        ->bind($participantId, $compId, $score, $_SESSION['admin_username'] ?? 'admin')->execute();
                }

                if ($comp['type'] === 'TPA')
                    $totalTPA += $score;
                if ($comp['type'] === 'BIDANG')
                    $totalBidang += $score;
            }
        }

        // Update Total in Participants Table (Cache for easy reading)
        // Note: For partial updates, we should re-calculate total from DB.

        // Re-calc Total TPA (Sum always)
        // ONLY if Provider is PPKPP ULM (or not set)
        // If Provider is External, we TRUST the manual total we just set above or exists in DB.
        $pCheck = $db->query("SELECT tpa_provider, nilai_tpa_total FROM participants WHERE id = ?")->bind($participantId)->fetchAssoc();
        $tpaSum = 0; // Initialize

        if (!$pCheck || $pCheck['tpa_provider'] === 'PPKPP ULM') {
            $tpaScores = $db->query("SELECT s.score FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = ? AND c.type = 'TPA'")->bind($participantId)->fetchAll();
            foreach ($tpaScores as $s) {
                $tpaSum += $s['score'];
            }
            $db->query("UPDATE participants SET nilai_tpa_total = ? WHERE id = ?")->bind($tpaSum, $participantId)->execute();
        } else {
            // Use existing manual value for external providers
            $tpaSum = $pCheck['nilai_tpa_total'] ?? 0;
            // Note: If we just updated it above (lines 806-837), pCheck might be stale if triggered before commit?
            // Actually, pCheck query happens NOW, so it should see the update if transaction committed or same connection.
            // But wait, line 829 updates `nilai_tpa_total` manually.
            // So fetching it here is correct.
        }

        // Re-calc Total Bidang (Weighted if weights exist, else Sum)
        $bidangScores = $db->query("SELECT s.score, c.bobot_persen FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = ? AND c.type = 'BIDANG'")->bind($participantId)->fetchAll();

        $bidangSum = 0;
        $hasWeights = false;

        // Check if any has weight > 0
        foreach ($bidangScores as $s) {
            if ($s['bobot_persen'] > 0)
                $hasWeights = true;
        }

        foreach ($bidangScores as $s) {
            if ($hasWeights) {
                // Weighted Sum logic: Score * Weight / 100
                // Assuming weights sum to 100.
                if ($s['bobot_persen'] > 0) {
                    $bidangSum += ($s['score'] * $s['bobot_persen'] / 100);
                }
                // If weight 0 in a weighted system, it contributes 0.
            } else {
                $bidangSum += $s['score'];
            }
        }

        $db->query("UPDATE participants SET nilai_tpa_total = ?, nilai_bidang_total = ? WHERE id = ?")
            ->bind($tpaSum, $bidangSum, $participantId)
            ->execute();

        // Auto-suggest status_tes_bidang based on prodi threshold
        // Only if threshold > 0 and status was NOT explicitly set in this request
        if (!isset($scores['status_tes_bidang'])) {
            // Get participant's kode_prodi
            $participant = $db->query("SELECT kode_prodi FROM participants WHERE id = ? LIMIT 1")
                ->bind($participantId)->fetchAssoc();
            $kodeProdi = $participant['kode_prodi'] ?? null;

            // Get active semester
            $activeSemester = Semester::getActive();
            $semesterId = $activeSemester['id'] ?? null;

            if ($kodeProdi && $semesterId) {
                $quota = $db->query("SELECT nilai_minimum_bidang FROM prodi_quotas WHERE kode_prodi = ? AND semester_id = ? LIMIT 1")
                    ->bind($kodeProdi, $semesterId)->fetchAssoc();
                $threshold = intval($quota['nilai_minimum_bidang'] ?? 0);

                if ($threshold > 0) {
                    // Auto-suggest based on threshold
                    $autoStatus = ($bidangSum >= $threshold) ? 'lulus' : 'tidak_lulus';
                    $db->query("UPDATE participants SET status_tes_bidang = ? WHERE id = ?")
                        ->bind($autoStatus, $participantId)->execute();
                }
            }
        }

        // Redirect based on source
        // Redirect based on source
        $from = Request::get('from');
        if ($from === 'bidang') {
            header("Location: /admin/assessment/bidang?success=saved");
        } elseif ($from === 'tpa') {
            header("Location: /admin/assessment/tpa?success=saved");
        } elseif (\App\Utils\RoleHelper::isAdminProdi()) {
            // Fallback for Admin Prodi
            header("Location: /admin/assessment/tpa?success=saved");
        } else {
            header("Location: /admin/assessment/scores?success=saved&participant=$participantId");
        }
        exit;
    }

    public function getScores($participantId)
    {
        $this->checkAuth();

        $db = Database::connection();
        $scores = $db->query("SELECT component_id, score FROM assessment_scores WHERE participant_id = ?")->bind($participantId)->fetchAll();
        $participant = $db->query("SELECT status_tes_bidang, tpa_provider, tpa_certificate_url, nilai_tpa_total FROM participants WHERE id = ?")->bind($participantId)->fetchAssoc();

        // Return JSON
        header('Content-Type: application/json');
        echo json_encode([
            'scores' => $scores,
            'status_tes_bidang' => $participant['status_tes_bidang'] ?? null,
            'tpa_provider' => $participant['tpa_provider'] ?? 'PPKPP ULM',
            'tpa_certificate_url' => $participant['tpa_certificate_url'] ?? null,
            'nilai_tpa_total' => $participant['nilai_tpa_total'] ?? 0
        ]);
        exit;
    }

    /**
     * Export Hasil Akhir - Clean Excel for BAAK Submission
     */
    public function exportFinal()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $prodiFilter = Request::get('prodi') ?? 'all';

        $db = Database::connection();

        // Get semester info for filename
        $semester = $db->query("SELECT nama, kode FROM semesters WHERE id = ?")->bind($semesterId)->fetchAssoc();
        $semesterName = $semester['kode'] ?? 'unknown';

        // Get TPA thresholds
        $thresholdS2 = floatval(\App\Models\Setting::get('tpa_threshold_s2', 450));
        $thresholdS3 = floatval(\App\Models\Setting::get('tpa_threshold_s3', 500));

        // Fetch Participants with scores
        $sql = "SELECT p.nomor_peserta, p.nama_lengkap, p.email, p.no_hp, p.nama_prodi, p.kode_prodi,
                       p.nilai_tpa_total, p.nilai_bidang_total, p.status_tes_bidang, p.keputusan_akhir
                FROM participants p 
                WHERE p.semester_id = ? 
                AND p.status_berkas = 'lulus' 
                AND p.status_pembayaran = 1";

        $params = [$semesterId];
        if ($prodiFilter !== 'all') {
            $sql .= " AND p.nama_prodi = ?";
            $params[] = $prodiFilter;
        }
        $sql .= " ORDER BY p.nama_prodi ASC, p.nilai_tpa_total DESC, p.nilai_bidang_total DESC";

        $participants = $db->query($sql)->bind(...$params)->fetchAll();

        // Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Penilaian');

        // Headers (12 columns: A-L)
        $headers = [
            'NO',
            'NO PESERTA',
            'NAMA LENGKAP',
            'EMAIL',
            'NO HP',
            'PROGRAM STUDI',
            'NILAI TPA',
            'REKOMENDASI TPA',
            'NILAI BIDANG',
            'STATUS BIDANG',
            'KEPUTUSAN AKHIR',
            'KETERANGAN'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style Header
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Data
        $row = 2;
        $no = 1;
        foreach ($participants as $p) {
            // Detect jenjang
            $jenjang = 'S2';
            if (
                stripos($p['nama_prodi'] ?? '', 'Doktor') !== false ||
                stripos($p['nama_prodi'] ?? '', 'S3') !== false ||
                stripos($p['kode_prodi'] ?? '', 'S3') !== false
            ) {
                $jenjang = 'S3';
            }
            $threshold = ($jenjang === 'S3') ? $thresholdS3 : $thresholdS2;

            // Calculate TPA recommendation
            $tpaValue = floatval($p['nilai_tpa_total'] ?? 0);
            $rekomendasiTpa = ($tpaValue >= $threshold) ? 'L' : (($tpaValue > 0) ? 'TL' : '-');

            // Status Bidang
            $statusBidang = '-';
            if ($p['status_tes_bidang'] === 'lulus')
                $statusBidang = 'L';
            elseif ($p['status_tes_bidang'] === 'tidak_lulus')
                $statusBidang = 'TL';

            // Keputusan
            $keputusan = $p['keputusan_akhir'] ?? '-';
            $keterangan = '';
            if ($keputusan === 'L')
                $keterangan = 'Lulus';
            elseif ($keputusan === 'TL')
                $keterangan = 'Tidak Lulus';
            elseif ($keputusan === 'T')
                $keterangan = 'Tertunda';

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $p['nomor_peserta'] ?? '-');
            $sheet->setCellValue('C' . $row, $p['nama_lengkap']);
            $sheet->setCellValue('D' . $row, $p['email']);
            $sheet->setCellValue('E' . $row, $p['no_hp'] ?? '-');
            $sheet->setCellValue('F' . $row, $p['nama_prodi']);
            $sheet->setCellValue('G' . $row, $p['nilai_tpa_total'] ?? 0);
            $sheet->setCellValue('H' . $row, $rekomendasiTpa . " (min: {$threshold})");
            $sheet->setCellValue('I' . $row, $p['nilai_bidang_total'] ?? '-');
            $sheet->setCellValue('J' . $row, $statusBidang);
            $sheet->setCellValue('K' . $row, $keputusan);
            $sheet->setCellValue('L' . $row, $keterangan);
            $row++;
            $no++;
        }

        // AutoSize columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $filename = "Hasil_Penilaian_{$semesterName}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- Excel Import/Export ---

    public function exportTemplate()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $prodiFilter = Request::get('prodi') ?? 'all';

        $db = Database::connection();

        // 1. Fetch Participants
        $sql = "SELECT p.id, p.nomor_peserta, p.nama_lengkap, p.nama_prodi, p.status_tes_bidang 
                FROM participants p 
                WHERE p.semester_id = ? 
                AND p.status_berkas = 'lulus' 
                AND p.status_pembayaran = 1";

        $params = [$semesterId];
        if ($prodiFilter !== 'all') {
            $sql .= " AND p.nama_prodi = ?";
            $params[] = $prodiFilter;
        }
        $sql .= " ORDER BY p.nama_prodi ASC, p.nama_lengkap ASC";

        $participants = $db->query($sql)->bind(...$params)->fetchAll();

        // 2. Fetch Components (All pertinent ones)
        // If Admin Prodi, logic applies.
        $compSql = "SELECT * FROM assessment_components ORDER BY type DESC, nama_komponen ASC";
        $components = $db->query($compSql)->fetchAll();

        // 3. Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 4. Headers
        // Headers: A=ID, B=No, C=Nama, D=Prodi. Start Components at E (5).
        $headers = ['ID_SYSTEM (JANGAN DIUBAH)', 'NO PESERTA', 'NAMA', 'PRODI'];
        $sheet->setCellValue('A1', $headers[0]);
        $sheet->setCellValue('B1', $headers[1]);
        $sheet->setCellValue('C1', $headers[2]);
        $sheet->setCellValue('D1', $headers[3]);

        $colIndex = 5; // Start at E

        // Map Component ID to Column
        $compMap = [];

        foreach ($components as $c) {
            // Header Format: "Nama Komponen [ID:123]"
            $headerTitle = $c['nama_komponen'] . " [" . $c['type'] . "] [ID:" . $c['id'] . "]";
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $headerTitle);
            $compMap[$colIndex] = $c;
            $colIndex++;
        }

        // Add Status Column at the END
        $statusColIndex = $colIndex;
        $sheet->setCellValueByColumnAndRow($statusColIndex, 1, 'STATUS REKOMENDASI (L/TL)');
        $statusColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($statusColIndex);

        // Style Header
        $sheet->getStyle("A1:{$statusColLetter}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$statusColLetter}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');
        // AutoSize
        foreach (range('A', $statusColLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5. Populate Data
        $row = 2;
        foreach ($participants as $p) {
            $sheet->setCellValue('A' . $row, $p['id']);
            $sheet->setCellValue('B' . $row, $p['nomor_peserta']);
            $sheet->setCellValue('C' . $row, $p['nama_lengkap']);
            $sheet->setCellValue('D' . $row, $p['nama_prodi']);

            // Status value
            $statusShort = '';
            if ($p['status_tes_bidang'] === 'lulus')
                $statusShort = 'L';
            if ($p['status_tes_bidang'] === 'tidak_lulus')
                $statusShort = 'TL';

            // Fetch existing scores
            $scores = $db->query("SELECT component_id, score FROM assessment_scores WHERE participant_id = ?")->bind($p['id'])->fetchAll();
            $scoreMap = [];
            foreach ($scores as $s)
                $scoreMap[$s['component_id']] = $s['score'];

            // Loop columns to fill scores keys from E (5) to statusColIndex-1
            // We iterate compMap which maps colIndex to component
            foreach ($compMap as $cIndex => $component) {
                if (isset($scoreMap[$component['id']])) {
                    $sheet->setCellValueByColumnAndRow($cIndex, $row, $scoreMap[$component['id']]);
                }
            }

            // Set Status at End
            $sheet->setCellValueByColumnAndRow($statusColIndex, $row, $statusShort);

            $row++;
        }

        // 6. Protection Logic
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('PmbPascaSafe');

        // Unlock Score Columns (E to Status-1) and Status Column
        $lastRow = $row - 1;
        // Unlock E (5) to StatusColIndex
        // We can use coordinate logic or loop
        for ($r = 2; $r <= $lastRow; $r++) {
            // Unlock Scores
            for ($c = 5; $c < $statusColIndex; $c++) {
                $cell = $sheet->getCellByColumnAndRow($c, $r);
                $cell->getStyle()->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
            }
            // Unlock Status
            $cell = $sheet->getCellByColumnAndRow($statusColIndex, $r);
            $cell->getStyle()->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

            // Validation for Status
            $validation = $cell->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Nilai harus L atau TL.');
            $validation->setPromptTitle('Pilih Status');
            $validation->setFormula1('"L,TL"');
        }

        // If Admin Prodi: Can only edit BIDANG columns.
        // We iterate components to check which columns to unlock.
        $currCol = 5; // Start from E
        foreach ($components as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currCol);

            $canEdit = true;
            // Restriction: Admin Prodi cannot edit TPA
            if (\App\Utils\RoleHelper::isAdminProdi() && $c['type'] === 'TPA') {
                $canEdit = false;
            }

            if ($canEdit) {
                $sheet->getStyle($colLetter . '2:' . $colLetter . $lastRow)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
            }
            $currCol++;
        }

        // Hide ID Column?
        // $sheet->getColumnDimension('A')->setVisible(false); // User might modify hidden accidentally, but safer to keep visible as ID reference.

        // Output
        $filename = 'Template_Nilai_Pasca.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importScores()
    {
        $this->checkAuth();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
            header('Location: /admin/assessment/scores?error=upload_failed');
            exit;
        }

        $file = $_FILES['file']['tmp_name'];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            header('Location: /admin/assessment/scores?error=invalid_file');
            exit;
        }

        $db = Database::connection();
        $updatedCount = 0;

        // Parse Header (Row 0)
        $header = $rows[0];
        $colMap = []; // Col Index => Component ID
        $statusIdx = -1; // Initialize with an invalid index

        foreach ($header as $index => $val) {
            $valClean = trim($val);
            // Regex to find [ID:123]
            if (preg_match('/\[ID:(\d+)\]/', $valClean, $matches)) {
                $colMap[$index] = $matches[1];
            }
            // Find Status Column
            if (strpos(strtoupper($valClean), 'STATUS REKOMENDASI') !== false) {
                $statusIdx = $index;
            }
        }

        // Process Rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $id = $row[0]; // ID System

            if (empty($id))
                continue;

            // Optional: Verify ID exists
            // Update Status Rekomendasi (Use dynamic index)
            if ($statusIdx !== -1) { // Only process if status column was found
                $statusRaw = isset($row[$statusIdx]) ? strtoupper(trim($row[$statusIdx])) : '';
                $status = null;
                if ($statusRaw === 'L')
                    $status = 'lulus';
                if ($statusRaw === 'TL')
                    $status = 'tidak_lulus';

                if ($status) {
                    $db->query("UPDATE participants SET status_tes_bidang = ? WHERE id = ?")->bind($status, $id)->execute();
                }
            }
            // Update Scores
            foreach ($colMap as $colIndex => $compId) {
                $scoreVal = $row[$colIndex];

                // If empty, skip or set 0? Excel empty usually means no change or 0? 
                // Let's assume if numeric update.
                if (is_numeric($scoreVal)) {
                    // Check logic permission (Double check backend side)
                    $comp = $db->query("SELECT * FROM assessment_components WHERE id = ?")->bind($compId)->fetchAssoc();
                    if (!$comp)
                        continue;

                    // Restriction: Admin Prodi cannot edit TPA via Import
                    if (\App\Utils\RoleHelper::isAdminProdi() && $comp['type'] === 'TPA') {
                        continue;
                    }

                    // Upsert Score
                    $exist = $db->query("SELECT id FROM assessment_scores WHERE participant_id = ? AND component_id = ?")->bind($id, $compId)->fetchAssoc();
                    if ($exist) {
                        $db->query("UPDATE assessment_scores SET score = ?, created_by = ? WHERE id = ?")
                            ->bind($scoreVal, 'import', $exist['id'])->execute();
                    } else {
                        $db->query("INSERT INTO assessment_scores (participant_id, component_id, score, created_by) VALUES (?, ?, ?, ?)")
                            ->bind($id, $compId, $scoreVal, 'import')->execute();
                    }
                }
            }

            // Recalculate Totals for this ID
            $this->recalculateTotals($id);
            $updatedCount++;
        }

        header("Location: /admin/assessment/scores?success=imported_tpa&count=$updatedCount");
        exit;
    }

    private function recalculateTotals($participantId)
    {
        $db = Database::connection();

        // Recalc Total TPA (AVERAGE)
        $tpaScores = $db->query("SELECT s.score FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = ? AND c.type = 'TPA'")->bind($participantId)->fetchAll();
        $tpaSum = 0;
        $tpaCount = count($tpaScores);
        foreach ($tpaScores as $s)
            $tpaSum += $s['score'];

        // Logic: Average if multiple components, else Sum?
        // User example implied Average (1515 / 3 = 505).
        $tpaTotal = $tpaCount > 0 ? round($tpaSum / $tpaCount) : 0;

        // Recalc Total Bidang (Weighted Sum)
        $bidangScores = $db->query("SELECT s.score, c.bobot_persen FROM assessment_scores s JOIN assessment_components c ON s.component_id = c.id WHERE s.participant_id = ? AND c.type = 'BIDANG'")->bind($participantId)->fetchAll();
        $bidangSum = 0;
        $hasWeights = false;
        foreach ($bidangScores as $s)
            if ($s['bobot_persen'] > 0)
                $hasWeights = true;

        foreach ($bidangScores as $s) {
            if ($hasWeights) {
                if ($s['bobot_persen'] > 0)
                    $bidangSum += ($s['score'] * $s['bobot_persen'] / 100);
            } else {
                $bidangSum += $s['score'];
            }
        }

        // Update value. ALWAYS set tpa_provider = 'PPKPP ULM' if we are calculating from components.
        // Because Components = Internal Test.
        $db->query("UPDATE participants SET nilai_tpa_total = ?, nilai_bidang_total = ?, tpa_provider = COALESCE(tpa_provider, 'PPKPP ULM') WHERE id = ?")
            ->bind($tpaTotal, $bidangSum, $participantId)
            ->execute();
    }

    public function saveFinalDecision()
    {
        $this->checkAuth();
        $db = Database::connection();

        $inputs = Request::body();
        $decisions = $inputs['decision'] ?? [];

        // Validation
        if (empty($decisions)) {
            header('Location: /admin/assessment/scores?error=no_data');
            exit;
        }

        // Logic: Map ID => Status (L, TL, T)
        foreach ($decisions as $id => $status) {
            // Sanitize status - only accept L, TL, T, or empty
            if (!in_array($status, ['L', 'TL', 'T', ''])) {
                continue;
            }

            $statusValue = $status === '' ? null : $status;
            $db->query("UPDATE participants SET keputusan_akhir = ? WHERE id = ?")
                ->bind($statusValue, $id)->execute();
        }

        $semesterId = $inputs['semester_id'] ?? '';
        $prodi = $inputs['prodi_filter'] ?? 'all';

        header("Location: /admin/assessment/scores?semester_id=$semesterId&prodi=$prodi&success=final_saved");
        exit;
    }

    /**
     * Import Keputusan Akhir from Excel
     * Reads NO PESERTA (Column B) and KEPUTUSAN AKHIR (Column K)
     */
    public function importTPA()
    {
        $this->checkAuth();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
            header('Location: /admin/assessment/tpa?error=upload_failed');
            exit;
        }

        $file = $_FILES['file']['tmp_name'];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            header('Location: /admin/assessment/tpa?error=invalid_file');
            exit;
        }

        $db = Database::connection();
        $updatedCount = 0;

        // Parse Header (Row 0)
        $header = array_map(function ($v) {
            return strtolower(trim($v ?? ''));
        }, $rows[0] ?? []);

        $colIndexNum = array_search('nomor peserta', $header);
        if ($colIndexNum === false)
            $colIndexNum = array_search('no peserta', $header);

        if ($colIndexNum === false) {
            header('Location: /admin/assessment/tpa?error=format_invalid&msg=Kolom Nomor Peserta tidak ditemukan');
            exit;
        }

        // Map Component Names to IDs for TPA
        $tpaComponents = $db->query("SELECT id, nama_komponen FROM assessment_components WHERE type = 'TPA'")->fetchAll();
        $compMap = [];
        foreach ($tpaComponents as $comp) {
            $name = strtolower($comp['nama_komponen']);
            foreach ($header as $idx => $h) {
                if (strpos($h, $name) !== false) {
                    $compMap[$idx] = $comp['id'];
                }
            }
        }

        // Check for "Nilai TPA" (Direct Import)
        $colIndexTotal = array_search('nilai tpa', $header);
        if ($colIndexTotal === false)
            $colIndexTotal = array_search('total tpa', $header);

        // Check for "Penyelenggara" column
        $colIndexProvider = array_search('penyelenggara', $header);
        if ($colIndexProvider === false)
            $colIndexProvider = array_search('provider', $header);

        // Process Rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $nomorPeserta = trim($row[$colIndexNum] ?? '');

            if (empty($nomorPeserta))
                continue;

            $participant = $db->query("SELECT id FROM participants WHERE nomor_peserta = ? LIMIT 1")->bind($nomorPeserta)->fetchAssoc();
            if (!$participant)
                continue;

            $participantId = $participant['id'];
            $isDirectImport = false;

            // 1. Try Direct Total Import
            if ($colIndexTotal !== false && !empty($row[$colIndexTotal])) {
                $totalScore = floatval($row[$colIndexTotal]);
                // Provider: from excel OR default 'External'
                $provider = ($colIndexProvider !== false && !empty($row[$colIndexProvider]))
                    ? trim($row[$colIndexProvider])
                    : 'External';

                $db->query("UPDATE participants SET nilai_tpa_total = ?, tpa_provider = ? WHERE id = ?")
                    ->bind($totalScore, $provider, $participantId)->execute();

                $updatedCount++;
                $isDirectImport = true;
            }

            // 2. If NOT direct import, try Component Import
            if (!$isDirectImport) {
                $hasScoreUpdate = false;
                foreach ($compMap as $idx => $compId) {
                    $score = floatval($row[$idx] ?? 0);
                    if ($score > 0) {
                        $exist = $db->query("SELECT id FROM assessment_scores WHERE participant_id = ? AND component_id = ?")->bind($participantId, $compId)->fetchAssoc();
                        if ($exist) {
                            $db->query("UPDATE assessment_scores SET score = ?, created_by = ? WHERE id = ?")
                                ->bind($score, 'import_cat', $exist['id'])->execute();
                        } else {
                            $db->query("INSERT INTO assessment_scores (participant_id, component_id, score, created_by) VALUES (?, ?, ?, ?)")
                                ->bind($participantId, $compId, $score, 'import_cat')->execute();
                        }
                        $hasScoreUpdate = true;
                    }
                }

                // Recalculate Total TPA
                if ($hasScoreUpdate) {
                    $updatedCount++;
                    $result = $db->query("SELECT SUM(score) as total, COUNT(*) as count FROM assessment_scores s 
                                    JOIN assessment_components c ON s.component_id = c.id 
                                    WHERE s.participant_id = ? AND c.type = 'TPA'")
                        ->bind($participantId)->fetchAssoc();

                    $sum = floatval($result['total'] ?? 0);
                    $count = intval($result['count'] ?? 1);
                    $average = $count > 0 ? round($sum / $count) : 0;

                    $db->query("UPDATE participants SET nilai_tpa_total = ?, tpa_provider = ? WHERE id = ?")
                        ->bind($average, 'PPKPP ULM', $participantId)->execute();
                }
            }
        }

        header("Location: /admin/assessment/tpa?success=imported&count=$updatedCount");
        exit;
    }

    public function exportTPATemplate()
    {
        $this->checkAuth();

        $semesterId = Request::get('semester_id') ?? Semester::getActive()['id'] ?? null;
        $db = Database::connection();

        // 1. Fetch Participants (Status Lulus Berkas & Bayar)
        $sql = "SELECT p.nomor_peserta, p.nama_lengkap, p.nama_prodi 
                FROM participants p 
                WHERE p.semester_id = ? 
                AND p.status_berkas = 'lulus' 
                AND p.status_pembayaran = 1
                ORDER BY p.nama_prodi ASC, p.nama_lengkap ASC";

        $participants = $db->query($sql)->bind($semesterId)->fetchAll();

        // 2. Fetch TPA Components
        $tpaComponents = $db->query("SELECT * FROM assessment_components WHERE type = 'TPA' ORDER BY id ASC")->fetchAll();

        // 3. Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template TPA');

        // 4. Headers
        $headers = ['NOMOR PESERTA', 'NAMA', 'PRODI'];
        $colIndex = 1;

        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $h);
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        // TPA Component Columns
        foreach ($tpaComponents as $c) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $c['nama_komponen']);
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
            $colIndex++;
        }

        // Add Optional Columns for External Provider
        $sheet->setCellValueByColumnAndRow($colIndex, 1, 'Nilai TPA (Opsional)');
        $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        $colIndex++;

        $sheet->setCellValueByColumnAndRow($colIndex, 1, 'Penyelenggara (Opsional)');
        $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        $colIndex++;

        // Style Header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        // 5. Populate Data
        $row = 2;
        foreach ($participants as $p) {
            $sheet->setCellValue('A' . $row, $p['nomor_peserta']);
            $sheet->setCellValue('B' . $row, $p['nama_lengkap']);
            $sheet->setCellValue('C' . $row, $p['nama_prodi']);
            // Leave score columns empty for input
            $row++;
        }

        // Output
        $filename = 'Template_Input_TPA.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importFinal()
    {
        $this->checkAuth();

        // Only Admin/Superadmin
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin/assessment/scores?error=unauthorized');
            exit;
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            header('Location: /admin/assessment/scores?error=upload_failed');
            exit;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $db = Database::connection();
            $updatedCount = 0;
            $skippedCount = 0;

            // Skip header row (row 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Column B (index 1) = NO PESERTA
                // Column K (index 10) = KEPUTUSAN AKHIR
                $nomorPeserta = trim($row[1] ?? '');
                $keputusan = strtoupper(trim($row[10] ?? ''));

                if (empty($nomorPeserta)) {
                    $skippedCount++;
                    continue;
                }

                // Validate keputusan (only L, TL, T, or empty/-)
                if (!in_array($keputusan, ['L', 'TL', 'T', '-', ''])) {
                    $skippedCount++;
                    continue;
                }

                $keputusanValue = ($keputusan === '-' || $keputusan === '') ? null : $keputusan;

                // Update participant by nomor_peserta
                $result = $db->query("UPDATE participants SET keputusan_akhir = ? WHERE nomor_peserta = ?")
                    ->bind($keputusanValue, $nomorPeserta)->execute();

                if ($result) {
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }

            header("Location: /admin/assessment/scores?success=import_final&updated=$updatedCount&skipped=$skippedCount");
            exit;

        } catch (\Exception $e) {
            header('Location: /admin/assessment/scores?error=import_error&msg=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function tpaCertificate($participantId)
    {
        $this->checkAuth();

        $db = Database::connection();
        $p = $db->query("SELECT semester_id, tpa_certificate_url FROM participants WHERE id = ?")->bind($participantId)->fetchAssoc();

        if (!$p || empty($p['tpa_certificate_url'])) {
            http_response_code(404);
            echo "Certificate not found.";
            exit;
        }

        $semId = $p['semester_id'] ?? 'unknown';
        $filename = $p['tpa_certificate_url'];

        // Prevent directory traversal
        $filename = basename($filename);

        $path = __DIR__ . '/../../storage/' . $semId . '/documents/tpa/' . $filename;

        if (!file_exists($path)) {
            // Fallback to legacy path for older uploads (Migration support)
            $legacyPath = __DIR__ . '/../../public/uploads/documents/tpa/' . $filename;
            if (file_exists($legacyPath)) {
                $path = $legacyPath;
            } else {
                http_response_code(404);
                echo "File not found on server.";
                exit;
            }
        }

        // Serve File
        $mime = mime_content_type($path);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
    public function saveTpaThreshold()
    {
        $this->checkAuth();

        // Only Admin/Superadmin
        if (\App\Utils\RoleHelper::isAdminProdi()) {
            header('Location: /admin/assessment/tpa?error=unauthorized');
            exit;
        }

        $s2 = $_POST['threshold_s2'] ?? '450';
        $s3 = $_POST['threshold_s3'] ?? '500';

        \App\Models\Setting::set('tpa_threshold_s2', $s2);
        \App\Models\Setting::set('tpa_threshold_s3', $s3);

        header('Location: /admin/assessment/tpa?success=threshold_saved');
        exit;
    }
}
