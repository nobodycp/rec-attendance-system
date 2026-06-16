<?php

declare(strict_types=1);

/**
 * إنشاء مسؤول النظام من سطر الأوامر (اختياري).
 * يتطلب تعيين SEED_ADMIN_EMAIL و SEED_ADMIN_PASSWORD و SEED_ADMIN_NAME.
 */

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/src/Database.php';

if ((getenv('DB_DRIVER') ?: 'mysql') === 'sqlite') {
    echo "استخدم /setup.php للتطوير المحلي.\n";
    exit(1);
}

$email = getenv('SEED_ADMIN_EMAIL');
$password = getenv('SEED_ADMIN_PASSWORD');
$name = getenv('SEED_ADMIN_NAME') ?: 'مسؤول النظام';

if (!$email || !$password) {
    echo "عيّن SEED_ADMIN_EMAIL و SEED_ADMIN_PASSWORD قبل التشغيل.\n";
    exit(1);
}

if (strlen($password) < 8) {
    echo "كلمة المرور يجب أن تكون 8 أحرف على الأقل.\n";
    exit(1);
}

$pdo = Database::getConnection();
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($count > 0) {
    echo "يوجد {$count} مستخدم مسبقاً. لا حاجة للتثبيت.\n";
    exit(0);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, timezone) VALUES (?, ?, ?, ?, ?)'
)->execute([
    $name,
    strtolower(trim($email)),
    $hash,
    'admin',
    getenv('APP_TIMEZONE') ?: 'Asia/Riyadh',
]);

echo "تم إنشاء مسؤول النظام: {$email}\n";
