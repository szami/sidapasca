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
            response()->redirect('/admin');
            return;
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

        // Add UI helpers
        foreach ($data as &$user) {
            $user['role_display'] = RoleHelper::getRoleDisplayName($user['role']);
            $user['role_badge'] = RoleHelper::getRoleBadgeClass($user['role']);
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
            response()->redirect('/admin');
            return;
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
            response()->redirect('/admin');
            return;
        }

        $username = request()->get('username');
        $password = request()->get('password');
        $role = request()->get('role');
        $prodiId = request()->get('prodi_id');

        // Validation
        if (empty($username) || empty($password) || empty($role)) {
            response()->redirect('/admin/users/create?error=empty_fields');
            return;
        }

        // For admin_prodi: username must equal prodi_id
        if ($role === 'admin_prodi') {
            if (empty($prodiId)) {
                response()->redirect('/admin/users/create?error=prodi_required');
                return;
            }
            // Auto-set username = prodi_id
            $username = $prodiId;
        }

        // Check if username exists
        $db = Database::connection();
        $existing = $db->select('users')->where('username', $username)->fetchAssoc();

        if ($existing) {
            response()->redirect('/admin/users/create?error=username_exists');
            return;
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

        response()->redirect('/admin/users?success=created');
    }

    public function edit($id)
    {
        if (!RoleHelper::canManageUsers()) {
            response()->redirect('/admin');
            return;
        }

        $db = Database::connection();
        $user = $db->select('users')->where('id', $id)->fetchAssoc();

        if (!$user) {
            response()->redirect('/admin/users?error=not_found');
            return;
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
            response()->redirect('/admin');
            return;
        }

        $db = Database::connection();
        $user = $db->select('users')->where('id', $id)->fetchAssoc();

        if (!$user) {
            response()->redirect('/admin/users?error=not_found');
            return;
        }

        $username = request()->get('username');
        $password = request()->get('password'); // Optional on edit
        $role = request()->get('role');
        $prodiId = request()->get('prodi_id');

        // Validation
        if (empty($username) || empty($role)) {
            response()->redirect("/admin/users/edit/{$id}?error=empty_fields");
            return;
        }

        // For admin_prodi: username must equal prodi_id
        if ($role === 'admin_prodi') {
            if (empty($prodiId)) {
                response()->redirect("/admin/users/edit/{$id}?error=prodi_required");
                return;
            }
            $username = $prodiId;
        }

        // Check if username exists (except current user)
        $existing = $db->select('users')
            ->where('username', $username)
            ->where('id', '!=', $id)
            ->fetchAssoc();

        if ($existing) {
            response()->redirect("/admin/users/edit/{$id}?error=username_exists");
            return;
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

        response()->redirect('/admin/users?success=updated');
    }

    public function destroy($id)
    {
        if (!RoleHelper::canManageUsers()) {
            response()->redirect('/admin');
            return;
        }

        $db = Database::connection();

        // Prevent deleting own account
        if ($id == RoleHelper::getUserId()) {
            response()->redirect('/admin/users?error=cannot_delete_self');
            return;
        }

        // Prevent deleting the only superadmin
        $user = $db->select('users')->where('id', $id)->fetchAssoc();
        if ($user && $user['role'] === 'superadmin') {
            $superadminCount = $db->select('users')->where('role', 'superadmin')->fetchAll();
            if (count($superadminCount) <= 1) {
                response()->redirect('/admin/users?error=last_superadmin');
                return;
            }
        }

        $db->delete('users')->where('id', $id)->execute();

        response()->redirect('/admin/users?success=deleted');
    }

    /**
     * Change password form (for all logged-in users)
     */
    public function changePasswordForm()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        echo View::render('admin.users.change_password');
    }

    /**
     * Update password (for all logged-in users)
     */
    public function changePassword()
    {
        if (!isset($_SESSION['admin'])) {
            response()->redirect('/admin/login');
            return;
        }

        $currentPassword = request()->get('current_password');
        $newPassword = request()->get('new_password');
        $confirmPassword = request()->get('confirm_password');

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            response()->redirect('/admin/change-password?error=empty_fields');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            response()->redirect('/admin/change-password?error=password_mismatch');
            return;
        }

        if (strlen($newPassword) < 6) {
            response()->redirect('/admin/change-password?error=password_too_short');
            return;
        }

        // Get current user
        $db = Database::connection();
        $user = $db->select('users')->where('id', $_SESSION['admin'])->fetchAssoc();

        if (!$user) {
            response()->redirect('/admin/login');
            return;
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            response()->redirect('/admin/change-password?error=wrong_password');
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $db->update('users')
            ->params(['password' => $hashedPassword])
            ->where('id', $user['id'])
            ->execute();

        response()->redirect('/admin/change-password?success=updated');
    }
}
