<?php
$route = $currentRoute ?? currentRoute();
$isEmployee = RoleHelper::isEmployee(Auth::role());

$navActive = static function (string ...$paths) use ($route): string {
    foreach ($paths as $path) {
        if ($route === $path) {
            return ' is-active';
        }
    }
    return '';
};
?>
<nav class="rd-sidebar-nav" aria-label="القائمة الرئيسية">
    <?php if ($isEmployee): ?>
        <div class="rd-nav-section">الموظف</div>
        <a class="rd-nav-link<?= $navActive('/employee/dashboard') ?>" href="<?= e(url('/employee/dashboard')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg></span>
            <span>لوحتي</span>
        </a>
        <a class="rd-nav-link<?= $navActive('/employee/report') ?>" href="<?= e(url('/employee/report')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg></span>
            <span>تقريري الشهري</span>
        </a>
    <?php else: ?>
        <div class="rd-nav-section">الإدارة</div>
        <a class="rd-nav-link<?= $navActive('/manager/dashboard') ?>" href="<?= e(url('/manager/dashboard')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg></span>
            <span>لوحة التحكم</span>
        </a>
        <a class="rd-nav-link<?= $navActive('/manager/users') ?>" href="<?= e(url('/manager/users')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            <span>الموظفون</span>
        </a>
        <a class="rd-nav-link<?= $navActive('/manager/tasks', '/manager/evaluate') ?>" href="<?= e(url('/manager/tasks')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg></span>
            <span>المهام</span>
        </a>
        <a class="rd-nav-link<?= $navActive('/manager/attendance') ?>" href="<?= e(url('/manager/attendance')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
            <span>الحضور</span>
        </a>
        <a class="rd-nav-link<?= $navActive('/manager/reports') ?>" href="<?= e(url('/manager/reports')) ?>">
            <span class="rd-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg></span>
            <span>التقارير</span>
        </a>
    <?php endif; ?>
</nav>
