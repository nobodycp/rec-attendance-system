<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Throwable $e) {
    fwrite(STDERR, 'Cannot connect to database: ' . $e->getMessage() . PHP_EOL);
    exit(0);
}

try {
    $pdo->query('SELECT 1 FROM users LIMIT 1');
    echo "Database already initialized.\n";
    exit(0);
} catch (Throwable) {
    // Schema not applied yet
}

$schemaPath = dirname(__DIR__) . '/database/schema.sql';
if (!is_file($schemaPath)) {
    fwrite(STDERR, "schema.sql not found.\n");
    exit(0);
}

$sql = file_get_contents($schemaPath);
if ($sql === false) {
    fwrite(STDERR, "Could not read schema.sql.\n");
    exit(0);
}

$sql = preg_replace('/--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }
    try {
        $pdo->exec($statement);
    } catch (Throwable $e) {
        fwrite(STDERR, 'Schema statement skipped: ' . $e->getMessage() . PHP_EOL);
    }
}

echo "Database schema applied successfully.\n";
