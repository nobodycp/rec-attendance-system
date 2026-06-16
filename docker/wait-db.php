<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/config.php';
$dbConfig = $config['db'];

try {
    $port = (int) ($dbConfig['port'] ?? 3306);
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $dbConfig['host'],
        $port,
        $dbConfig['name']
    );
    new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    exit(0);
} catch (Throwable) {
    exit(1);
}
