<?php

namespace App\Utils;

class SchemaDefinition
{
    public static function getExpectedSchema()
    {
        return [
            'users' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) DEFAULT 'admin',
                    prodi_id VARCHAR(50) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'semesters' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS semesters (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    kode VARCHAR(10) UNIQUE NOT NULL,
                    nama VARCHAR(255) NOT NULL,
                    periode INTEGER DEFAULT 0,
                    is_active BOOLEAN DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'surveys' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS surveys (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(255) NOT NULL,
                    target_role VARCHAR(50) NOT NULL,
                    is_active BOOLEAN DEFAULT 1,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'survey_questions' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_questions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    survey_id INTEGER NOT NULL,
                    code VARCHAR(20) NULL,
                    question_text TEXT NOT NULL,
                    category VARCHAR(100) NULL,
                    order_num INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
                )"
            ],
            'survey_responses' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_responses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    survey_id INTEGER NOT NULL,
                    user_id INTEGER NULL,
                    respondent_identifier VARCHAR(100) NULL,
                    respondent_type VARCHAR(50) NOT NULL,
                    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    suggestion TEXT,
                    FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
                )"
            ],
            'survey_answers' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_answers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    response_id INTEGER NOT NULL,
                    question_id INTEGER NOT NULL,
                    score INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
                    FOREIGN KEY(question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
                )"
            ],
            // Add other tables as needed (news, guides, etc.)
            'update_logs' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS update_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    version_from VARCHAR(20),
                    version_to VARCHAR(20),
                    status VARCHAR(20),
                    message TEXT,
                    performed_by INTEGER,
                    backup_file VARCHAR(255),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(performed_by) REFERENCES users(id)
                )"
            ]
        ];
    }

    public static function getSeeder($table)
    {
        $seeders = [
            'surveys' => function ($db) {
                $defaultSurveys = [
                    [
                        'title' => 'Survei Kepuasan Masyarakat (SKM) PMB Pascasarjana',
                        'target_role' => 'participant',
                        'description' => 'Survei standar pelayanan publik sesuai PermenPAN-RB No 14 Tahun 2017'
                    ],
                    [
                        'title' => 'Evaluasi Kinerja Sistem & Infrastruktur (Teknis Umum)',
                        'target_role' => 'committee_general',
                        'description' => 'Evaluasi untuk Tim UPA TIK dan BAAK terkait Admisi, CAT, dan Infrastruktur'
                    ],
                    [
                        'title' => 'Evaluasi Sistem Manajemen Internal (SIDA)',
                        'target_role' => 'committee_internal',
                        'description' => 'Evaluasi khusus Tim Pascasarjana terkait aplikasi SIDA'
                    ],
                    [
                        'title' => 'Evaluasi Logistik & Pengawasan (Lapangan)',
                        'target_role' => 'committee_field',
                        'description' => 'Evaluasi untuk Pengawas Ujian dan Tim Akomodasi/Logistik'
                    ]
                ];

                foreach ($defaultSurveys as $s) {
                    $stmt = $db->query("SELECT id FROM surveys WHERE title = '$s[title]'")->fetchAssoc();
                    if (!$stmt) {
                        $db->query("INSERT INTO surveys (title, target_role, description) VALUES ('$s[title]', '$s[target_role]', '$s[description]')")->execute();
                    }
                }
                return "Surveys seeded.";
            },
            'survey_questions' => function ($db) {
                // Seed Questions Logic (Simplified from skm_migration.php)
                // 1. Participant
                $surveyParticipant = $db->query("SELECT id FROM surveys WHERE target_role = 'participant'")->fetchAssoc();
                if ($surveyParticipant) {
                    $questions = [
                        ['U1', 'Bagaimana pendapat Anda tentang kesesuaian persyaratan pendaftaran yang diminta dengan informasi yang disampaikan?', 'Persyaratan', 1],
                        ['U2', 'Bagaimana kemudahan prosedur pendaftaran di web admisipasca.ulm.ac.id hingga ujian Sistem CAT?', 'Prosedur', 2],
                        ['U3', 'Bagaimana kesesuaian waktu pelaksanaan ujian (Sesi/Jadwal) dengan jadwal yang telah ditetapkan?', 'Waktu Pelayanan', 3],
                        ['U4', 'Bagaimana keterjangkauan dan kejelasan biaya pendaftaran yang ditetapkan?', 'Biaya/Tarif', 4],
                        ['U5', 'Bagaimana ketersediaan dan kejelasan informasi mengenai Program Studi yang Anda pilih?', 'Produk Layanan', 5],
                        ['U6', 'Bagaimana kemampuan petugas/panitia dalam menjawab pertanyaan atau membantu kendala teknis Anda?', 'Kompetensi Pelaksana', 6],
                        ['U7', 'Bagaimana kesopanan dan keramahan petugas (Helpdesk/Pengawas) dalam melayani Anda?', 'Perilaku Pelaksana', 7],
                        ['U8', 'Bagaimana kecepatan dan ketepatan respon pengaduan/keluhan (jika ada) selama proses seleksi?', 'Penanganan Pengaduan', 8],
                        ['U9', 'Bagaimana kualitas sarana prasarana ujian (Aplikasi CAT, Komputer, Jaringan, Kenyamanan Ruangan)?', 'Sarana & Prasarana', 9]
                    ];

                    // We check if count matches, simplistic check
                    $count = $db->query("SELECT count(*) as c FROM survey_questions WHERE survey_id = " . $surveyParticipant['id'])->fetchAssoc()['c'] ?? 0;

                    if ($count < count($questions)) {
                        // Only seed if less (to avoid overwriting custom edits, or maybe we SHOULD overwrite? User said 'update data if exists'. Let's overwrite/ensure.)
                        // Safe approach: Delete and re-insert for master data reset
                        $db->query("DELETE FROM survey_questions WHERE survey_id = " . $surveyParticipant['id'])->execute();
                        foreach ($questions as $q) {
                            $db->query("INSERT INTO survey_questions (survey_id, code, question_text, category, order_num) VALUES (" . $surveyParticipant['id'] . ", '$q[0]', '$q[1]', '$q[2]', $q[3])")->execute();
                        }
                    }
                }
                return "Questions seeded/updated.";
            }
        ];

        return $seeders[$table] ?? null;
    }

    public static function hasDataMismatch($table, $db)
    {
        if ($table === 'surveys') {
            $count = $db->query("SELECT count(*) as c FROM surveys")->fetchAssoc()['c'] ?? 0;
            return $count < 4; // We expect at least 4 default surveys
        }
        if ($table === 'survey_questions') {
            // Check if participant survey exists and has questions
            $survey = $db->query("SELECT id FROM surveys WHERE target_role = 'participant'")->fetchAssoc();
            if ($survey) {
                $count = $db->query("SELECT count(*) as c FROM survey_questions WHERE survey_id = " . $survey['id'])->fetchAssoc()['c'] ?? 0;
                return $count < 9; // Expect 9 default questions
            }
        }
        return false;
    }
}
