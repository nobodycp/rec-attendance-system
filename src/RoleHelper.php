<?php

declare(strict_types=1);

class RoleHelper
{
    public const ROLES = [
        'employee' => 'موظف',
        'manager' => 'مشرف',
        'dept_manager' => 'مدير قسم',
        'admin' => 'مسؤول نظام',
    ];

    public static function label(string $role): string
    {
        return self::ROLES[$role] ?? $role;
    }

    public static function all(): array
    {
        return self::ROLES;
    }

    public static function isValid(string $role): bool
    {
        return isset(self::ROLES[$role]);
    }

    public static function isEmployee(string $role): bool
    {
        return $role === 'employee';
    }

    public static function isManagement(string $role): bool
    {
        return in_array($role, ['admin', 'manager', 'dept_manager'], true);
    }

    public static function canManageUsers(string $role): bool
    {
        return in_array($role, ['admin', 'manager', 'dept_manager'], true);
    }

    public static function dashboardPath(string $role): string
    {
        return self::isEmployee($role) ? '/employee/dashboard' : '/manager/dashboard';
    }

    /** أدوار يمكن تعيينها كمشرف مسؤول عن الموظف */
    public static function supervisorRoles(): array
    {
        return ['admin', 'manager', 'dept_manager'];
    }
}
