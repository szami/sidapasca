<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db.php';

$db = \App\Utils\Database::connection();

echo "=== Daftar Semester ===\n\n";
$semesters = $db->select('semesters')->orderBy('id', 'DESC')->fetchAll();

foreach ($semesters as $sem) {
    echo "ID: {$sem['id']} | Kode: {$sem['kode']} | {$sem['nama']}";
    if ($sem['is_active'])
        echo " [AKTIF]";
    echo "\n";
}

echo "\n=== Data Prodi per Semester ===\n\n";

foreach ($semesters as $sem) {
    $result = $db->query("
        SELECT DISTINCT kode_prodi, nama_prodi, COUNT(*) as total
        FROM participants 
        WHERE semester_id = {$sem['id']} 
        AND kode_prodi IS NOT NULL 
        GROUP BY kode_prodi, nama_prodi
        ORDER BY kode_prodi
    ")->fetchAll();

    if (!empty($result)) {
        echo "Semester: {$sem['kode']} - {$sem['nama']}\n";
        echo str_repeat('-', 60) . "\n";

        foreach ($result as $row) {
            printf(
                "  %-6s | %-30s | %d peserta\n",
                $row['kode_prodi'],
                $row['nama_prodi'],
                $row['total']
            );
        }
        echo "\n";
    }
}
