<?php

declare(strict_types=1);

/**
 * ترحيلات قاعدة البيانات — تُشغَّل تلقائياً عند بدء الحاوية.
 */

require dirname(__DIR__) . '/src/Database.php';

function runMigrations(PDO $pdo): void
{
    $column = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_path'")->fetch();
    if (!$column) {
        $pdo->exec('ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL AFTER is_active');
        echo "Migration: added users.avatar_path\n";
    }
}

try {
    $pdo = Database::getConnection();
    runMigrations($pdo);
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration skipped: ' . $e->getMessage() . PHP_EOL);
}
