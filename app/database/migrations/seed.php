<?php

// Standalone Native PDO Seeder
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

    // 1. Seed Settings (Defaults)
    $settings = [
        'app_name' => 'SIDA Pasca ULM',
        'timezone' => 'Asia/Makassar',
        'allow_exam_card_download' => '0',
        'tanggal_ujian' => '20 Januari 2024',
        'lokasi_ujian' => 'Gedung Pascasarjana ULM, Banjarmasin',
        'pengumuman' => 'Peserta wajib membawa Kartu Tanda Peserta Ujian dan Identitas Diri (KTP/SIM) asli.'
    ];

    echo "Seeding Settings...\n";
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key_name, value) VALUES (:key, :value)");
        $stmt->execute([':key' => $key, ':value' => $value]);
    }

    // 2. Seed Master Data: Exam Rooms
    echo "Seeding Exam Rooms...\n";
    $count = $pdo->query("SELECT count(*) FROM exam_rooms")->fetchColumn();
    if ($count == 0) {
        $rooms = [
            ['Gedung Pascasarjana Lt. 1', 'Ruang 101', 30],
            ['Gedung Pascasarjana Lt. 1', 'Ruang 102', 30],
            ['Gedung Pascasarjana Lt. 2', 'Ruang 201', 40],
            ['Gedung Pascasarjana Lt. 2', 'Ruang 202', 40],
            ['Gedung Pascasarjana Lt. 3', 'Aula Utama', 100],
        ];

        $stmt = $pdo->prepare("INSERT INTO exam_rooms (fakultas, nama_ruang, kapasitas) VALUES (?, ?, ?)");
        foreach ($rooms as $room) {
            $stmt->execute($room);
        }
        echo "Inserted " . count($rooms) . " rooms.\n";
    } else {
        echo "Exam rooms already exist.\n";
    }

    // 3. Seed Master Data: Exam Sessions (for Semester ID 1)
    echo "Seeding Exam Sessions (Example)...\n";
    $countSess = $pdo->query("SELECT count(*) FROM exam_sessions")->fetchColumn();
    if ($countSess == 0) {
        // Assume semester 1 exists and room 1 exists
        $stmt = $pdo->prepare("INSERT INTO exam_sessions (semester_id, exam_room_id, nama_sesi, tanggal, waktu_mulai, waktu_selesai) VALUES (?, ?, ?, ?, ?, ?)");

        // Sesi 1
        $stmt->execute([1, 1, 'Sesi 1 (TPA)', date('Y-m-d', strtotime('+1 month')), '08:00', '10:00']);
        // Sesi 2
        $stmt->execute([1, 1, 'Sesi 2 (Wawancara)', date('Y-m-d', strtotime('+1 month')), '10:30', '12:00']);
        // Sesi 3
        $stmt->execute([1, 1, 'Sesi 3 (Lanjutan)', date('Y-m-d', strtotime('+1 month')), '13:00', '15:00']);

        echo "Inserted sample sessions.\n";
    } else {
        echo "Exam sessions already exist.\n";
    }

    echo "Seeding completed successfully!\n";

} catch (PDOException $e) {
    echo "Seeding Error: " . $e->getMessage() . "\n";
    exit(1);
}
