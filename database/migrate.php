<?php

declare(strict_types=1);

/**
 * ترحيلات قاعدة البيانات — تُشغَّل تلقائياً عند بدء الحاوية.
 */

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/Database.php';

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
    $stmt->execute([$column]);

    return (bool) $stmt->fetch();
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);

    return (bool) $stmt->fetch();
}

function runMigrations(PDO $pdo): void
{
    if (!columnExists($pdo, 'users', 'avatar_path')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER is_active');
        echo "Migration: added users.avatar_path\n";
    }

    if (!columnExists($pdo, 'attendance_records', 'is_manual')) {
        $pdo->exec('ALTER TABLE attendance_records ADD COLUMN is_manual TINYINT(1) NOT NULL DEFAULT 0 AFTER ip_address');
        echo "Migration: added attendance_records.is_manual\n";
    }
    if (!columnExists($pdo, 'attendance_records', 'corrected_by')) {
        $pdo->exec('ALTER TABLE attendance_records ADD COLUMN corrected_by INT UNSIGNED NULL AFTER is_manual');
        echo "Migration: added attendance_records.corrected_by\n";
    }
    if (!columnExists($pdo, 'attendance_records', 'correction_reason')) {
        $pdo->exec('ALTER TABLE attendance_records ADD COLUMN correction_reason VARCHAR(255) NULL AFTER corrected_by');
        echo "Migration: added attendance_records.correction_reason\n";
    }

    if (!tableExists($pdo, 'holidays')) {
        $pdo->exec(
            'CREATE TABLE holidays (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                holiday_date DATE NOT NULL UNIQUE,
                name VARCHAR(150) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        echo "Migration: created holidays table\n";
    }

    if (!tableExists($pdo, 'audit_logs')) {
        $pdo->exec(
            'CREATE TABLE audit_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                actor_id INT UNSIGNED NOT NULL,
                action VARCHAR(80) NOT NULL,
                target_user_id INT UNSIGNED NULL,
                details TEXT NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_audit_target FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
                KEY idx_audit_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        echo "Migration: created audit_logs table\n";
    }

    if (!tableExists($pdo, 'login_attempts')) {
        $pdo->exec(
            'CREATE TABLE login_attempts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(190) NOT NULL,
                ip_address VARCHAR(45) NULL,
                attempted_at DATETIME NOT NULL,
                KEY idx_login_attempts_email_time (email, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        echo "Migration: created login_attempts table\n";
    }

    HolidayService::seedDefaults();
}

try {
    $pdo = Database::getConnection();
    runMigrations($pdo);
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration skipped: ' . $e->getMessage() . PHP_EOL);
}
