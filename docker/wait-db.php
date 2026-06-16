<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/config.php';
$dbConfig = $config['db'];
$debug = (bool) ($config['app']['debug'] ?? false);

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
} catch (Throwable $e) {
    if ($debug) {
        fwrite(STDERR, 'DB connection failed: ' . $e->getMessage() . PHP_EOL);
        fwrite(STDERR, sprintf(
            "Config: host=%s port=%d db=%s user=%s driver=%s\n",
            $dbConfig['host'] ?? '?',
            $dbConfig['port'] ?? 3306,
            $dbConfig['name'] ?? '?',
            $dbConfig['user'] ?? '?',
            $dbConfig['driver'] ?? '?'
        ));
        $source = getenv('DATABASE_URL') ? 'DATABASE_URL'
            : (getenv('MYSQL_URL') ? 'MYSQL_URL'
            : (getenv('DB_URL') ? 'DB_URL' : 'DB_* vars'));
        fwrite(STDERR, "Source: {$source}\n");
    }
    exit(1);
}
