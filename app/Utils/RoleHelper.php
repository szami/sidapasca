<?php

namespace App\Utils;

/**
 * RoleHelper - Role-based Access Control
 * Provides methods to check user roles and permissions
 */
class RoleHelper
{
    /**
     * Check if current user is superadmin
     */
    public static function isSuperadmin(): bool
    {
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
    }

    /**
     * Check if current user is admin (superadmin atau admin biasa)
     */
    public static function isAdmin(): bool
    {
        if (!isset($_SESSION['admin_role'])) {
            return false;
        }

        return in_array($_SESSION['admin_role'], ['superadmin', 'admin']);
    }

    /**
     * Check if current user is admin prodi
     */
    public static function isAdminProdi(): bool
    {
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin_prodi';
    }

    /**
     * Get current user role
     */
    public static function getRole(): ?string
    {
        return $_SESSION['admin_role'] ?? null;
    }

    /**
     * Get prodi_id for admin_prodi
     */
    public static function getProdiId(): ?string
    {
        if (self::isAdminProdi()) {
            return $_SESSION['admin_prodi_id'] ?? null;
        }
        return null;
    }

    /**
     * Check if user can do CRUD operations
     * Admin prodi tidak bisa CRUD, hanya view dan download
     */
    public static function canCRUD(): bool
    {
        if (!isset($_SESSION['admin'])) {
            return false;
        }

        return !self::isAdminProdi();
    }

    /**
     * Check if user can access admin panel
     */
    public static function canAccessAdmin(): bool
    {
        return isset($_SESSION['admin']) && isset($_SESSION['admin_role']);
    }

    /**
     * Get user ID from session
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['admin'] ?? null;
    }

    /**
     * Get username from session
     */
    public static function getUsername(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    /**
     * Check if user can access specific menu
     * 
     * @param string $menu Menu identifier (import, settings, users, etc)
     * @return bool
     */
    public static function canAccessMenu(string $menu): bool
    {
        if (self::isSuperadmin()) {
            return true; // Superadmin dapat akses semua
        }

        if (self::isAdmin()) {
            // Admin dapat akses semua kecuali user management
            return $menu !== 'users';
        }

        if (self::isAdminProdi()) {
            // Admin prodi hanya bisa akses participants dan reports
            return in_array($menu, ['participants', 'reports']);
        }

        return false;
    }

    /**
     * Get role display name (for UI)
     */
    public static function getRoleDisplayName(?string $role = null): string
    {
        $role = $role ?? self::getRole();

        $roleNames = [
            'superadmin' => 'Super Admin',
            'admin' => 'Administrator',
            'admin_prodi' => 'Admin Prodi'
        ];

        return $roleNames[$role] ?? 'Unknown';
    }

    /**
     * Get role badge CSS class (for UI)
     */
    public static function getRoleBadgeClass(?string $role = null): string
    {
        $role = $role ?? self::getRole();

        $badgeClasses = [
            'superadmin' => 'badge-danger',
            'admin' => 'badge-primary',
            'admin_prodi' => 'badge-info'
        ];

        return $badgeClasses[$role] ?? 'badge-secondary';
    }
}
