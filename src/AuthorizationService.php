<?php

declare(strict_types=1);

class AuthorizationService
{
    public static function canAccessEmployee(int $actorId, string $actorRole, int $employeeId): bool
    {
        if ($actorRole === 'admin') {
            return true;
        }
        if (!in_array($actorRole, ['manager', 'dept_manager'], true)) {
            return (int) $employeeId === $actorId;
        }
        if ((int) $employeeId === $actorId) {
            return true;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT manager_id, role FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch();

        return $employee
            && $employee['role'] === 'employee'
            && (int) $employee['manager_id'] === $actorId;
    }

    public static function canManageUser(int $actorId, string $actorRole, array $targetUser): bool
    {
        if ($actorRole === 'admin') {
            return true;
        }

        return $targetUser['role'] === 'employee'
            && (int) ($targetUser['manager_id'] ?? 0) === $actorId;
    }

    public static function requireEmployeeAccess(int $employeeId): void
    {
        if (!self::canAccessEmployee(Auth::id(), Auth::role(), $employeeId)) {
            flash('error', 'لا يمكنك الوصول إلى هذا الموظف.');
            redirect(RoleHelper::dashboardPath(Auth::role()));
        }
    }

    public static function requireUserManagement(array $targetUser): void
    {
        if (!self::canManageUser(Auth::id(), Auth::role(), $targetUser)) {
            flash('error', 'لا يمكنك إدارة هذا المستخدم.');
            redirect('/manager/users');
        }
    }
}
