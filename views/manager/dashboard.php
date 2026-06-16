<?php $title = 'لوحة المشرف'; ?>
<div class="page-header">
    <h1>لوحة المشرف</h1>
    <p class="page-header__subtitle">تاريخ اليوم: <?= e($today) ?></p>
</div>

<div class="stats">
    <div class="stat-box">
        <div class="label">عدد الموظفين</div>
        <div class="value"><?= count($team) ?></div>
    </div>
</div>

<div class="card">
    <h2>حضور الفريق اليوم</h2>
    <?php if (Auth::role() === 'admin'): ?>
        <p class="text-muted" style="padding: 0 1.25rem 1.25rem">كمدير نظام، استخدم صفحة الحضور أو التقارير لعرض التفاصيل.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>الموظف</th><th>حضور</th><th>انصراف</th></tr></thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="3" class="table-empty">لا يوجد موظفون في فريقك</td></tr>
            <?php else: foreach ($attendance as $a): ?>
            <tr>
                <td><strong><?= e($a['name']) ?></strong></td>
                <td><?= $a['check_in_utc'] ? e(TimezoneHelper::formatArabic($a['check_in_utc'], $a['timezone'])) : '—' ?></td>
                <td><?= $a['check_out_utc'] ? e(TimezoneHelper::formatArabic($a['check_out_utc'], $a['timezone'])) : '—' ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="grid-2">
    <a href="<?= e(url('/manager/users')) ?>" class="card link-card">
        <h3>إدارة الموظفين</h3>
        <p class="text-muted">إضافة موظفين جدد وتعيين المشرف</p>
    </a>
    <a href="<?= e(url('/manager/tasks')) ?>" class="card link-card">
        <h3>إدارة المهام</h3>
        <p class="text-muted">إضافة مهام يومية ومتابعة إتمامها</p>
    </a>
    <a href="<?= e(url('/manager/reports')) ?>" class="card link-card">
        <h3>التقارير الشهرية</h3>
        <p class="text-muted">درجة الحضور والأداء لكل موظف</p>
    </a>
</div>
