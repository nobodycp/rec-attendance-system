<?php

declare(strict_types=1);

/**
 * الإعدادات تُقرأ من متغيرات البيئة (Docker / Coolify).
 * انسخ .env.example إلى .env للتطوير المحلي.
 */

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function envBool(string $key, bool $default = false): bool
{
    $value = env($key);
    if ($value === null) {
        return $default;
    }
    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
}

$driver = env('DB_DRIVER', 'mysql');

return [
    'db' => [
        'driver' => $driver,
        'sqlite_path' => env('DB_SQLITE_PATH', dirname(__DIR__) . '/database/attendance.sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => (int) env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'rec_attendance'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => env('APP_NAME', 'جمعية مركز الإرشاد التربوي REC'),
        'url' => rtrim(env('APP_URL', 'http://localhost:8080'), '/'),
        'base_path' => env('APP_BASE_PATH', ''),
        'default_timezone' => env('APP_TIMEZONE', 'Asia/Riyadh'),
        'debug' => envBool('APP_DEBUG', false),
        'setup_enabled' => envBool('SETUP_ENABLED', false),
    ],
];
