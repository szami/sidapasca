<?php

namespace App\Controllers;

use App\Utils\View;
use App\Utils\Database;
use App\Utils\RoleHelper;

class UserController
{
    public function index()
    {
        // Only superadmin can access user management
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        echo View::render('admin.users.index');
    }

    public function apiData()
    {
        if (!RoleHelper::canManageUsers()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $db = Database::connection();

        // DataTables parameters
        $draw = intval(request()->get('draw') ?? 1);
        $start = intval(request()->get('start') ?? 0);
        $length = intval(request()->get('length') ?? 10);
        $search = request()->get('search')['value'] ?? '';
        $orderColumnIndex = request()->get('order')[0]['column'] ?? 0;
        $orderDir = request()->get('order')[0]['dir'] ?? 'desc';

        $columns = [
            0 => 'id',
            1 => 'username',
            2 => 'role',
            3 => 'prodi_id'
        ];
        $orderBy = $columns[$orderColumnIndex] ?? 'id';

        // Base query
        $whereClause = "WHERE 1=1";
        if (!empty($search)) {
            $searchEscaped = str_replace("'", "''", $search);
            $whereClause .= " AND (username LIKE '%$searchEscaped%' OR role LIKE '%$searchEscaped%' OR prodi_id LIKE '%$searchEscaped%')";
        }

        $totalRes = $db->query("SELECT COUNT(*) as total FROM users")->fetchAssoc();
        $totalRecords = $totalRes['total'] ?? 0;
        $filteredRes = $db->query("SELECT COUNT(*) as total FROM users $whereClause")->fetchAssoc();
        $recordsFiltered = $filteredRes['total'] ?? 0;

        $sql = "SELECT * FROM users $whereClause ORDER BY $orderBy $orderDir LIMIT $length OFFSET $start";
        $data = $db->query($sql)->fetchAll();

        // Fetch prodi names map
        $prodiIds = array_filter(array_column($data, 'prodi_id'));
        $prodiMap = [];
        if (!empty($prodiIds)) {
            $idsStr = "'" . implode("','", array_map('addslashes', $prodiIds)) . "'";
            $prodiRows = $db->query("SELECT DISTINCT kode_prodi, nama_prodi FROM participants WHERE kode_prodi IN ($idsStr)")->fetchAll();
            foreach ($prodiRows as $pr) {
                // Take the first name found for this code
                if (!isset($prodiMap[$pr['kode_prodi']])) {
                    $prodiMap[$pr['kode_prodi']] = $pr['nama_prodi'];
                }
            }
        }

        // Add UI helpers
        foreach ($data as &$user) {
            $user['role_display'] = RoleHelper::getRoleDisplayName($user['role']);
            $user['role_badge'] = RoleHelper::getRoleBadgeClass($user['role']);
            $user['nama_prodi'] = $prodiMap[$user['prodi_id']] ?? '';
        }

        response()->json([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function create()
    {
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        // Get all prodi for dropdown
        $db = Database::connection();
        $prodis = $db->query("
            SELECT DISTINCT kode_prodi, nama_prodi 
            FROM participants 
            WHERE kode_prodi IS NOT NULL 
            ORDER BY kode_prodi
        ")->fetchAll();

        echo View::render('admin.users.form', [
            'user' => null,
            'prodis' => $prodis,
            'isEdit' => false
        ]);
    }

    public function store()
    {
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        $username = request()->get('username');
        $password = request()->get('password');
        $role = request()->get('role');
        $prodiId = request()->get('prodi_id');

        // Validation
        if (empty($username) || empty($password) || empty($role)) {
            header('Location: /admin/users/create?error=empty_fields');
            exit;
        }

        // For admin_prodi: username must equal prodi_id
        if ($role === 'admin_prodi') {
            if (empty($prodiId)) {
                header('Location: /admin/users/create?error=prodi_required');
                exit;
            }
            // Auto-set username = prodi_id
            $username = $prodiId;
        }

        // Check if username exists
        $db = Database::connection();
        $existing = $db->select('users')->where('username', $username)->fetchAssoc();

        if ($existing) {
            header('Location: /admin/users/create?error=username_exists');
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $db->insert('users')->params([
            'username' => $username,
            'password' => $hashedPassword,
            'role' => $role,
            'prodi_id' => $role === 'admin_prodi' ? $prodiId : null
        ])->execute();

        header('Location: /admin/users?success=created');
        exit;
    }

    public function edit($id)
    {
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        $db = Database::connection();
        $user = $db->select('users')->where('id', $id)->fetchAssoc();

        if (!$user) {
            header('Location: /admin/users?error=not_found');
            exit;
        }

        // Get all prodi for dropdown
        $prodis = $db->query("
            SELECT DISTINCT kode_prodi, nama_prodi 
            FROM participants 
            WHERE kode_prodi IS NOT NULL 
            ORDER BY kode_prodi
        ")->fetchAll();

        echo View::render('admin.users.form', [
            'user' => $user,
            'prodis' => $prodis,
            'isEdit' => true
        ]);
    }

    public function update($id)
    {
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        $db = Database::connection();
        $user = $db->select('users')->where('id', $id)->fetchAssoc();

        if (!$user) {
            header('Location: /admin/users?error=not_found');
            exit;
        }

        $username = request()->get('username');
        $password = request()->get('password'); // Optional on edit
        $role = request()->get('role');
        $prodiId = request()->get('prodi_id');

        // Validation
        if (empty($username) || empty($role)) {
            header("Location: /admin/users/edit/{$id}?error=empty_fields");
            exit;
        }

        // For admin_prodi: username must equal prodi_id
        if ($role === 'admin_prodi') {
            if (empty($prodiId)) {
                header("Location: /admin/users/edit/{$id}?error=prodi_required");
                exit;
            }
            $username = $prodiId;
        }

        // Check if username exists (except current user)
        $existing = $db->select('users')
            ->where('username', $username)
            ->where('id', '!=', $id)
            ->fetchAssoc();

        if ($existing) {
            header("Location: /admin/users/edit/{$id}?error=username_exists");
            exit;
        }

        // Prepare update data
        $updateData = [
            'username' => $username,
            'role' => $role,
            'prodi_id' => $role === 'admin_prodi' ? $prodiId : null
        ];

        // Only update password if provided
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        // Update user
        $db->update('users')
            ->params($updateData)
            ->where('id', $id)
            ->execute();

        header('Location: /admin/users?success=updated');
        exit;
    }

    public function destroy($id)
    {
        if (!RoleHelper::canManageUsers()) {
            header('Location: /admin');
            exit;
        }

        $db = Database::connection();

        // Prevent deleting own account
        if ($id == RoleHelper::getUserId()) {
            header('Location: /admin/users?error=cannot_delete_self');
            exit;
        }

        // Prevent deleting the only superadmin
        $user = $db->select('users')->where('id', $id)->fetchAssoc();
        if ($user && $user['role'] === 'superadmin') {
            $superadminCount = $db->select('users')->where('role', 'superadmin')->fetchAll();
            if (count($superadminCount) <= 1) {
                header('Location: /admin/users?error=last_superadmin');
                exit;
            }
        }

        $db->delete('users')->where('id', $id)->execute();

        header('Location: /admin/users?success=deleted');
        exit;
    }

    /**
     * Change password form (for all logged-in users)
     */
    public function changePasswordForm()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        echo View::render('admin.users.change_password');
    }

    /**
     * Update password (for all logged-in users)
     */
    public function changePassword()
    {
        if (!isset($_SESSION['admin'])) {
            header('Location: /admin/login');
            exit;
        }

        $currentPassword = request()->get('current_password');
        $newPassword = request()->get('new_password');
        $confirmPassword = request()->get('confirm_password');

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            header('Location: /admin/change-password?error=empty_fields');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            header('Location: /admin/change-password?error=password_mismatch');
            exit;
        }

        if (strlen($newPassword) < 6) {
            header('Location: /admin/change-password?error=password_too_short');
            exit;
        }

        // Get current user
        $db = Database::connection();
        $user = $db->select('users')->where('id', $_SESSION['admin'])->fetchAssoc();

        if (!$user) {
            header('Location: /admin/login');
            exit;
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            header('Location: /admin/change-password?error=wrong_password');
            exit;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->update('users')
            ->params(['password' => $hashedPassword])
            ->where('id', $user['id'])
            ->execute();

        header('Location: /admin/change-password?success=updated');
        exit;
    }
}
