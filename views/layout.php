<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('/assets/css/shell.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
</head>
<body class="<?= ($page ?? '') === 'login' ? 'page-login' : (Auth::check() ? 'app-shell' : '') ?>">
<?php if (Auth::check() && ($page ?? '') !== 'login'): ?>
<div class="rd-app" data-rd-nav>
    <div class="rd-sidebar-backdrop" data-rd-nav-backdrop aria-hidden="true"></div>

    <aside class="rd-sidebar" data-rd-nav-panel id="rd-sidebar">
        <a class="rd-sidebar-brand" href="<?= e(url(RoleHelper::dashboardPath(Auth::role()))) ?>">
            <span class="rd-sidebar-brand-mark">REC</span>
            <span>
                <div class="rd-sidebar-brand-title"><?= e(config('app.name')) ?></div>
                <div class="rd-sidebar-brand-tagline">نظام الحضور والمهام</div>
            </span>
        </a>

        <?php require __DIR__ . '/partials/sidebar_nav.php'; ?>
    </aside>

    <div class="rd-main">
        <header class="rd-topbar no-print">
            <div class="rd-topbar-start">
                <button
                    type="button"
                    class="rd-menu-btn"
                    data-rd-nav-toggle
                    aria-controls="rd-sidebar"
                    aria-expanded="false"
                    aria-label="فتح القائمة"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <div class="rd-topbar-meta">
                <div class="rd-user-chip">
                    <span class="rd-avatar" aria-hidden="true"><?= e(userInitials(Auth::name())) ?></span>
                    <span>
                        <div class="rd-user-name"><?= e(Auth::name()) ?></div>
                        <div class="rd-user-role"><?= e(roleLabel(Auth::role())) ?></div>
                    </span>
                </div>
                <a class="rd-logout-btn" href="<?= e(url('/logout')) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    خروج
                </a>
            </div>
        </header>

        <main class="rd-content">
            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error = flash('error')): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <?php require __DIR__ . '/' . ($name ?? 'login') . '.php'; ?>
        </main>
    </div>
</div>
<script src="<?= e(url('/assets/js/nav-toggle.js')) ?>"></script>
<script src="<?= e(url('/assets/js/ui.js')) ?>"></script>
<?php elseif (($page ?? '') === 'login'): ?>
    <?php require __DIR__ . '/' . ($name ?? 'login') . '.php'; ?>
<?php else: ?>
<div class="container">
    <?php if ($success = flash('success')): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/' . ($name ?? 'login') . '.php'; ?>
</div>
<?php endif; ?>

<?php if (!empty($loadSignature)): ?>
<script src="<?= e(url('/assets/js/signature.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
