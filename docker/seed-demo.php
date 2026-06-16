<?php

declare(strict_types=1);

/**
 * إنشاء/تحديث حساب ديمو محلي.
 * الاستخدام: php docker/seed-demo.php
 */

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/src/Database.php';

$email = getenv('DEMO_ADMIN_EMAIL') ?: 'admin@test.test';
$password = getenv('DEMO_ADMIN_PASSWORD') ?: '123123123';
$name = getenv('DEMO_ADMIN_NAME') ?: 'مسؤول ديمو';

$pdo = Database::getConnection();
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare(
        'UPDATE users SET name = ?, password_hash = ?, role = ?, is_active = 1 WHERE id = ?'
    )->execute([$name, $hash, 'admin', (int) $existing['id']]);
    echo "تم تحديث حساب الديمو: {$email}\n";
    exit(0);
}

$pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, timezone) VALUES (?, ?, ?, ?, ?)'
)->execute([$name, $email, $hash, 'admin', getenv('APP_TIMEZONE') ?: 'Asia/Riyadh']);

echo "تم إنشاء حساب الديمو: {$email}\n";
