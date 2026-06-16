<?php

declare(strict_types=1);

$config = require dirname(__DIR__) . '/config/config.php';

if ($config['app']['debug'] ?? false) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

if (!($config['app']['debug'] ?? false)) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($isSecure) {
        ini_set('session.cookie_secure', '1');
    }
}

session_start();

date_default_timezone_set($config['app']['default_timezone']);

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/helpers.php';
require dirname(__DIR__) . '/src/Database.php';

$route = $_GET['route'] ?? '/';
$route = '/' . trim($route, '/');
if ($route === '//') {
    $route = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $dispatch = require dirname(__DIR__) . '/routes/web.php';
    $dispatch($route, $method);
} catch (Throwable $e) {
    if (config('app.debug')) {
        http_response_code(500);
        echo '<h1>خطأ</h1><pre>' . e($e->getMessage()) . '</pre>';
        echo '<pre>' . e($e->getTraceAsString()) . '</pre>';
        throw $e;
    }
    http_response_code(500);
    echo '<h1>خطأ في الخادم</h1>';
}
