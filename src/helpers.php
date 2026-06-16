<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/RoleHelper.php';

function config(string $key, mixed $default = null): mixed
{
    if (!function_exists('app_config')) {
        require_once dirname(__DIR__) . '/config/config.php';
    }
    $cfg = app_config();
    $parts = explode('.', $key);
    $value = $cfg;
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function base_path(): string
{
    return rtrim((string) config('app.base_path', ''), '/');
}

function url(string $path = ''): string
{
    $base = base_path();
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '' : $path);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function view(string $name, array $data = []): void
{
    $data['name'] = $name;
    if (!isset($data['page'])) {
        $data['page'] = basename(str_replace('\\', '/', $name));
    }
    $data['currentRoute'] = currentRoute();
    extract($data);
    require dirname(__DIR__) . '/views/layout.php';
}

function partial(string $name, array $data = []): void
{
    extract($data);
    require dirname(__DIR__) . '/views/' . $name . '.php';
}

function statusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتملة',
        'evaluated' => 'مُقيَّمة',
        default => $status,
    };
}

function roleLabel(string $role): string
{
    return RoleHelper::label($role);
}

function clientIp(): string
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded !== '') {
        $parts = array_map('trim', explode(',', $forwarded));

        return $parts[0] !== '' ? $parts[0] : '0.0.0.0';
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function currentRoute(): string
{
    $route = $_GET['route'] ?? '/';
    $route = '/' . trim((string) $route, '/');
    return $route === '//' ? '/' : $route;
}

function userInitials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($parts === []) {
        return '?';
    }
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 2));
    }

    return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
}

function userHandle(string $email): string
{
    $email = trim(strtolower($email));
    $local = explode('@', $email)[0] ?? $email;
    return '@' . $local;
}

function avatarUrl(?string $path): ?string
{
    if ($path === null || trim($path) === '') {
        return null;
    }

    return url('/' . ltrim($path, '/'));
}

function passwordMinLength(): int
{
    return UserService::MIN_PASSWORD_LENGTH;
}

function paginate(array $items, int $page, int $perPage = 15): array
{
    $total = count($items);
    $page = max(1, $page);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;

    return [
        'items' => array_slice($items, $offset, $perPage),
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
        'per_page' => $perPage,
    ];
}

function auditActionLabel(string $action): string
{
    return match ($action) {
        'login' => 'تسجيل دخول',
        'logout' => 'تسجيل خروج',
        'user.create' => 'إنشاء مستخدم',
        'user.update' => 'تحديث مستخدم',
        'user.delete' => 'حذف مستخدم',
        'user.activate' => 'تفعيل مستخدم',
        'user.deactivate' => 'تعطيل مستخدم',
        'user.reset_password' => 'إعادة تعيين كلمة مرور',
        'attendance.check_in' => 'تسجيل حضور',
        'attendance.check_out' => 'تسجيل انصراف',
        'attendance.manual_check_in' => 'تصحيح حضور',
        'attendance.manual_check_out' => 'تصحيح انصراف',
        'task.create' => 'إنشاء مهمة',
        'task.complete' => 'إتمام مهمة',
        'task.evaluate' => 'تقييم مهمة',
        'holiday.create' => 'إضافة عطلة',
        'holiday.delete' => 'حذف عطلة',
        default => $action,
    };
}
