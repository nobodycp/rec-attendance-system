<?php

declare(strict_types=1);

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        if (LoginRateLimiter::tooManyAttempts($email)) {
            flash('error', 'محاولات كثيرة. انتظر 15 دقيقة ثم حاول مجدداً.');
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            LoginRateLimiter::recordFailure($email);
            return false;
        }

        LoginRateLimiter::clear($email);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_timezone'] = $user['timezone'];
        $_SESSION['user_avatar'] = $user['avatar_path'] ?? null;

        AuditService::log('login', (int) $user['id']);

        return true;
    }

    public static function logout(): void
    {
        if (self::check()) {
            AuditService::log('logout');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function role(): string
    {
        return $_SESSION['user_role'] ?? '';
    }

    public static function timezone(): string
    {
        return $_SESSION['user_timezone'] ?? 'Asia/Riyadh';
    }

    public static function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }

    public static function email(): string
    {
        return $_SESSION['user_email'] ?? '';
    }

    public static function avatarPath(): ?string
    {
        $path = $_SESSION['user_avatar'] ?? null;
        return is_string($path) && $path !== '' ? $path : null;
    }

    public static function refreshSession(): void
    {
        if (!self::check()) {
            return;
        }
        $user = self::user();
        if (!$user) {
            return;
        }
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_timezone'] = $user['timezone'];
        $_SESSION['user_avatar'] = $user['avatar_path'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login');
        }

        $user = self::user();
        if (!$user || (int) $user['is_active'] !== 1) {
            self::logout();
            flash('error', 'تم تعطيل حسابك. تواصل مع الإدارة.');
            redirect('/login');
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            flash('error', 'ليس لديك صلاحية للوصول إلى هذه الصفحة.');
            redirect(RoleHelper::dashboardPath(self::role()));
        }
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([self::id()]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}
