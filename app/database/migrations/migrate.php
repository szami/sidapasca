<?php

// Standalone Native PDO Migration
$baseDir = dirname(__DIR__, 3);
$dbPath = $baseDir . '/storage/database.sqlite';

echo "Target DB: $dbPath\n";

if (!file_exists($baseDir . '/storage')) {
    mkdir($baseDir . '/storage', 0755, true);
}
if (!file_exists($dbPath)) {
    touch($dbPath);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Users Table
    $users = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($users);
    echo "Users table migrated.\n";

    // 2. Semesters Table (NEW)
    $semesters = "CREATE TABLE IF NOT EXISTS semesters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kode VARCHAR(10) UNIQUE NOT NULL,
        nama VARCHAR(255) NOT NULL,
        periode INTEGER DEFAULT 0,
        is_active BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($semesters);

    // Check if periode exists (for existing table)
    $colsSem = $pdo->query("PRAGMA table_info(semesters)")->fetchAll(PDO::FETCH_ASSOC);
    $hasPeriode = false;
    foreach ($colsSem as $col) {
        if ($col['name'] === 'periode')
            $hasPeriode = true;
    }
    if (!$hasPeriode) {
        $pdo->exec("ALTER TABLE semesters ADD COLUMN periode INTEGER DEFAULT 0");
        echo "Semesters table updated with periode.\n";
    } else {
        echo "Semesters table migrated.\n";
    }

    // 3. Participants Table (Modified with semester_id)
    // Note: SQLite ALTER TABLE is limited. If table exists, we should check if column exists.
    // For simplicity in dev, we can CREATE IF NOT EXISTS.
    // If it exists without semester_id, we add it.

    $participants = "CREATE TABLE IF NOT EXISTS participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        semester_id INTEGER NULL,
        nomor_peserta VARCHAR(50) UNIQUE NULL,
        no_billing VARCHAR(50) NULL,
        nama_lengkap VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        tgl_lahir DATE NOT NULL,
        kode_prodi VARCHAR(50) NULL,
        nama_prodi VARCHAR(255) NULL,
        jalur_masuk VARCHAR(50) NULL,
        status_berkas VARCHAR(50) DEFAULT 'pending', 
        status_pembayaran BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(semester_id) REFERENCES semesters(id)
    )";
    $pdo->exec($participants);

    // Check if semester_id exists (for existing table)
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $hasSemester = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'semester_id')
            $hasSemester = true;
    }
    if (!$hasSemester) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN semester_id INTEGER NULL REFERENCES semesters(id)");
        echo "Participants table updated with semester_id.\n";
    } else {
        echo "Participants table migrated.\n";
    }

    // Seed Semesters
    $checkSem = $pdo->query("SELECT count(*) FROM semesters")->fetchColumn();
    if ($checkSem == 0) {
        $stmt = $pdo->prepare("INSERT INTO semesters (kode, nama, is_active) VALUES (?, ?, ?)");
        $stmt->execute(['20251', 'Semester Ganjil 2025/2026', 0]);
        $stmt->execute(['20252', 'Semester Genap 2025/2026', 1]); // Set Active as per user request example order
        echo "Semesters seeded.\n";
    }

    // 4. Exam Attendances Table (NEW)
    $attendances = "CREATE TABLE IF NOT EXISTS exam_attendances (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        participant_id INTEGER NOT NULL,
        semester_id INTEGER NOT NULL,
        is_present BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(participant_id) REFERENCES participants(id),
        FOREIGN KEY(semester_id) REFERENCES semesters(id),
        UNIQUE(participant_id, semester_id)
    )";
    $pdo->exec($attendances);
    echo "Exam Attendances table migrated.\n";

    // 5. Exam Rooms Table (NEW)
    $exam_rooms = "CREATE TABLE IF NOT EXISTS exam_rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fakultas VARCHAR(255) NULL,
        nama_ruang VARCHAR(255) NOT NULL,
        kapasitas INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($exam_rooms);
    echo "Exam Rooms table migrated.\n";

    // 6. Exam Sessions Table (NEW)
    $exam_sessions = "CREATE TABLE IF NOT EXISTS exam_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        semester_id INTEGER NOT NULL,
        exam_room_id INTEGER NOT NULL,
        nama_sesi VARCHAR(255) NOT NULL,
        tanggal DATE NOT NULL,
        waktu_mulai TIME NOT NULL,
        waktu_selesai TIME NOT NULL,
        is_active BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(semester_id) REFERENCES semesters(id),
        FOREIGN KEY(exam_room_id) REFERENCES exam_rooms(id)
    )";
    $pdo->exec($exam_sessions);
    echo "Exam Sessions table migrated.\n";

    // 7. Settings Table (NEW)
    $settings = "CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key_name VARCHAR(255) NOT NULL UNIQUE,
        value TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($settings);
    echo "Settings table migrated.\n";

    // 8. Add scheduling columns to participants (if not exists)
    echo "Checking participants scheduling columns...\n";
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($cols, 'name');

    if (!in_array('ruang_ujian', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN ruang_ujian VARCHAR(255) NULL");
        echo "Added ruang_ujian column to participants.\n";
    } else {
        echo "Column ruang_ujian already exists.\n";
    }

    if (!in_array('sesi_ujian', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN sesi_ujian VARCHAR(255) NULL");
        echo "Added sesi_ujian column to participants.\n";
    } else {
        echo "Column sesi_ujian already exists.\n";
    }

    if (!in_array('tanggal_ujian', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN tanggal_ujian DATE NULL");
        echo "Added tanggal_ujian column to participants.\n";
    } else {
        echo "Column tanggal_ujian already exists.\n";
    }

    if (!in_array('waktu_ujian', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN waktu_ujian VARCHAR(50) NULL");
        echo "Added waktu_ujian column to participants.\n";
    } else {
        echo "Column waktu_ujian already exists.\n";
    }

    // 9. Add photo column to participants (if not exists)
    if (!in_array('photo_filename', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN photo_filename VARCHAR(255) NULL");
        echo "Added photo_filename column to participants.\n";
    } else {
        echo "Column photo_filename already exists.\n";
    }

    // 10. Add document columns to participants (if not exists)
    if (!in_array('ktp_filename', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN ktp_filename VARCHAR(255) NULL");
        echo "Added ktp_filename column to participants.\n";
    } else {
        echo "Column ktp_filename already exists.\n";
    }

    if (!in_array('ijazah_filename', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN ijazah_filename VARCHAR(255) NULL");
        echo "Added ijazah_filename column to participants.\n";
    } else {
        echo "Column ijazah_filename already exists.\n";
    }

    if (!in_array('transkrip_filename', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN transkrip_filename VARCHAR(255) NULL");
        echo "Added transkrip_filename column to participants.\n";
    } else {
        echo "Column transkrip_filename already exists.\n";
    }

    // 11. Add Bio & Education columns (System Recommendations)
    $bioCols = [
        'tempat_lahir' => 'VARCHAR(255) NULL',
        'jenis_kelamin' => 'CHAR(1) NULL',
        'no_hp' => 'VARCHAR(50) NULL',
        // S1 (removed duplicates: asal_mk, s1_universitas, s1_tahun_lulus)
        's1_prodi' => 'VARCHAR(255) NULL',
        's1_ipk' => 'VARCHAR(10) NULL',
        // S2 (removed duplicate: asal_s2)
        's2_perguruan_tinggi' => 'VARCHAR(255) NULL',
        's2_prodi' => 'VARCHAR(255) NULL',
        's2_tahun_masuk' => 'VARCHAR(10) NULL',
        's2_tahun_tamat' => 'VARCHAR(10) NULL',
        's2_ipk' => 'VARCHAR(10) NULL',
        's2_gelar' => 'VARCHAR(50) NULL',
    ];

    foreach ($bioCols as $colName => $colType) {
        if (!in_array($colName, $existingCols)) {
            $pdo->exec("ALTER TABLE participants ADD COLUMN $colName $colType");
            echo "Added $colName column to participants.\n";
        }
    }

    // 12. Add role and prodi_id to users table (User Management System)
    echo "Checking users table for role management columns...\n";
    $cols = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
    $hasRole = false;
    $hasProdiId = false;

    foreach ($cols as $col) {
        if ($col['name'] === 'role')
            $hasRole = true;
        if ($col['name'] === 'prodi_id')
            $hasProdiId = true;
    }

    if (!$hasRole) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'admin'");
        echo "Added role column to users.\n";
    } else {
        echo "Column role already exists.\n";
    }

    if (!$hasProdiId) {
        $pdo->exec("ALTER TABLE users ADD COLUMN prodi_id VARCHAR(50) NULL");
        echo "Added prodi_id column to users.\n";
    } else {
        echo "Column prodi_id already exists.\n";
    }

    // Upgrade existing admin to superadmin
    $pdo->exec("UPDATE users SET role = 'superadmin' WHERE username = 'admin' AND role != 'superadmin'");
    echo "Ensured admin user has superadmin role.\n";

    // 13. Update Logs Table (NEW - Auto Update System)
    $update_logs = "CREATE TABLE IF NOT EXISTS update_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        version_from VARCHAR(20),
        version_to VARCHAR(20),
        status VARCHAR(20),
        message TEXT,
        performed_by INTEGER,
        backup_file VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(performed_by) REFERENCES users(id)
    )";
    $pdo->exec($update_logs);
    echo "Update Logs table migrated.\n";

    // Seed Users with different roles
    $defaultUsers = [
        ['username' => 'admin', 'password' => 'admin123', 'role' => 'superadmin', 'prodi_id' => null],
        ['username' => 'operator', 'password' => 'operator123', 'role' => 'admin', 'prodi_id' => null],
        ['username' => 'upkh', 'password' => 'upkh123', 'role' => 'upkh', 'prodi_id' => null],
        ['username' => 'tu', 'password' => 'tu123', 'role' => 'tu', 'prodi_id' => null],
        ['username' => 'prodi_test', 'password' => 'prodi123', 'role' => 'admin_prodi', 'prodi_id' => '86103'],
    ];

    foreach ($defaultUsers as $userData) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute([':u' => $userData['username']]);
        $user = $stmt->fetch();

        if (!$user) {
            $pass = password_hash($userData['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, prodi_id) VALUES (:u, :p, :r, :pi)");
            $stmt->execute([
                ':u' => $userData['username'],
                ':p' => $pass,
                ':r' => $userData['role'],
                ':pi' => $userData['prodi_id']
            ]);
            echo "User '{$userData['username']}' (role: {$userData['role']}) seeded.\n";
        } else {
            // Update existing user role if needed
            if (!isset($user['role']) || $user['role'] !== $userData['role']) {
                $stmt = $pdo->prepare("UPDATE users SET role = :r, prodi_id = :pi WHERE username = :u");
                $stmt->execute([
                    ':r' => $userData['role'],
                    ':pi' => $userData['prodi_id'],
                    ':u' => $userData['username']
                ]);
                echo "User '{$userData['username']}' role updated to {$userData['role']}.\n";
            } else {
                echo "User '{$userData['username']}' already exists.\n";
            }
        }
    }


    // 14. Workflow Upgrade Tables (Assessment & Graduation)
    echo "Checking Workflow Upgrade Tables...\n";

    // 14a. Add columns to participants
    echo "Checking participants workflow columns...\n";
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($cols, 'name');

    if (!in_array('status_verifikasi_fisik', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN status_verifikasi_fisik VARCHAR(20) DEFAULT 'pending'"); // pending, lengkap, tidak_lengkap
        echo "Added status_verifikasi_fisik to participants.\n";
    }
    if (!in_array('status_kelulusan_akhir', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN status_kelulusan_akhir VARCHAR(20) DEFAULT 'pending'"); // pending, lulus, tidak_lulus
        echo "Added status_kelulusan_akhir to participants.\n";
    }
    if (!in_array('sk_kelulusan', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN sk_kelulusan VARCHAR(100) NULL");
        echo "Added sk_kelulusan to participants.\n";
    }
    if (!in_array('nilai_tpa_total', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN nilai_tpa_total DECIMAL(5,2) NULL");
        echo "Added nilai_tpa_total to participants.\n";
    }
    if (!in_array('nilai_bidang_total', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN nilai_bidang_total DECIMAL(5,2) NULL");
        echo "Added nilai_bidang_total to participants.\n";
    }

    // 14b. Assessment Components (TPA/Bidang)
    $assessment_components = "CREATE TABLE IF NOT EXISTS assessment_components (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        prodi_id VARCHAR(10) NULL,
        type VARCHAR(20) NOT NULL, -- 'TPA' or 'BIDANG'
        nama_komponen VARCHAR(100) NOT NULL,
        bobot_persen INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($assessment_components);
    echo "Assessment Components table migrated.\n";

    // 14c. Assessment Scores
    $assessment_scores = "CREATE TABLE IF NOT EXISTS assessment_scores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        participant_id INTEGER NOT NULL,
        component_id INTEGER NOT NULL,
        score DECIMAL(5,2) DEFAULT 0,
        created_by VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(participant_id) REFERENCES participants(id),
        FOREIGN KEY(component_id) REFERENCES assessment_components(id)
    )";
    $pdo->exec($assessment_scores);
    echo "Assessment Scores table migrated.\n";

    // 14d. Prodi Configs (Thresholds)
    $prodi_configs = "CREATE TABLE IF NOT EXISTS prodi_configs (
        kode_prodi VARCHAR(10) PRIMARY KEY,
        jenjang VARCHAR(5) NOT NULL, -- 'S2' or 'S3'
        min_tpa DECIMAL(5,2) DEFAULT 450,
        min_bidang DECIMAL(5,2) DEFAULT 0
    )";
    $pdo->exec($prodi_configs);
    echo "Prodi Configs table migrated.\n";

    // 14b. Assessment Refinement (Direct Recommendation)
    echo "Checking Refinement Columns...\n";
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($cols, 'name'); // Re-fetch cols

    if (!in_array('status_tes_bidang', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN status_tes_bidang VARCHAR(20) DEFAULT NULL");
        echo "Added status_tes_bidang to participants.\n";
    }
    $pdo->exec($prodi_configs);
    echo "Prodi Configs table migrated.\n";

    // 14e. Prodi Quotas (Daya Tampung)
    $prodi_quotas = "CREATE TABLE IF NOT EXISTS prodi_quotas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        semester_id INTEGER NOT NULL,
        kode_prodi VARCHAR(10) NOT NULL,
        daya_tampung INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(semester_id) REFERENCES semesters(id),
        UNIQUE(semester_id, kode_prodi)
    )";
    $pdo->exec($prodi_quotas);
    echo "Prodi Quotas table migrated.\n";

    // 15. News Management Table
    $newsSql = file_get_contents($baseDir . '/database/migrations/create_news_table.sql');
    if ($newsSql) {
        $pdo->exec($newsSql);
        echo "News table migrated.\n";
    }

    // 16. Guide Management Table
    $guidesSql = file_get_contents($baseDir . '/database/migrations/create_guides_table.sql');
    if ($guidesSql) {
        $pdo->exec($guidesSql);
        echo "Guides table migrated.\n";
    }

    // 17. Participant Physical Docs & Selection Results (Task: Reorganisasi Halaman Peserta)
    echo "Checking Participant Additional Columns (Physical Docs & Selection)...\n";
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $existingCols = array_column($cols, 'name');

    // Physical Documents
    if (!in_array('berkas_fisik_status', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN berkas_fisik_status TEXT DEFAULT 'belum_lengkap'");
        echo "Added berkas_fisik_status to participants.\n";
    }
    if (!in_array('berkas_fisik_note', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN berkas_fisik_note TEXT");
        echo "Added berkas_fisik_note to participants.\n";
    }

    // Selection Results
    if (!in_array('hasil_seleksi', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN hasil_seleksi TEXT DEFAULT 'belum_ada'");
        echo "Added hasil_seleksi to participants.\n";
    }
    if (!in_array('hasil_seleksi_note', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN hasil_seleksi_note TEXT");
        echo "Added hasil_seleksi_note to participants.\n";
    }
    if (!in_array('hasil_seleksi_date', $existingCols)) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN hasil_seleksi_date DATETIME");
        echo "Added hasil_seleksi_date to participants.\n";
    }

} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}

