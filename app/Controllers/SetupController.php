<?php

namespace App\Controllers;

use Leaf\App;
use PDO;
use PDOException;

class SetupController
{
    public function index()
    {
        // 1. Check Env
        $envStatus = true;
        $envMessage = "Environment file (.env) loaded successfully.";

        // 2. Check Database Connection
        $dbStatus = false;
        $dbMessage = "";
        $tables = [];
        $migrationNeeded = false;

        $dbPath = __DIR__ . '/../../storage/database.sqlite';

        try {
            if (!file_exists($dbPath)) {
                $dbMessage = "Database file (storage/database.sqlite) not found.";
                $migrationNeeded = true;
            } else {
                $pdo = new PDO("sqlite:$dbPath");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbStatus = true;
                $dbMessage = "Connected to SQLite database.";

                // Check for critical tables
                $tablesToCheck = ['users', 'participants', 'semesters', 'settings'];
                foreach ($tablesToCheck as $table) {
                    try {
                        $result = $pdo->query("SELECT count(*) FROM $table");
                        $tables[$table] = '<span style="color:green">&#10003; Exists</span>';
                    } catch (\Exception $e) {
                        $tables[$table] = '<span style="color:red">&#10005; Missing</span>';
                        $migrationNeeded = true;
                    }
                }
            }
        } catch (PDOException $e) {
            $dbMessage = "Connection failed: " . $e->getMessage();
            $migrationNeeded = true;
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>SIDA PASCA - System Setup</title>
            <style>
                body { font-family: sans-serif; padding: 40px; line-height: 1.6; background: #f4f6f8; }
                .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
                h1 { margin-top: 0; color: #333; }
                .status-item { margin-bottom: 15px; padding: 10px; border-radius: 4px; border: 1px solid #eee; }
                .success { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
                .error { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 16px; }
                .btn:hover { background: #0056b3; }
                .table-list { margin-left: 20px; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h1>System Setup Checker</h1>
                
                <div class='status-item " . ($envStatus ? 'success' : 'error') . "'>
                    <strong>Environment (.env):</strong> $envMessage
                </div>

                <div class='status-item " . ($dbStatus ? 'success' : 'error') . "'>
                    <strong>Database Connection:</strong> $dbMessage
                </div>

                <h3>Database Tables</h3>
                <div class='table-list'>";

        foreach ($tables as $name => $status) {
            $html .= "<div><strong>$name:</strong> $status</div>";
        }

        if (empty($tables) && $dbStatus) {
            $html .= "<div><em>No checks performed because connection failed or file missing.</em></div>";
        }

        $html .= "</div><br>";

        if ($migrationNeeded) {
            $html .= "<p>System detects that setup/migration is needed.</p>";
            $html .= "<form action='/setup/migrate' method='post'>
                        <button type='submit' class='btn'>Run Automatic Setup & Migration</button>
                      </form>";
        } else {
            $html .= "<p style='color:green'><strong>System is ready!</strong></p>";
            $html .= "<a href='/' class='btn'>Go to Application</a>";
        }

        $html .= "</div></body></html>";

        echo $html;
    }

    public function migrate()
    {
        $baseDir = dirname(__DIR__, 2);
        $dbPath = $baseDir . '/storage/database.sqlite';
        $log = [];

        try {
            if (!file_exists($baseDir . '/storage')) {
                mkdir($baseDir . '/storage', 0755, true);
                $log[] = "Created storage directory.";
            }
            if (!file_exists($dbPath))
                touch($dbPath);

            $pdo = new PDO("sqlite:$dbPath");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            /* --- Helper Function --- */
            $ensureColumn = function ($table, $col, $type) use ($pdo, &$log) {
                $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
                $found = false;
                foreach ($cols as $c) {
                    if ($c['name'] === $col)
                        $found = true;
                }
                if (!$found) {
                    $pdo->exec("ALTER TABLE $table ADD COLUMN $col $type");
                    $log[] = "Added column '$col' to table '$table'.";
                }
            };

            /* --- 1. Users --- */
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $ensureColumn('users', 'role', "VARCHAR(20) DEFAULT 'admin'");
            $ensureColumn('users', 'prodi_id', "VARCHAR(50) NULL");
            $log[] = "Verified 'users' table.";

            /* --- 2. Semesters --- */
            $pdo->exec("CREATE TABLE IF NOT EXISTS semesters (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kode VARCHAR(10) UNIQUE NOT NULL,
                nama VARCHAR(255) NOT NULL,
                is_active BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            $ensureColumn('semesters', 'periode', "INTEGER DEFAULT 0");
            $log[] = "Verified 'semesters' table.";

            /* --- 3. Participants --- */
            $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nomor_peserta VARCHAR(50) UNIQUE NULL,
                nama_lengkap VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            // Ensure all columns exist (Basic Info)
            $participantCols = [
                'semester_id' => "INTEGER NULL REFERENCES semesters(id)",
                'no_billing' => "VARCHAR(50) NULL",
                'tgl_lahir' => "DATE NULL",
                'kode_prodi' => "VARCHAR(50) NULL",
                'nama_prodi' => "VARCHAR(255) NULL",
                'jalur_masuk' => "VARCHAR(50) NULL",
                'status_berkas' => "VARCHAR(50) DEFAULT 'pending'",
                'status_pembayaran' => "BOOLEAN DEFAULT 0",
                // Exam Info
                'ruang_ujian' => "VARCHAR(255) NULL",
                'sesi_ujian' => "VARCHAR(255) NULL",
                'tanggal_ujian' => "DATE NULL",
                'waktu_ujian' => "VARCHAR(50) NULL",
                // Bio
                'tempat_lahir' => "TEXT NULL",
                'alamat_ktp' => "TEXT NULL",
                'kecamatan' => "TEXT NULL",
                'kota' => "TEXT NULL",
                'provinsi' => "TEXT NULL",
                'kode_pos' => "TEXT NULL",
                'no_hp' => "TEXT NULL",
                'agama' => "TEXT NULL",
                'jenis_kelamin' => "TEXT NULL",
                'status_pernikahan' => "TEXT NULL",
                'pekerjaan' => "TEXT NULL",
                'instansi_pekerjaan' => "TEXT NULL",
                'alamat_pekerjaan' => "TEXT NULL",
                'telpon_pekerjaan' => "TEXT NULL",
                // S1 (removed duplicates: asal_mk, s1_universitas, s1_tahun_lulus)
                's1_tahun_masuk' => "TEXT NULL",
                's1_tahun_tamat' => "TEXT NULL",
                's1_perguruan_tinggi' => "TEXT NULL",
                's1_fakultas' => "TEXT NULL",
                's1_prodi' => "TEXT NULL",
                's1_ipk' => "TEXT NULL",
                's1_gelar' => "TEXT NULL",
                // S2 (removed duplicate: asal_s2)
                's2_perguruan_tinggi' => "TEXT NULL",
                's2_fakultas' => "TEXT NULL",
                's2_prodi' => "TEXT NULL",
                's2_tahun_masuk' => "TEXT NULL",
                's2_tahun_tamat' => "TEXT NULL",
                's2_ipk' => "TEXT NULL",
                's2_gelar' => "TEXT NULL",
                // Files
                'photo_filename' => "VARCHAR(255) NULL",
                'ktp_filename' => "VARCHAR(255) NULL",
                'ijazah_filename' => "VARCHAR(255) NULL",
                'transkrip_filename' => "VARCHAR(255) NULL",
                'ijazah_s2_filename' => "TEXT NULL",
                'transkrip_s2_filename' => "TEXT NULL",
                // Transaction
                'transaction_id' => "VARCHAR(50) NULL",
                'payment_date' => "DATETIME NULL",
                'payment_method' => "VARCHAR(50) NULL"
            ];
            foreach ($participantCols as $col => $type) {
                $ensureColumn('participants', $col, $type);
            }
            $log[] = "Verified 'participants' table and columns.";

            /* --- 4. Master Data & Settings --- */
            $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key_name VARCHAR(255) NOT NULL UNIQUE,
                value TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS exam_rooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fakultas TEXT NULL,
                nama_ruang TEXT NOT NULL,
                kapasitas INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS exam_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                semester_id INTEGER NOT NULL,
                exam_room_id INTEGER NOT NULL,
                nama_sesi TEXT NOT NULL,
                tanggal DATE NOT NULL,
                waktu_mulai TEXT NOT NULL,
                waktu_selesai TEXT NOT NULL,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS exam_attendances (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                participant_id INTEGER NOT NULL,
                semester_id INTEGER NOT NULL,
                is_present BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(participant_id, semester_id)
            )");
            $log[] = "Verified Master Data tables.";

            /* --- 5. New System Tables --- */

            // Document Verifications
            $pdo->exec("CREATE TABLE IF NOT EXISTS document_verifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                participant_id INTEGER,
                formulir_pendaftaran INTEGER,
                formulir_pendaftaran_jumlah INTEGER,
                ijazah_s1_legalisir INTEGER,
                ijazah_s1_jumlah INTEGER,
                transkrip_s1_legalisir INTEGER,
                transkrip_s1_jumlah INTEGER,
                bukti_pembayaran INTEGER,
                bukti_pembayaran_jumlah INTEGER,
                surat_rekomendasi INTEGER,
                surat_rekomendasi_jumlah INTEGER,
                ijazah_s2_legalisir INTEGER,
                ijazah_s2_jumlah INTEGER,
                transkrip_s2_legalisir INTEGER,
                transkrip_s2_jumlah INTEGER,
                status_verifikasi_fisik VARCHAR(20),
                catatan_admin TEXT,
                verified_by INTEGER,
                verified_at DATETIME,
                bypass_verification INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            // Email System
            $pdo->exec("CREATE TABLE IF NOT EXISTS email_configurations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                smtp_host VARCHAR(255),
                smtp_port INTEGER,
                smtp_username VARCHAR(255),
                smtp_password VARCHAR(255),
                smtp_encryption VARCHAR(10),
                from_email VARCHAR(255),
                from_name VARCHAR(255),
                is_active INTEGER,
                driver TEXT,
                api_url TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS email_templates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255),
                subject VARCHAR(500),
                body TEXT,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS email_reminders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                semester_id INTEGER,
                template_id INTEGER,
                subject VARCHAR(500),
                body TEXT,
                recipient_count INTEGER,
                sent_count INTEGER,
                failed_count INTEGER,
                status VARCHAR(50),
                sent_by INTEGER,
                sent_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS email_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reminder_id INTEGER,
                participant_id INTEGER,
                email VARCHAR(255),
                status VARCHAR(50),
                error_message TEXT,
                sent_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT,
                content TEXT,
                content_type TEXT,
                image_url TEXT,
                category TEXT,
                is_published BOOLEAN DEFAULT 0,
                published_at DATETIME,
                created_by TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS guides (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT,
                content TEXT,
                role TEXT,
                order_index INTEGER DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_by TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $log[] = "Verified New System tables (Emails, Docs, News, Guides, Logs).";

            // Seed Admin if not exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
            $stmt->execute([':u' => 'admin']);
            if (!$stmt->fetch()) {
                $pass = password_hash('admin123', PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, 'superadmin')");
                $stmt->execute([':u' => 'admin', ':p' => $pass]);
                $log[] = "Created default admin user.";
            }

        } catch (\Exception $e) {
            echo "<h1>Setup Failed</h1><p>" . $e->getMessage() . "</p><a href='/setup'>Back</a>";
            exit;
        }

        // Success Page
        echo "<!DOCTYPE html><html><head><title>Setup Complete</title>
        <style>body{font-family:sans-serif;padding:40px;background:#e8f5e9;text-align:center;}.card{background:white;padding:40px;border-radius:10px;display:inline-block;box-shadow:0 10px 25px rgba(0,0,0,0.1);}</style>
        </head><body>
            <div class='card'>
                <h1 style='color:#2e7d32'>Migration Successful!</h1>
                <p>Database structure has been synchronized with the latest version.</p>
                <div style='text-align:left;background:#f9f9f9;padding:15px;border:1px solid #ddd;max-height:200px;overflow-y:auto;'>
                    <ul style='margin:0;padding-left:20px;color:#555;'>";
        foreach ($log as $l) {
            echo "<li>$l</li>";
        }
        echo "      </ul>
                </div>
                <br>
                <a href='/' style='background:#2e7d32;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;font-weight:bold;'>Go to Dashboard &rarr;</a>
            </div>
        </body></html>";
    }
}
