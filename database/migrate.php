<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/src/Database.php';

$pdo = Database::getConnection();

$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->exec('
    CREATE TABLE IF NOT EXISTS users_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "employee",
        timezone TEXT NOT NULL DEFAULT "Asia/Riyadh",
        manager_id INTEGER NULL,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
    )
');

$hasOld = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
if ($hasOld) {
    $pdo->exec('INSERT INTO users_new SELECT * FROM users');
    $pdo->exec('DROP TABLE users');
}
$pdo->exec('ALTER TABLE users_new RENAME TO users');
$pdo->exec('PRAGMA foreign_keys = ON');

echo "تم تحديث جدول المستخدمين لدعم جميع الأدوار.\n";
