<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/db.php';

$db = \App\Utils\Database::connection();

echo "=== Creating Test Admin Prodi Users ===\n\n";

// Test users to create
$testUsers = [
    [
        'username' => '73101',
        'password' => '73101', // Same as username
        'role' => 'admin_prodi',
        'prodi_id' => '73101',
        'prodi_name' => 'S2- MAGISTER PSIKOLOGI'
    ],
    [
        'username' => '48101',
        'password' => '48101',
        'role' => 'admin_prodi',
        'prodi_id' => '48101',
        'prodi_name' => 'S2- MAGISTER FARMASI'
    ]
];

foreach ($testUsers as $userData) {
    // Check if user already exists
    $existing = $db->select('users')
        ->where('username', $userData['username'])
        ->first();

    if ($existing) {
        echo "⏭️  User {$userData['username']} ({$userData['prodi_name']}) sudah ada\n";
        continue;
    }

    // Create user
    $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);

    $db->insert('users')->params([
        'username' => $userData['username'],
        'password' => $hashedPassword,
        'role' => $userData['role'],
        'prodi_id' => $userData['prodi_id']
    ])->execute();

    echo "✅ Created user: {$userData['username']} ({$userData['prodi_name']})\n";
    echo "   Username: {$userData['username']}\n";
    echo "   Password: {$userData['password']}\n";
    echo "   Role: {$userData['role']}\n\n";
}

echo "\n=== User List ===\n";
$users = $db->select('users')->orderBy('id', 'DESC')->fetchAll();

foreach ($users as $user) {
    $role = $user['role'] ?? 'admin';
    $prodiInfo = $user['prodi_id'] ? " (Prodi: {$user['prodi_id']})" : '';
    echo "- {$user['username']} | {$role}{$prodiInfo}\n";
}

echo "\nDone!\n";
