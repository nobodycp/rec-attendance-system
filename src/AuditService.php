<?php

declare(strict_types=1);

class AuditService
{
    public static function log(string $action, ?int $targetUserId = null, ?string $details = null): void
    {
        if (!Auth::check()) {
            return;
        }

        $pdo = Database::getConnection();
        $pdo->prepare(
            'INSERT INTO audit_logs (actor_id, action, target_user_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            Auth::id(),
            $action,
            $targetUserId,
            $details,
            clientIp(),
        ]);
    }

    public static function recent(int $limit = 100): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT a.*, u.name AS actor_name, t.name AS target_name
             FROM audit_logs a
             JOIN users u ON u.id = a.actor_id
             LEFT JOIN users t ON t.id = a.target_user_id
             ORDER BY a.created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
