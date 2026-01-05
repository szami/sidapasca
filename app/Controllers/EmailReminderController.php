<?php

namespace App\Controllers;

use App\Models\EmailReminder;
use App\Models\EmailTemplate;
use App\Models\Semester;
use App\Utils\EmailService;
use App\Utils\View;
use App\Utils\Database;
use Leaf\Http\Request;

class EmailReminderController
{
    private function checkAuth()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $activeSemester = Semester::getActive();
        $reminders = EmailReminder::all($activeSemester['id'] ?? null);

        echo View::render('admin.email.reminders.index', [
            'reminders' => $reminders,
            'activeSemester' => $activeSemester
        ]);
    }

    public function create()
    {
        $this->checkAuth();

        // Get active semester
        $activeSemester = Semester::getActive();
        if (!$activeSemester) {
            header('Location: /admin/email/reminders?error=no_active_semester');
            exit;
        }

        // Get participants for active semester
        $db = Database::connection();
        $participants = $db->query("
            SELECT p.*, s.nama as semester_nama
            FROM participants p
            JOIN semesters s ON p.semester_id = s.id
            WHERE p.semester_id = ?
            ORDER BY p.nama_lengkap ASC
        ")->bind($activeSemester['id'])->all();

        // Get templates
        $templates = EmailTemplate::all();

        echo View::render('admin.email.reminders.send', [
            'participants' => $participants,
            'templates' => $templates,
            'activeSemester' => $activeSemester
        ]);
    }

    public function send()
    {
        $this->checkAuth();

        $mode = Request::get('mode'); // 'testing' or 'actual'
        $templateId = Request::get('template_id');
        $subject = Request::get('subject');
        $body = Request::get('body', false); // Don't sanitize HTML

        $activeSemester = Semester::getActive();

        if ($mode === 'testing') {
            // Testing mode - send to test emails
            $testEmails = Request::get('test_emails');
            $emails = array_map('trim', explode(',', $testEmails));

            // Get first participant as sample data
            $db = Database::connection();
            $sampleParticipant = $db->query("
                SELECT p.*, s.nama as semester_nama
                FROM participants p
                JOIN semesters s ON p.semester_id = s.id
                WHERE p.semester_id = ?
                LIMIT 1
            ")->bind($activeSemester['id'])->all()[0] ?? null;

            $successCount = 0;
            $errors = [];

            foreach ($emails as $email) {
                if (empty($email))
                    continue;

                try {
                    EmailService::send($email, $subject, $body, $sampleParticipant);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to send to $email: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                header("Location: /admin/email/reminders/send?success=test&count=$successCount");
            } else {
                header("Location: /admin/email/reminders/send?error=" . urlencode(implode(', ', $errors)));
            }
            exit;
        }

        // Actual mode - send to selected participants
        // Actual mode - send to selected participants
        $participantIds = Request::get('participant_ids') ?? []; // Array of IDs

        if (empty($participantIds)) {
            header('Location: /admin/email/reminders/send?error=no_participants');
            exit;
        }

        // Create reminder record
        $reminderId = EmailReminder::create([
            'semester_id' => $activeSemester['id'],
            'template_id' => $templateId,
            'subject' => $subject,
            'body' => $body,
            'recipient_count' => count($participantIds),
            'status' => 'sending',
            'sent_by' => $_SESSION['admin']
        ]);

        $db = Database::connection();
        $sentCount = 0;
        $failedCount = 0;

        foreach ($participantIds as $participantId) {
            // Get participant data
            $participant = $db->query("
                SELECT p.*, s.nama as semester_nama
                FROM participants p
                JOIN semesters s ON p.semester_id = s.id
                WHERE p.id = ?
            ")->bind($participantId)->all()[0] ?? null;

            if (!$participant || empty($participant['email'])) {
                $failedCount++;
                EmailReminder::createLog([
                    'reminder_id' => $reminderId,
                    'participant_id' => $participantId,
                    'email' => $participant['email'] ?? 'N/A',
                    'status' => 'failed',
                    'error_message' => 'Email address not found'
                ]);
                continue;
            }

            // Create log entry
            $logId = EmailReminder::createLog([
                'reminder_id' => $reminderId,
                'participant_id' => $participantId,
                'email' => $participant['email'],
                'status' => 'pending'
            ]);

            try {
                EmailService::send($participant['email'], $subject, $body, $participant);
                EmailReminder::updateLog($logId, 'sent');
                $sentCount++;
            } catch (\Exception $e) {
                EmailReminder::updateLog($logId, 'failed', $e->getMessage());
                $failedCount++;
            }
        }

        // Update reminder status
        EmailReminder::updateStatus($reminderId, 'sent', [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ]);

        header("Location: /admin/email/reminders?success=sent&sent=$sentCount&failed=$failedCount");
        exit;
    }

    public function detail($id)
    {
        $this->checkAuth();

        $reminder = EmailReminder::find($id);
        $logs = EmailReminder::getLogs($id);

        echo View::render('admin.email.reminders.detail', [
            'reminder' => $reminder,
            'logs' => $logs
        ]);
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
        $orderColumnIndex = Request::get('order')[0]['column'] ?? 2;
        $orderDir = Request::get('order')[0]['dir'] ?? 'asc';

        $columns = [
            0 => 'p.id',
            1 => 'p.nomor_peserta',
            2 => 'p.nama_lengkap',
            3 => 'p.nama_prodi',
            4 => 'p.email',
            5 => 'p.status_berkas',
            6 => 'p.status_pembayaran',
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'p.nama_lengkap';

        // Filter Params
        $preset = Request::get('preset') ?? 'all';

        // Base WHERE
        $whereClause = "WHERE p.semester_id = '$semesterId'";

        // Apply Preset
        if ($preset === 'unpaid') {
            $whereClause .= " AND p.status_berkas = 'lulus' AND (p.nomor_peserta IS NULL OR p.nomor_peserta = '')";
        }

        // Global Search
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (p.nama_lengkap LIKE '%$searchEscaped%' 
                             OR p.email LIKE '%$searchEscaped%'
                             OR p.nomor_peserta LIKE '%$searchEscaped%'
                             OR p.nama_prodi LIKE '%$searchEscaped%')";
        }

        // record counts
        $totalRecordsSql = "SELECT COUNT(*) as total FROM participants p WHERE p.semester_id = '$semesterId'";
        $totalRes = $db->query($totalRecordsSql)->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;

        $filteredRecordsSql = "SELECT COUNT(*) as total FROM participants p $whereClause";
        $filteredRes = $db->query($filteredRecordsSql)->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        // data fetching
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
