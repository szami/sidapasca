<?php

namespace App\Utils;

/**
 * RoleHelper - Role-based Access Control (RBAC)
 * Supports 5 roles: superadmin, admin, upkh, tu, admin_prodi
 */
class RoleHelper
{
    // Role Constants
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_UPKH = 'upkh';
    const ROLE_TU = 'tu';
    const ROLE_ADMIN_PRODI = 'admin_prodi';

    // All available roles
    const ALL_ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_UPKH,
        self::ROLE_TU,
        self::ROLE_ADMIN_PRODI
    ];

    /**
     * Get current user role from session
     */
    public static function getRole(): ?string
    {
        return $_SESSION['admin_role'] ?? null;
    }

    /**
     * Check if current user is superadmin
     */
    public static function isSuperadmin(): bool
    {
        return self::getRole() === self::ROLE_SUPERADMIN;
    }

    /**
     * Check if current user is admin (superadmin or admin)
     */
    public static function isAdmin(): bool
    {
        return in_array(self::getRole(), [self::ROLE_SUPERADMIN, self::ROLE_ADMIN]);
    }

    /**
     * Check if current user is UPKH
     */
    public static function isUPKH(): bool
    {
        return self::getRole() === self::ROLE_UPKH;
    }

    /**
     * Check if current user is TU
     */
    public static function isTU(): bool
    {
        return self::getRole() === self::ROLE_TU;
    }

    /**
     * Check if current user is admin prodi
     */
    public static function isAdminProdi(): bool
    {
        return self::getRole() === self::ROLE_ADMIN_PRODI;
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
     * Check if user can access admin panel
     */
    public static function canAccessAdmin(): bool
    {
        return isset($_SESSION['admin']) && isset($_SESSION['admin_role']);
    }

    // ==========================================
    // PERMISSION METHODS
    // ==========================================

    /**
     * Check if user can edit participant biodata (update form)
     * Allowed: Superadmin only
     */
    public static function canEditParticipant(): bool
    {
        return self::isSuperadmin();
    }

    /**
     * Check if user can delete participant
     * Allowed: Superadmin only
     */
    public static function canDeleteParticipant(): bool
    {
        return self::isSuperadmin();
    }

    /**
     * Check if user can upload/delete participant documents
     * Allowed: Superadmin, Admin
     */
    public static function canUploadDocuments(): bool
    {
        return self::isAdmin();
    }

    /**
     * Check if user can validate physical documents
     * Allowed: Superadmin, Admin, UPKH
     */
    public static function canValidatePhysical(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_UPKH
        ]);
    }

    /**
     * Check if user can manage exam schedule
     * Allowed: Superadmin, Admin, TU
     */
    public static function canManageSchedule(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TU
        ]);
    }

    /**
     * Check if user can manage users
     * Allowed: Superadmin only
     */
    public static function canManageUsers(): bool
    {
        return self::isSuperadmin();
    }

    /**
     * Check if user can import/export data
     * Allowed: Superadmin, Admin
     */
    public static function canImportExport(): bool
    {
        return self::isAdmin();
    }

    /**
     * Check if user can manage settings
     * Allowed: Superadmin, Admin
     */
    public static function canManageSettings(): bool
    {
        return self::isAdmin();
    }

    /**
     * Check if user can manage email/communication
     * Allowed: Superadmin, Admin, TU
     */
    public static function canManageEmail(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TU
        ]);
    }

    /**
     * Check if user can print cards/forms
     * Allowed: Superadmin, Admin, UPKH
     */
    public static function canPrintCards(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_UPKH
        ]);
    }

    /**
     * Check if user can print schedule/attendance
     * Allowed: Superadmin, Admin, TU
     */
    public static function canPrintSchedule(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TU
        ]);
    }

    /**
     * Check if user can manage master data (rooms/sessions)
     * Allowed: Superadmin, Admin, TU
     */
    public static function canManageMasterData(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TU
        ]);
    }

    /**
     * Check if user can download documents (ZIP)
     * Allowed: Superadmin, Admin, UPKH, Admin Prodi (own prodi)
     */
    public static function canDownloadDocuments(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_UPKH,
            self::ROLE_ADMIN_PRODI
        ]);
    }

    /**
     * Check if user can view reports
     * Allowed: Superadmin, Admin, TU, Admin Prodi
     */
    public static function canViewReports(): bool
    {
        return in_array(self::getRole(), [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_TU,
            self::ROLE_ADMIN_PRODI
        ]);
    }

    /**
     * Check if prodi access is allowed (for Admin Prodi restriction)
     */
    public static function canAccessProdi(?string $prodiId): bool
    {
        // Non admin_prodi can access all
        if (!self::isAdminProdi()) {
            return true;
        }

        // Admin prodi can only access their own prodi
        return self::getProdiId() === $prodiId;
    }

    // ==========================================
    // MENU ACCESS
    // ==========================================

    /**
     * Get menu access array for current role
     * Returns array of menu identifiers that can be accessed
     */
    public static function getMenuAccess(): array
    {
        $role = self::getRole();

        $menuAccess = [
            self::ROLE_SUPERADMIN => [
                'dashboard',
                'admisi',
                'peserta',
                'verification',
                'tools',
                'master',
                'exam',
                'email',
                'settings',
                'users',
                'reports'
            ],
            self::ROLE_ADMIN => [
                'dashboard',
                'admisi',
                'peserta',
                'verification',
                'tools',
                'master',
                'exam',
                'email',
                'settings',
                'reports'
            ],
            self::ROLE_UPKH => [
                'dashboard',
                'admisi_view',
                'peserta_view',
                'verification',
                'download',
                'print_cards',
                'reports_physical'
            ],
            self::ROLE_TU => [
                'dashboard',
                'peserta_view',
                'master',
                'exam',
                'print_schedule',
                'email',
                'reports'
            ],
            self::ROLE_ADMIN_PRODI => [
                'dashboard',
                'admisi_view',
                'peserta_view',
                'download',
                'reports'
            ]
        ];

        return $menuAccess[$role] ?? [];
    }

    /**
     * Check if user can access specific menu
     */
    public static function canAccessMenu(string $menu): bool
    {
        return in_array($menu, self::getMenuAccess());
    }

    // ==========================================
    // UI HELPERS
    // ==========================================

    /**
     * Get role display name (for UI)
     */
    public static function getRoleDisplayName(?string $role = null): string
    {
        $role = $role ?? self::getRole();

        $roleNames = [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_UPKH => 'UPKH',
            self::ROLE_TU => 'Tata Usaha',
            self::ROLE_ADMIN_PRODI => 'Admin Prodi'
        ];

        return $roleNames[$role] ?? 'Unknown';
    }

    /**
     * Get role badge CSS class (for UI - Tailwind)
     */
    public static function getRoleBadgeClass(?string $role = null): string
    {
        $role = $role ?? self::getRole();

        $badgeClasses = [
            self::ROLE_SUPERADMIN => 'bg-red-100 text-red-800',
            self::ROLE_ADMIN => 'bg-blue-100 text-blue-800',
            self::ROLE_UPKH => 'bg-green-100 text-green-800',
            self::ROLE_TU => 'bg-yellow-100 text-yellow-800',
            self::ROLE_ADMIN_PRODI => 'bg-purple-100 text-purple-800'
        ];

        return $badgeClasses[$role] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get all roles for select dropdown
     */
    public static function getAllRolesForSelect(): array
    {
        return [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_UPKH => 'UPKH (Verifikasi Fisik)',
            self::ROLE_TU => 'Tata Usaha (Penjadwalan)',
            self::ROLE_ADMIN_PRODI => 'Admin Prodi'
        ];
    }

    // Legacy compatibility
    public static function canCRUD(): bool
    {
        return self::canEditParticipant();
    }
}
