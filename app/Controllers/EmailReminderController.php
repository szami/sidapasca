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
}
