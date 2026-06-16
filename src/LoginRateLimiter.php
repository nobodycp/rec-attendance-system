<?php

declare(strict_types=1);

class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public static function tooManyAttempts(string $email): bool
    {
        $pdo = Database::getConnection();
        $since = (new DateTimeImmutable('-' . self::WINDOW_MINUTES . ' minutes'))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = ? AND attempted_at >= ?'
        );
        $stmt->execute([trim(strtolower($email)), $since]);

        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public static function recordFailure(string $email): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address, attempted_at) VALUES (?, ?, ?)'
        )->execute([
            trim(strtolower($email)),
            clientIp(),
            (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public static function clear(string $email): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([trim(strtolower($email))]);
    }
}
