<?php

declare(strict_types=1);

class UserService
{
    public const MIN_PASSWORD_LENGTH = 8;

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

    public static function find(int $userId): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function supervisors(): array
    {
        $pdo = Database::getConnection();
        $roles = implode('","', RoleHelper::supervisorRoles());
        return $pdo->query(
            "SELECT id, name, role FROM users WHERE role IN (\"{$roles}\") AND is_active = 1 ORDER BY name"
        )->fetchAll();
    }

    public static function activeEmployees(?int $managerId = null): array
    {
        $pdo = Database::getConnection();
        if ($managerId === null) {
            return $pdo->query(
                'SELECT id, name, email, timezone FROM users WHERE role = "employee" AND is_active = 1 ORDER BY name'
            )->fetchAll();
        }

        $stmt = $pdo->prepare(
            'SELECT id, name, email, timezone FROM users WHERE manager_id = ? AND role = "employee" AND is_active = 1 ORDER BY name'
        );
        $stmt->execute([$managerId]);

        return $stmt->fetchAll();
    }

    public static function create(
        string $name,
        string $email,
        string $password,
        string $role,
        string $timezone,
        ?int $managerId
    ): int {
        self::validatePassword($password);

        $name = trim($name);
        $email = trim(strtolower($email));

        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('يرجى تعبئة جميع الحقول المطلوبة.');
        }
        if (!RoleHelper::isValid($role)) {
            throw new InvalidArgumentException('الدور غير صالح.');
        }
        if ($role === 'employee' && !$managerId) {
            throw new InvalidArgumentException('يجب تعيين مشرف أو مدير قسم للموظف.');
        }
        if (!TimezoneHelper::isValid($timezone)) {
            throw new InvalidArgumentException('المنطقة الزمنية غير صالحة.');
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

        $id = (int) $pdo->lastInsertId();
        AuditService::log('user.create', $id, $email);

        return $id;
    }

    public static function update(
        int $userId,
        string $name,
        string $email,
        string $role,
        string $timezone,
        ?int $managerId,
        int $actorId,
        string $actorRole
    ): void {
        $user = self::find($userId);
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }

        AuthorizationService::requireUserManagement($user);

        $name = trim($name);
        $email = trim(strtolower($email));
        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('يرجى تعبئة جميع الحقول المطلوبة.');
        }
        if ($actorRole !== 'admin') {
            $role = 'employee';
            $managerId = $actorId;
        } elseif (!RoleHelper::isValid($role)) {
            throw new InvalidArgumentException('الدور غير صالح.');
        }
        if ($role === 'employee' && !$managerId) {
            throw new InvalidArgumentException('يجب تعيين مشرف للموظف.');
        }
        if (!TimezoneHelper::isValid($timezone)) {
            throw new InvalidArgumentException('المنطقة الزمنية غير صالحة.');
        }

        $pdo = Database::getConnection();
        $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $exists->execute([$email, $userId]);
        if ($exists->fetch()) {
            throw new RuntimeException('البريد الإلكتروني مستخدم مسبقاً.');
        }

        $pdo->prepare(
            'UPDATE users SET name = ?, email = ?, role = ?, timezone = ?, manager_id = ? WHERE id = ?'
        )->execute([
            $name,
            $email,
            $role,
            $timezone,
            $role === 'employee' ? $managerId : null,
            $userId,
        ]);

        AuditService::log('user.update', $userId, $email);
    }

    public static function resetPassword(int $userId, string $password, int $actorId, string $actorRole): void
    {
        $user = self::find($userId);
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }
        if ($actorRole !== 'admin' && !AuthorizationService::canManageUser($actorId, $actorRole, $user)) {
            throw new RuntimeException('لا يمكنك إعادة تعيين كلمة مرور هذا المستخدم.');
        }

        self::validatePassword($password);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        Database::getConnection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
        AuditService::log('user.reset_password', $userId);
    }

    public static function toggleActive(int $userId, int $actorId, string $actorRole): void
    {
        if ($userId === $actorId) {
            throw new RuntimeException('لا يمكنك تعطيل حسابك.');
        }

        $user = self::find($userId);
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }

        AuthorizationService::requireUserManagement($user);

        $newStatus = (int) $user['is_active'] === 1 ? 0 : 1;
        Database::getConnection()->prepare('UPDATE users SET is_active = ? WHERE id = ?')->execute([$newStatus, $userId]);
        AuditService::log($newStatus ? 'user.activate' : 'user.deactivate', $userId);
    }

    public static function delete(int $userId, int $actorId, string $actorRole): void
    {
        if ($actorRole !== 'admin') {
            throw new RuntimeException('حذف المستخدمين متاح لمسؤول النظام فقط.');
        }
        if ($userId === $actorId) {
            throw new RuntimeException('لا يمكنك حذف حسابك.');
        }

        $user = self::find($userId);
        if (!$user) {
            throw new RuntimeException('المستخدم غير موجود.');
        }

        if ($user['role'] === 'admin') {
            $adminCount = (int) Database::getConnection()->query(
                'SELECT COUNT(*) FROM users WHERE role = "admin" AND is_active = 1'
            )->fetchColumn();
            if ($adminCount <= 1) {
                throw new RuntimeException('لا يمكن حذف آخر مسؤول نظام نشط.');
            }
        }

        Database::getConnection()->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        AuditService::log('user.delete', $userId, $user['email']);
    }

    private static function validatePassword(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new InvalidArgumentException(
                'كلمة المرور يجب أن تكون ' . self::MIN_PASSWORD_LENGTH . ' أحرف على الأقل.'
            );
        }
    }
}
