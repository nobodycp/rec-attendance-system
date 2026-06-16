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

require dirname(__DIR__) . '/src/helpers.php';
require dirname(__DIR__) . '/src/Database.php';
require dirname(__DIR__) . '/src/Csrf.php';
require dirname(__DIR__) . '/src/Auth.php';
require dirname(__DIR__) . '/src/TimezoneHelper.php';
require dirname(__DIR__) . '/src/AttendanceService.php';
require dirname(__DIR__) . '/src/TaskService.php';
require dirname(__DIR__) . '/src/ReportService.php';
require dirname(__DIR__) . '/src/UserService.php';
require dirname(__DIR__) . '/src/AccountService.php';
require dirname(__DIR__) . '/src/DbDiagnostics.php';

$route = $_GET['route'] ?? '/';
$route = '/' . trim($route, '/');
if ($route === '//') {
    $route = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    match (true) {
        $route === '/' && $method === 'GET' => (function () {
            if (Auth::check()) {
                redirect(RoleHelper::dashboardPath(Auth::role()));
            }
            redirect('/login');
        })(),

        $route === '/login' && $method === 'GET' => (function () {
            if (Auth::check()) {
                redirect(RoleHelper::dashboardPath(Auth::role()));
            }
            view('login', ['title' => 'تسجيل الدخول']);
        })(),

        $route === '/login' && $method === 'POST' => (function () {
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج. أعد المحاولة.');
                redirect('/login');
            }
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (Auth::attempt($email, $password)) {
                redirect(RoleHelper::dashboardPath(Auth::role()));
            }
            flash('error', 'البريد أو كلمة المرور غير صحيحة.');
            redirect('/login');
        })(),

        $route === '/logout' => (function () use ($method) {
            if ($method === 'POST' && !Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect(RoleHelper::dashboardPath(Auth::role()));
            }
            Auth::logout();
            redirect('/login');
        })(),

        $route === '/account/settings' && $method === 'GET' => (function () {
            Auth::requireLogin();
            view('account/settings', ['title' => 'إعدادات الحساب']);
        })(),

        $route === '/account/password' && $method === 'POST' => (function () {
            Auth::requireLogin();
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/account/settings');
            }
            try {
                AccountService::changePassword(
                    Auth::id(),
                    $_POST['current_password'] ?? '',
                    $_POST['new_password'] ?? '',
                    $_POST['confirm_password'] ?? ''
                );
                flash('success', 'تم تحديث كلمة المرور بنجاح.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/account/settings');
        })(),

        $route === '/account/avatar' && $method === 'POST' => (function () {
            Auth::requireLogin();
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/account/settings');
            }
            try {
                AccountService::uploadAvatar(Auth::id(), $_FILES['avatar'] ?? []);
                Auth::refreshSession();
                flash('success', 'تم تحديث صورة الملف الشخصي.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/account/settings');
        })(),

        $route === '/health' && $method === 'GET' => (function () {
            header('Content-Type: application/json');
            $payload = ['status' => 'ok', 'db' => 'unknown'];

            try {
                Database::getConnection()->query('SELECT 1');
                $payload['db'] = 'connected';
            } catch (Throwable $e) {
                $payload['db'] = 'failed';
                if (config('app.debug')) {
                    $payload['db_error'] = $e->getMessage();
                }
            }

            echo json_encode($payload);
        })(),

        $route === '/debug/db' && $method === 'GET' => (function () {
            if (!config('app.debug')) {
                http_response_code(404);
                echo 'Not found';
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(testDatabaseConnection(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        })(),

        $route === '/employee/dashboard' && $method === 'GET' => (function () {
            Auth::requireRole(['employee']);
            $tz = Auth::timezone();
            $status = AttendanceService::todayStatus(Auth::id(), $tz);
            $tasks = TaskService::forEmployee(Auth::id(), date('Y-m-d', strtotime('-7 days')), date('Y-m-d', strtotime('+7 days')));
            $recent = AttendanceService::recent(Auth::id(), 7);
            view('employee/dashboard', array_merge(compact('status', 'tasks', 'recent', 'tz'), ['loadSignature' => true]));
        })(),

        $route === '/employee/attendance' && $method === 'POST' => (function () {
            Auth::requireRole(['employee']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/employee/dashboard');
            }
            try {
                AttendanceService::sign(
                    Auth::id(),
                    $_POST['type'] ?? '',
                    $_POST['signature_data'] ?? '',
                    Auth::timezone(),
                    clientIp()
                );
                flash('success', $_POST['type'] === 'check_in' ? 'تم تسجيل الحضور بنجاح.' : 'تم تسجيل الانصراف بنجاح.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/employee/dashboard');
        })(),

        $route === '/employee/task/complete' && $method === 'POST' => (function () {
            Auth::requireRole(['employee', 'manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/employee/dashboard');
            }
            $taskId = (int) ($_POST['task_id'] ?? 0);
            if (!TaskService::canAccess($taskId, Auth::id(), Auth::role())) {
                flash('error', 'لا يمكنك إتمام هذه المهمة.');
                redirect(Auth::role() === 'employee' ? '/employee/dashboard' : '/manager/tasks');
            }
            try {
                $tz = Auth::timezone();
                $localDt = str_replace('T', ' ', $_POST['completed_at'] ?? '');
                TaskService::complete($taskId, Auth::id(), $localDt, $tz, trim($_POST['notes'] ?? '') ?: null);
                flash('success', 'تم تسجيل إتمام المهمة.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect(Auth::role() === 'employee' ? '/employee/dashboard' : '/manager/tasks');
        })(),

        $route === '/employee/report' && $method === 'GET' => (function () {
            Auth::requireRole(['employee']);
            $year = (int) ($_GET['year'] ?? date('Y'));
            $month = (int) ($_GET['month'] ?? date('n'));
            $report = ReportService::fullReport(Auth::id(), $year, $month);
            view('employee/monthly_report', compact('report', 'year', 'month'));
        })(),

        $route === '/manager/dashboard' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $team = TaskService::teamEmployees(Auth::id());
            if (Auth::role() === 'admin') {
                $pdo = Database::getConnection();
                $team = $pdo->query('SELECT id, name, email, timezone FROM users WHERE role="employee" AND is_active=1 ORDER BY name')->fetchAll();
            }
            $today = TimezoneHelper::localWorkDate(TimezoneHelper::utcNow(), Auth::timezone());
            $attendance = Auth::role() === 'admin' ? [] : AttendanceService::teamAttendance(Auth::id(), $today);
            view('manager/dashboard', compact('team', 'attendance', 'today'));
        })(),

        $route === '/manager/tasks' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
            $to = $_GET['to'] ?? date('Y-m-d', strtotime('+7 days'));
            $tasks = Auth::role() === 'admin'
                ? Database::getConnection()->query(
                    'SELECT t.*, e.name AS employee_name, tc.completed_at_utc, te.score
                     FROM daily_tasks t JOIN users e ON e.id=t.employee_id
                     LEFT JOIN task_completions tc ON tc.task_id=t.id
                     LEFT JOIN task_evaluations te ON te.task_id=t.id
                     ORDER BY t.task_date DESC'
                )->fetchAll()
                : TaskService::forManager(Auth::id(), $from, $to);
            $employees = Auth::role() === 'admin'
                ? Database::getConnection()->query('SELECT id, name FROM users WHERE role="employee" AND is_active=1 ORDER BY name')->fetchAll()
                : TaskService::teamEmployees(Auth::id());
            view('manager/tasks', compact('tasks', 'employees', 'from', 'to'));
        })(),

        $route === '/manager/tasks/create' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/tasks');
            }
            try {
                TaskService::create(
                    (int) $_POST['employee_id'],
                    Auth::id(),
                    trim($_POST['title'] ?? ''),
                    trim($_POST['description'] ?? '') ?: null,
                    $_POST['task_date'] ?? date('Y-m-d')
                );
                flash('success', 'تمت إضافة المهمة.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/tasks');
        })(),

        $route === '/manager/attendance' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $date = $_GET['date'] ?? TimezoneHelper::localWorkDate(TimezoneHelper::utcNow(), Auth::timezone());
            $attendance = Auth::role() === 'admin' ? [] : AttendanceService::teamAttendance(Auth::id(), $date);
            view('manager/attendance', compact('attendance', 'date'));
        })(),

        $route === '/manager/evaluate' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $taskId = (int) ($_GET['id'] ?? 0);
            if (!TaskService::canAccess($taskId, Auth::id(), Auth::role())) {
                flash('error', 'المهمة غير موجودة.');
                redirect('/manager/tasks');
            }
            $task = TaskService::getById($taskId);
            view('manager/evaluate', compact('task'));
        })(),

        $route === '/manager/evaluate' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/tasks');
            }
            $taskId = (int) ($_POST['task_id'] ?? 0);
            if (!TaskService::canAccess($taskId, Auth::id(), Auth::role())) {
                flash('error', 'لا يمكنك تقييم هذه المهمة.');
                redirect('/manager/tasks');
            }
            try {
                TaskService::evaluate($taskId, Auth::id(), (int) $_POST['score'], trim($_POST['notes'] ?? '') ?: null);
                flash('success', 'تم حفظ التقييم.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/tasks');
        })(),

        $route === '/manager/reports' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $year = (int) ($_GET['year'] ?? date('Y'));
            $month = (int) ($_GET['month'] ?? date('n'));
            $employees = Auth::role() === 'admin'
                ? Database::getConnection()->query('SELECT id, name FROM users WHERE role="employee" AND is_active=1 ORDER BY name')->fetchAll()
                : TaskService::teamEmployees(Auth::id());
            $employeeId = (int) ($_GET['employee_id'] ?? ($employees[0]['id'] ?? 0));
            $report = $employeeId ? ReportService::fullReport($employeeId, $year, $month) : null;
            view('manager/reports', compact('employees', 'report', 'year', 'month', 'employeeId'));
        })(),

        $route === '/manager/users' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $isAdmin = Auth::role() === 'admin';
            $users = $isAdmin ? UserService::listForAdmin() : UserService::listForManager(Auth::id());
            $supervisors = UserService::supervisors();
            $availableRoles = $isAdmin
                ? RoleHelper::all()
                : ['employee' => RoleHelper::label('employee')];
            view('manager/users', compact('users', 'supervisors', 'isAdmin', 'availableRoles'));
        })(),

        $route === '/manager/users/create' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/users');
            }
            try {
                $role = $_POST['role'] ?? 'employee';
                if (Auth::role() !== 'admin') {
                    $role = 'employee';
                }
                if (!RoleHelper::isValid($role)) {
                    throw new InvalidArgumentException('الدور غير صالح.');
                }
                $managerId = $role === 'employee'
                    ? (in_array(Auth::role(), ['manager', 'dept_manager'], true)
                        ? Auth::id()
                        : (int) ($_POST['manager_id'] ?? 0))
                    : null;
                UserService::create(
                    $_POST['name'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $role,
                    $_POST['timezone'] ?? config('app.default_timezone'),
                    $managerId ?: null
                );
                flash('success', 'تمت إضافة المستخدم بنجاح.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/users');
        })(),

        $route === '/manager/users/toggle' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/users');
            }
            try {
                UserService::toggleActive((int) ($_POST['user_id'] ?? 0), Auth::id(), Auth::role());
                flash('success', 'تم تحديث حالة المستخدم.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/users');
        })(),

        $route === '/manager/users/delete' && $method === 'POST' => (function () {
            Auth::requireRole(['admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/users');
            }
            try {
                UserService::delete((int) ($_POST['user_id'] ?? 0), Auth::id(), Auth::role());
                flash('success', 'تم حذف المستخدم وجميع سجلاته المرتبطة.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/users');
        })(),

        default => (function () use ($route) {
            http_response_code(404);
            echo '<h1>404 - الصفحة غير موجودة</h1><p><a href="' . e(url('/')) . '">العودة</a></p>';
        })(),
    };
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
