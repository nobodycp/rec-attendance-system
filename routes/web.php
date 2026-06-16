<?php

declare(strict_types=1);

return static function (string $route, string $method): void {
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
            if (!flash('error')) {
                flash('error', 'البريد أو كلمة المرور غير صحيحة.');
            }
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
            view('account/settings', [
                'title' => 'إعدادات الحساب',
                'breadcrumbs' => [['label' => 'إعدادات الحساب']],
            ]);
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

        $route === '/account/email' && $method === 'POST' => (function () {
            Auth::requireLogin();
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/account/settings');
            }
            try {
                AccountService::updateEmail(Auth::id(), $_POST['email'] ?? '');
                Auth::refreshSession();
                flash('success', 'تم تحديث البريد الإلكتروني.');
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
            Auth::requireRole(['employee', 'manager', 'dept_manager', 'admin']);
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
            $today = TimezoneHelper::localWorkDate(TimezoneHelper::utcNow(), Auth::timezone());
            $team = Auth::role() === 'admin'
                ? UserService::activeEmployees()
                : TaskService::teamEmployees(Auth::id());
            $attendance = Auth::role() === 'admin'
                ? AttendanceService::orgAttendance($today)
                : AttendanceService::teamAttendance(Auth::id(), $today);
            $stats = Auth::role() === 'admin' ? DashboardService::adminStats($today) : null;
            view('manager/dashboard', compact('team', 'attendance', 'today', 'stats'));
        })(),

        $route === '/manager/tasks' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
            $to = $_GET['to'] ?? date('Y-m-d', strtotime('+7 days'));
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $allTasks = Auth::role() === 'admin'
                ? TaskService::forAdmin($from, $to)
                : TaskService::forManager(Auth::id(), $from, $to);
            $pagination = paginate($allTasks, $page, 20);
            $tasks = $pagination['items'];
            $employees = Auth::role() === 'admin'
                ? UserService::activeEmployees()
                : TaskService::teamEmployees(Auth::id());
            view('manager/tasks', compact('tasks', 'employees', 'from', 'to', 'pagination'));
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
                    $_POST['task_date'] ?? date('Y-m-d'),
                    Auth::role()
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
            $managerFilter = Auth::role() === 'admin' ? (int) ($_GET['manager_id'] ?? 0) : Auth::id();
            $attendance = Auth::role() === 'admin'
                ? AttendanceService::orgAttendance($date, $managerFilter > 0 ? $managerFilter : null)
                : AttendanceService::teamAttendance(Auth::id(), $date);
            $supervisors = Auth::role() === 'admin' ? UserService::supervisors() : [];
            $employees = UserService::activeEmployees(Auth::role() === 'admin' ? null : Auth::id());
            view('manager/attendance', compact('attendance', 'date', 'supervisors', 'managerFilter', 'employees'));
        })(),

        $route === '/manager/attendance/correct' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/attendance');
            }
            try {
                AttendanceService::manualRecord(
                    (int) ($_POST['employee_id'] ?? 0),
                    $_POST['type'] ?? '',
                    $_POST['date'] ?? '',
                    $_POST['reason'] ?? '',
                    Auth::id(),
                    Auth::timezone()
                );
                flash('success', 'تم تسجيل التصحيح بنجاح.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/attendance?date=' . urlencode($_POST['date'] ?? date('Y-m-d')));
        })(),

        $route === '/manager/evaluate' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $taskId = (int) ($_GET['id'] ?? 0);
            if (!TaskService::canAccess($taskId, Auth::id(), Auth::role())) {
                flash('error', 'المهمة غير موجودة.');
                redirect('/manager/tasks');
            }
            $task = TaskService::getById($taskId);
            view('manager/evaluate', [
                'title' => 'تقييم المهمة',
                'task' => $task,
                'breadcrumbs' => [
                    ['label' => 'المهام', 'url' => '/manager/tasks'],
                    ['label' => 'تقييم المهمة'],
                ],
            ]);
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

        $route === '/manager/reports' && $method === 'GET' => (function () use ($route, $method) {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $year = (int) ($_GET['year'] ?? date('Y'));
            $month = (int) ($_GET['month'] ?? date('n'));
            $employees = Auth::role() === 'admin'
                ? UserService::activeEmployees()
                : TaskService::teamEmployees(Auth::id());
            $employeeId = (int) ($_GET['employee_id'] ?? ($employees[0]['id'] ?? 0));
            if ($employeeId && !AuthorizationService::canAccessEmployee(Auth::id(), Auth::role(), $employeeId)) {
                flash('error', 'لا يمكنك عرض تقرير هذا الموظف.');
                redirect('/manager/reports');
            }
            $report = $employeeId ? ReportService::fullReport($employeeId, $year, $month) : null;

            if (isset($_GET['export']) && $_GET['export'] === 'csv' && $report) {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="report-' . $employeeId . '-' . $year . '-' . $month . '.csv"');
                echo ExportService::reportCsv($report);
                return;
            }

            view('manager/reports', compact('employees', 'report', 'year', 'month', 'employeeId'));
        })(),

        $route === '/manager/users' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $isAdmin = Auth::role() === 'admin';
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $allUsers = $isAdmin ? UserService::listForAdmin() : UserService::listForManager(Auth::id());
            $pagination = paginate($allUsers, $page, 20);
            $users = $pagination['items'];
            $supervisors = UserService::supervisors();
            $availableRoles = $isAdmin ? RoleHelper::all() : ['employee' => RoleHelper::label('employee')];
            view('manager/users', compact('users', 'supervisors', 'isAdmin', 'availableRoles', 'pagination'));
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

        $route === '/manager/users/edit' && $method === 'GET' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            $userId = (int) ($_GET['id'] ?? 0);
            $user = UserService::find($userId);
            if (!$user) {
                flash('error', 'المستخدم غير موجود.');
                redirect('/manager/users');
            }
            AuthorizationService::requireUserManagement($user);
            $supervisors = UserService::supervisors();
            $isAdmin = Auth::role() === 'admin';
            $availableRoles = $isAdmin ? RoleHelper::all() : ['employee' => RoleHelper::label('employee')];
            view('manager/user_edit', [
                'title' => 'تعديل المستخدم',
                'breadcrumbs' => [
                    ['label' => 'الموظفون', 'url' => '/manager/users'],
                    ['label' => 'تعديل: ' . $user['name']],
                ],
                'user' => $user,
                'supervisors' => $supervisors,
                'isAdmin' => $isAdmin,
                'availableRoles' => $availableRoles,
            ]);
        })(),

        $route === '/manager/users/update' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/users');
            }
            $userId = (int) ($_POST['user_id'] ?? 0);
            try {
                UserService::update(
                    $userId,
                    $_POST['name'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['role'] ?? 'employee',
                    $_POST['timezone'] ?? config('app.default_timezone'),
                    (int) ($_POST['manager_id'] ?? 0) ?: null,
                    Auth::id(),
                    Auth::role()
                );
                flash('success', 'تم تحديث المستخدم.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/users/edit?id=' . $userId);
        })(),

        $route === '/manager/users/reset-password' && $method === 'POST' => (function () {
            Auth::requireRole(['manager', 'dept_manager', 'admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/users');
            }
            $userId = (int) ($_POST['user_id'] ?? 0);
            try {
                UserService::resetPassword($userId, $_POST['password'] ?? '', Auth::id(), Auth::role());
                flash('success', 'تم إعادة تعيين كلمة المرور.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/users/edit?id=' . $userId);
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

        $route === '/manager/holidays' && $method === 'GET' => (function () {
            Auth::requireRole(['admin']);
            $holidays = HolidayService::listAll();
            view('manager/holidays', [
                'title' => 'العطل الرسمية',
                'breadcrumbs' => [['label' => 'العطل الرسمية']],
                'holidays' => $holidays,
            ]);
        })(),

        $route === '/manager/holidays/create' && $method === 'POST' => (function () {
            Auth::requireRole(['admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/holidays');
            }
            try {
                HolidayService::create($_POST['holiday_date'] ?? '', $_POST['name'] ?? '');
                AuditService::log('holiday.create', null, $_POST['name'] ?? '');
                flash('success', 'تمت إضافة العطلة.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/holidays');
        })(),

        $route === '/manager/holidays/delete' && $method === 'POST' => (function () {
            Auth::requireRole(['admin']);
            if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'انتهت صلاحية النموذج.');
                redirect('/manager/holidays');
            }
            try {
                HolidayService::delete((int) ($_POST['holiday_id'] ?? 0));
                AuditService::log('holiday.delete');
                flash('success', 'تم حذف العطلة.');
            } catch (Throwable $e) {
                flash('error', $e->getMessage());
            }
            redirect('/manager/holidays');
        })(),

        $route === '/manager/audit' && $method === 'GET' => (function () {
            Auth::requireRole(['admin']);
            $logs = AuditService::recent(200);
            view('manager/audit', [
                'title' => 'سجل التدقيق',
                'breadcrumbs' => [['label' => 'سجل التدقيق']],
                'logs' => $logs,
            ]);
        })(),

        default => (function () use ($route) {
            http_response_code(404);
            echo '<h1>404 - الصفحة غير موجودة</h1><p><a href="' . e(url('/')) . '">العودة</a></p>';
        })(),
    };
};
