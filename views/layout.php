<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body>
<?php if (Auth::check() && ($page ?? '') !== 'login'): ?>
<nav class="navbar no-print">
    <div><strong><?= e(config('app.name')) ?></strong></div>
    <div>
        <?php if (RoleHelper::isEmployee(Auth::role())): ?>
            <a href="<?= e(url('/employee/dashboard')) ?>">لوحتي</a>
            <a href="<?= e(url('/employee/report')) ?>">تقريري الشهري</a>
        <?php else: ?>
            <a href="<?= e(url('/manager/dashboard')) ?>">لوحة التحكم</a>
            <a href="<?= e(url('/manager/users')) ?>">الموظفون</a>
            <a href="<?= e(url('/manager/tasks')) ?>">المهام</a>
            <a href="<?= e(url('/manager/attendance')) ?>">الحضور</a>
            <a href="<?= e(url('/manager/reports')) ?>">التقارير</a>
        <?php endif; ?>
        <a href="<?= e(url('/logout')) ?>">خروج</a>
    </div>
</nav>
<?php endif; ?>

<div class="<?= ($page ?? '') === 'login' ? '' : 'container' ?>">
    <?php if ($success = flash('success')): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/' . ($name ?? 'login') . '.php'; ?>
</div>

<?php if (!empty($loadSignature)): ?>
<script src="<?= e(url('/assets/js/signature.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
