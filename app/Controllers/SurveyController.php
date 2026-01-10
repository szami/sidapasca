<?php

namespace App\Controllers;

use App\Utils\Database;
use App\Utils\View;

class SurveyController
{
    // List available surveys (for Admin)
    public function index()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();
        $surveys = $db->query("SELECT * FROM surveys ORDER BY created_at ASC")->fetchAll();

        // Count responses for each
        foreach ($surveys as &$s) {
            $count = $db->query("SELECT count(*) as total FROM survey_responses WHERE survey_id = ?")->bind($s['id'])->first();
            $s['response_count'] = $count['total'] ?? 0;
        }

        echo View::render('admin.survey.index', ['surveys' => $surveys]);
    }

    // Show survey form (Public/Participant or Committee)
    public function show($id)
    {
        $db = Database::connection();

        // Fetch Survey
        $survey = $db->query("SELECT * FROM surveys WHERE id = ?")->bind($id)->first();

        if (!$survey) {
            echo "Survey tidak ditemukan.";
            return;
        }

        // Check Access & Logic
        // For 'participant', we might want to check if they already submitted?
        // Let's allow multiple submissions for dev, but in prod maybe limit.

        if ($survey['target_role'] == 'participant') {
            // Optional: Force login for participant survey?
            if (!isset($_SESSION['user'])) {
                // header('Location: /login?redirect=/survey/'.$id);
                // exit;
            }
        }

        // Fetch Questions
        $questions = $db->query("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_num ASC")->bind($id)->fetchAll();

        // Group by Category if exists (for internal surveys)
        $groupedQuestions = [];
        $hasCategories = false;

        foreach ($questions as $q) {
            $cat = $q['category'] ?? 'General';
            if (!empty($q['category']))
                $hasCategories = true;
            $groupedQuestions[$cat][] = $q;
        }

        // Render View
        echo View::render('survey.form', [
            'survey' => $survey,
            'questions' => $questions,
            'groupedQuestions' => $groupedQuestions,
            'hasCategories' => $hasCategories
        ]);
    }

    // Handle Submission
    public function submit($id)
    {
        $db = Database::connection();

        // 1. Determine Respondent Info
        $respondentType = 'anonymous';
        $userId = null;
        $identifier = 'ANON';

        if (isset($_SESSION['user'])) {
            $respondentType = 'participant';
            $userId = $_SESSION['user'];
            // Fetch participant detail for identifier
            $p = $db->query("SELECT nomor_peserta FROM participants WHERE id = ?")->bind($userId)->first();
            $identifier = $p['nomor_peserta'] ?? 'P-' . $userId;
        } elseif (isset($_SESSION['admin'])) {
            $respondentType = 'committee';
            // In a real app we might track admin ID better, but here we assume simple session
            $identifier = $_SESSION['admin'] ?? 'ADMIN';
        }

        // Check for existing submission for participants
        if ($respondentType === 'participant' && $userId) {
            $existing = $db->query("SELECT id FROM survey_responses WHERE survey_id = ? AND user_id = ?")
                ->bind($id, $userId)
                ->first();
            if ($existing) {
                header("Location: /dashboard?msg=survey_completed");
                exit;
            }
        }

        // 2. Insert Response
        // Using direct SQL for insert to be safe
        $db->query(
            "INSERT INTO survey_responses (survey_id, user_id, respondent_identifier, respondent_type, suggestion) VALUES (?, ?, ?, ?, ?)"
        )->bind($id, $userId, $identifier, $respondentType, $_POST['suggestion'] ?? '')
            ->execute();

        // Get last insert ID - assuming SQLite
        $res = $db->query("SELECT last_insert_rowid() as id")->first();
        $responseId = $res['id'];

        // 3. Insert Answers
        $answers = $_POST['answers'] ?? []; // Array [question_id => score]

        foreach ($answers as $qId => $score) {
            $scoreInt = intval($score);
            if ($scoreInt >= 1 && $scoreInt <= 4) {
                $db->query(
                    "INSERT INTO survey_answers (response_id, question_id, score) VALUES (?, ?, ?)"
                )->bind($responseId, $qId, $scoreInt)
                    ->execute();
            }
        }

        // Redirect based on user role
        if (isset($_SESSION['user'])) {
            // If participant, go back to dashboard
            header("Location: /dashboard?msg=survey_completed");
        } else {
            // Public or Admin
            header("Location: /survey/thank-you");
        }
    }

    public function thankYou()
    {
        echo "<div style='font-family: Roboto, Arial, sans-serif; text-align: center; margin-top: 50px;'>
                <h1 style='color: #28a745;'>Terima Kasih!</h1>
                <p>Partisipasi Anda sangat berharga bagi peningkatan kualitas layanan kami.</p>
                <a href='/' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Beranda</a>
              </div>";
    }

    // Admin Report (IKM Calculation)
    public function report($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        // Fetch Survey Info
        $survey = $db->query("SELECT * FROM surveys WHERE id = ?")->bind($id)->first();

        if (!$survey) {
            echo "Survey tidak ditemukan.";
            return;
        }

        // Fetch Questions
        $questions = $db->query("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_num ASC")->bind($id)->fetchAll();

        // Fetch Stats per Question
        $stats = [];
        $totalRes = $db->query("SELECT count(*) as total FROM survey_responses WHERE survey_id = ?")->bind($id)->first();
        $totalResponses = $totalRes['total'] ?? 0;

        $totalNRR = 0; // Nilai Rata-rata Tertimbang Total

        foreach ($questions as $q) {
            // Get Average Score for this question
            $avgRes = $db->query("SELECT AVG(score) as avg_score FROM survey_answers WHERE question_id = ?")->bind($q['id'])->first();
            $avg = $avgRes['avg_score'];

            $avg = $avg ? round($avg, 2) : 0;

            // Calculate NRR per unsur (Bobot = 1/9 for 9 questions, or 1/TotalQuestions)
            // PermenPAN 14/2017: Bobot = 1 / Jumlah Unsur
            $bobot = 1 / count($questions);
            $nrr = $avg * $bobot;

            $stats[] = [
                'question' => $q,
                'avg_score' => $avg,
                'nrr' => $nrr
            ];

            $totalNRR += $nrr;
        }

        // Calculate IKM (Index Kepuasan Masyarakat)
        // IKM = Total NRR * 25
        $ikm = $totalNRR * 25;

        // Determine Mutu Pelayanan
        $mutu = 'D';
        $kinerja = 'Tidak Baik';
        if ($ikm >= 88.31) {
            $mutu = 'A';
            $kinerja = 'Sangat Baik';
        } elseif ($ikm >= 76.61) {
            $mutu = 'B';
            $kinerja = 'Baik';
        } elseif ($ikm >= 65.00) {
            $mutu = 'C';
            $kinerja = 'Kurang Baik';
        }

        // Fetch Recent Suggestions
        $suggestions = $db->query("SELECT suggestion, submitted_at, respondent_identifier FROM survey_responses WHERE survey_id = ? AND suggestion IS NOT NULL AND suggestion != '' ORDER BY submitted_at DESC LIMIT 20")->bind($id)->fetchAll();

        echo View::render('admin.survey.report', [
            'survey' => $survey,
            'stats' => $stats,
            'ikm' => round($ikm, 2),
            'mutu' => $mutu,
            'kinerja' => $kinerja,
            'totalResponses' => $totalResponses,
            'suggestions' => $suggestions
        ]);
    }

    // ADMIN: List Respondents
    public function respondents($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        // Fetch Survey
        $survey = $db->query("SELECT * FROM surveys WHERE id = ?")->bind($id)->first();
        if (!$survey) {
            echo "Survey tidak ditemukan.";
            return;
        }

        // Fetch Responses with Participant Name if available
        // We join participants table on user_id if respondent_type is participant
        // Using LEFT JOIN to safely handle anonymous or other types
        $sql = "SELECT r.*, p.nama_lengkap, p.nomor_peserta 
                FROM survey_responses r 
                LEFT JOIN participants p ON r.user_id = p.id AND r.respondent_type = 'participant'
                WHERE r.survey_id = ? 
                ORDER BY r.submitted_at DESC";

        $responses = $db->query($sql)->bind($id)->fetchAll();

        echo View::render('admin.survey.respondents', [
            'survey' => $survey,
            'responses' => $responses
        ]);
    }


    // ADMIN: Detail Response
    public function responseDetail($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        // Fetch Response
        $response = $db->query(
            "SELECT r.*, s.title as survey_title, s.description as survey_desc, 
                    p.nama_lengkap, p.nomor_peserta 
             FROM survey_responses r 
             JOIN surveys s ON r.survey_id = s.id
             LEFT JOIN participants p ON r.user_id = p.id AND r.respondent_type = 'participant'
             WHERE r.id = ?"
        )->bind($id)->first();

        if (!$response) {
            echo "Data respon tidak ditemukan.";
            return;
        }

        // Fetch Answers with Question Text
        $answers = $db->query(
            "SELECT a.*, q.question_text, q.category, q.code, q.order_num 
             FROM survey_answers a 
             JOIN survey_questions q ON a.question_id = q.id 
             WHERE a.response_id = ? 
             ORDER BY q.order_num ASC"
        )->bind($id)->fetchAll();

        echo View::render('admin.survey.response_detail', [
            'response' => $response,
            'answers' => $answers
        ]);
    }

    // ADMIN: Delete Response
    public function deleteResponse($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        // 1. Get Survey ID for redirect
        $response = $db->query("SELECT survey_id FROM survey_responses WHERE id = ?")->bind($id)->first();
        $surveyId = $response['survey_id'] ?? null;

        if ($surveyId) {
            // 2. Delete Answers
            $db->query("DELETE FROM survey_answers WHERE response_id = ?")->bind($id)->execute();

            // 3. Delete Response
            $db->query("DELETE FROM survey_responses WHERE id = ?")->bind($id)->execute();

            header("Location: /admin/surveys/respondents/$surveyId?msg=deleted");
        } else {
            // Fallback
            header("Location: /admin/surveys?msg=error");
        }
        exit;
    }

    // ADMIN: Form Edit Survey & Manage Questions
    public function edit($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        // Fetch Survey
        $survey = $db->query("SELECT * FROM surveys WHERE id = ?")->bind($id)->first();
        if (!$survey) {
            echo "Survey tidak ditemukan.";
            return;
        }

        // Fetch Questions
        $questions = $db->query("SELECT * FROM survey_questions WHERE survey_id = ? ORDER BY order_num ASC")->bind($id)->fetchAll();

        echo View::render('admin.survey.edit', [
            'survey' => $survey,
            'questions' => $questions
        ]);
    }

    // ADMIN: Update Survey Details
    public function update($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $target_role = $_POST['target_role'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $db->query("UPDATE surveys SET title = ?, description = ?, target_role = ?, is_active = ? WHERE id = ?")
            ->bind([$title, $description, $target_role, $is_active, $id])
            ->execute();

        // Redirect back to edit page
        header("Location: /admin/surveys/edit/" . $id . "?msg=updated");
    }

    // ADMIN: Add New Question
    public function storeQuestion($surveyId)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();

        $text = $_POST['question_text'];
        $category = $_POST['category'];
        $code = $_POST['code'] ?? null;

        // Get Max Order
        $max = $db->query("SELECT MAX(order_num) as max_ord FROM survey_questions WHERE survey_id = ?")->bind($surveyId)->first();
        $order = ($max['max_ord'] ?? 0) + 1;

        $db->query("INSERT INTO survey_questions (survey_id, question_text, category, order_num, code) VALUES (?, ?, ?, ?, ?)")
            ->bind([$surveyId, $text, $category, $order, $code])
            ->execute();

        header("Location: /admin/surveys/edit/" . $surveyId . "?msg=q_added");
    }

    // ADMIN: Update Question
    public function updateQuestion($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();
        $q = $db->query("SELECT survey_id FROM survey_questions WHERE id = ?")->bind($id)->first();
        $surveyId = $q['survey_id'];

        $text = $_POST['question_text'];
        $category = $_POST['category'];
        $code = $_POST['code'] ?? null;

        $db->query("UPDATE survey_questions SET question_text = ?, category = ?, code = ? WHERE id = ?")
            ->bind([$text, $category, $code, $id])
            ->execute();

        header("Location: /admin/surveys/edit/" . $surveyId . "?msg=q_updated");
    }

    // ADMIN: Delete Question
    public function deleteQuestion($id)
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /');
            exit;
        }

        $db = Database::connection();
        $q = $db->query("SELECT survey_id FROM survey_questions WHERE id = ?")->bind($id)->first();
        $surveyId = $q['survey_id'];

        $db->query("DELETE FROM survey_questions WHERE id = ?")->bind($id)->execute();

        header("Location: /admin/surveys/edit/" . $surveyId . "?msg=q_deleted");
    }
}
