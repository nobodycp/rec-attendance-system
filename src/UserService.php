<?php

declare(strict_types=1);

class UserService
{
    public static function listForAdmin(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query(
            'SELECT u.*, m.name AS manager_name
             FROM users u
             LEFT JOIN users m ON m.id = u.manager_id
             ORDER BY u.role, u.name'
        )->fetchAll();
    }

    public static function listForManager(int $managerId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT u.*, m.name AS manager_name
             FROM users u
             LEFT JOIN users m ON m.id = u.manager_id
             WHERE u.manager_id = ? AND u.role = "employee"
             ORDER BY u.name'
        );
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }

    public static function supervisors(): array
    {
        $pdo = Database::getConnection();
        $roles = implode('","', RoleHelper::supervisorRoles());
        return $pdo->query(
            "SELECT id, name, role FROM users WHERE role IN (\"{$roles}\") AND is_active = 1 ORDER BY name"
        )->fetchAll();
    }

    public static function create(
        string $name,
        string $email,
        string $password,
        string $role,
        string $timezone,
        ?int $managerId
    ): int {
        $name = trim($name);
        $email = trim(strtolower($email));

        if ($name === '' || $email === '' || strlen($password) < 6) {
            throw new InvalidArgumentException('يرجى تعبئة جميع الحقول. كلمة المرور 6 أحرف على الأقل.');
        }
        if (!RoleHelper::isValid($role)) {
            throw new InvalidArgumentException('الدور غير صالح.');
        }
        if ($role === 'employee' && !$managerId) {
            throw new InvalidArgumentException('يجب تعيين مشرف أو مدير قسم للموظف.');
        }

        $pdo = Database::getConnection();
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetch()) {
            throw new RuntimeException('البريد الإلكتروني مستخدم مسبقاً.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, timezone, manager_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $email,
            $hash,
            $role,
            $timezone,
            $role === 'employee' ? $managerId : null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function toggleActive(int $userId, int $actorId, string $actorRole): void
    {
        if ($userId === $actorId) {
            throw new RuntimeException('لا يمكنك تعطيل حسابك.');
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }

        if ($actorRole !== 'admin') {
            if ($user['role'] !== 'employee' || (int) $user['manager_id'] !== $actorId) {
                throw new RuntimeException('لا يمكنك تعديل هذا المستخدم.');
            }
        }

        $newStatus = (int) $user['is_active'] === 1 ? 0 : 1;
        $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$newStatus, $userId]);
    }

    public static function delete(int $userId, int $actorId, string $actorRole): void
    {
        if ($actorRole !== 'admin') {
            throw new RuntimeException('حذف المستخدمين متاح لمسؤول النظام فقط.');
        }
        if ($userId === $actorId) {
            throw new RuntimeException('لا يمكنك حذف حسابك.');
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }

        if ($user['role'] === 'admin') {
            $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin" AND is_active = 1')->fetchColumn();
            if ($adminCount <= 1) {
                throw new RuntimeException('لا يمكن حذف آخر مسؤول نظام نشط.');
            }
        }

        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
    }
}
