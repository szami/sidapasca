<?php

// SKM Module Migration & Seeder
// Run this script to setup the database for the Questionnaire Module

$baseDir = dirname(__DIR__, 3);
$dbPath = $baseDir . '/storage/database.sqlite';

echo "Target DB: $dbPath\n";

if (!file_exists($dbPath)) {
    echo "Database file not found. Run migrate.php first.\n";
    exit(1);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create Surveys Table
    $surveys = "CREATE TABLE IF NOT EXISTS surveys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        target_role VARCHAR(50) NOT NULL, -- participant, committee_general, committee_internal, committee_field
        is_active BOOLEAN DEFAULT 1,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($surveys);
    echo "Surveys table created.\n";

    // 2. Create Survey Questions Table
    $questions = "CREATE TABLE IF NOT EXISTS survey_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        survey_id INTEGER NOT NULL,
        code VARCHAR(20) NULL, -- U1, U2, etc.
        question_text TEXT NOT NULL,
        category VARCHAR(100) NULL, -- For internal committee categorization
        order_num INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
    )";
    $pdo->exec($questions);
    echo "Survey Questions table created.\n";

    // 3. Create Survey Responses Table
    $responses = "CREATE TABLE IF NOT EXISTS survey_responses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        survey_id INTEGER NOT NULL,
        user_id INTEGER NULL, -- Nullable for anonymity or if user not logged in (though typically logged in)
        respondent_identifier VARCHAR(100) NULL, -- e.g. Nomor Peserta or Username
        respondent_type VARCHAR(50) NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        suggestion TEXT,
        FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
    )";
    $pdo->exec($responses);
    echo "Survey Responses table created.\n";

    // 4. Create Survey Answers Table
    $answers = "CREATE TABLE IF NOT EXISTS survey_answers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        response_id INTEGER NOT NULL,
        question_id INTEGER NOT NULL,
        score INTEGER NOT NULL, -- 1-4 scale
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
        FOREIGN KEY(question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
    )";
    $pdo->exec($answers);
    echo "Survey Answers table created.\n";

    // --- SEEDING DEFAULT DATA ---

    // A. Seed default Surveys
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
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM surveys WHERE title = ?");
        $stmt->execute([$s['title']]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO surveys (title, target_role, description) VALUES (?, ?, ?)");
            $stmt->execute([$s['title'], $s['target_role'], $s['description']]);
            echo "Seeded Survey: {$s['title']}\n";
        }
    }

    // B. Seed Questions for 'participant' Survey
    $surveyParticipant = $pdo->query("SELECT id FROM surveys WHERE target_role = 'participant'")->fetch();
    if ($surveyParticipant) {
        $questionsParticipant = [
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

        // Clear existing questions to avoid duplicates/ensure update
        $pdo->exec("DELETE FROM survey_questions WHERE survey_id = " . $surveyParticipant['id']);

        $stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, code, question_text, category, order_num) VALUES (?, ?, ?, ?, ?)");
        foreach ($questionsParticipant as $q) {
            $stmt->execute([$surveyParticipant['id'], $q[0], $q[1], $q[2], $q[3]]);
        }
        echo "Seeded questions for Participant Survey.\n";
    }

    // C. Seed Questions for 'committee_general' Survey (Teknis Umum)
    $surveyGeneral = $pdo->query("SELECT id FROM surveys WHERE target_role = 'committee_general'")->fetch();
    if ($surveyGeneral) {
        $questionsGeneral = [
            ['T1', 'Bagaimana kestabilan dan kemudahan pengelolaan data pendaftar pada backend Administrator?', 'Sistem Admisi', 1],
            ['T2', 'Bagaimana performa server dan aplikasi CAT saat beban puncak (peak load) ujian berlangsung?', 'Sistem CAT', 2],
            ['T3', 'Bagaimana kualitas jaringan dan hardware server selama masa operasional seleksi?', 'Infrastruktur', 3]
        ];

        $pdo->exec("DELETE FROM survey_questions WHERE survey_id = " . $surveyGeneral['id']);
        $stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, code, question_text, category, order_num) VALUES (?, ?, ?, ?, ?)");
        foreach ($questionsGeneral as $q) {
            $stmt->execute([$surveyGeneral['id'], $q[0], $q[1], $q[2], $q[3]]);
        }
        echo "Seeded questions for General Technical Committee Survey.\n";
    }

    // D. Seed Questions for 'committee_internal' Survey (SIDA)
    $surveyInternal = $pdo->query("SELECT id FROM surveys WHERE target_role = 'committee_internal'")->fetch();
    if ($surveyInternal) {
        $questionsInternal = [
            ['I1', 'Bagaimana reliabilitas fitur manajemen/integrasi data pada aplikasi internal SIDA Pascasarjana?', 'Sistem SIDA', 1]
        ];

        $pdo->exec("DELETE FROM survey_questions WHERE survey_id = " . $surveyInternal['id']);
        $stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, code, question_text, category, order_num) VALUES (?, ?, ?, ?, ?)");
        foreach ($questionsInternal as $q) {
            $stmt->execute([$surveyInternal['id'], $q[0], $q[1], $q[2], $q[3]]);
        }
        echo "Seeded questions for Internal Pascasarjana Survey.\n";
    }

    // E. Seed Questions for 'committee_field' Survey (Lapangan)
    $surveyField = $pdo->query("SELECT id FROM surveys WHERE target_role = 'committee_field'")->fetch();
    if ($surveyField) {
        $questionsField = [
            ['L1', 'Bagaimana kelayakan ruang ujian (AC, Pencahayaan, Kebersihan) untuk kenyamanan peserta?', 'Fasilitas Ruang', 1],
            ['L2', 'Bagaimana kondisi PC/Laptop klien yang digunakan peserta (Mouse, Keyboard, Layar)?', 'Perangkat Keras', 2],
            ['L3', 'Bagaimana kualitas dan ketepatan waktu penyediaan konsumsi/akomodasi bagi pengawas?', 'Akomodasi', 3],
            ['L4', 'Bagaimana kejelasan instruksi (briefing) dan alur koordinasi saat pelaksanaan ujian?', 'Koordinasi Lapangan', 4]
        ];

        $pdo->exec("DELETE FROM survey_questions WHERE survey_id = " . $surveyField['id']);
        $stmt = $pdo->prepare("INSERT INTO survey_questions (survey_id, code, question_text, category, order_num) VALUES (?, ?, ?, ?, ?)");
        foreach ($questionsField as $q) {
            $stmt->execute([$surveyField['id'], $q[0], $q[1], $q[2], $q[3]]);
        }
        echo "Seeded questions for Field Committee Survey.\n";
    }

    echo "SKM Migration & Seeding Completed Successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
