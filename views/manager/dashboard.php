<?php $title = 'لوحة المشرف'; ?>
<h1>لوحة المشرف</h1>
<p class="text-muted">تاريخ اليوم: <?= e($today) ?></p>

<div class="stats">
    <div class="stat-box">
        <div class="value"><?= count($team) ?></div>
        <div class="label">عدد الموظفين</div>
    </div>
</div>

<div class="card">
    <h2>حضور الفريق اليوم</h2>
    <?php if (Auth::role() === 'admin'): ?>
        <p class="text-muted">كمدير نظام، استخدم صفحة الحضور أو التقارير لعرض التفاصيل.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>الموظف</th><th>حضور</th><th>انصراف</th></tr></thead>
        <tbody>
        <?php if (empty($attendance)): ?>
            <tr><td colspan="3">لا يوجد موظفون في فريقك</td></tr>
        <?php else: foreach ($attendance as $a): ?>
        <tr>
            <td><?= e($a['name']) ?></td>
            <td><?= $a['check_in_utc'] ? e(TimezoneHelper::formatArabic($a['check_in_utc'], $a['timezone'])) : '—' ?></td>
            <td><?= $a['check_out_utc'] ? e(TimezoneHelper::formatArabic($a['check_out_utc'], $a['timezone'])) : '—' ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<div class="grid-2">
    <a href="<?= e(url('/manager/users')) ?>" class="card" style="text-decoration:none;color:inherit">
        <h3>إدارة الموظفين</h3>
        <p class="text-muted">إضافة موظفين جدد وتعيين المشرف</p>
    </a>
    <a href="<?= e(url('/manager/tasks')) ?>" class="card" style="text-decoration:none;color:inherit">
        <h3>إدارة المهام</h3>
        <p class="text-muted">إضافة مهام يومية ومتابعة إتمامها</p>
    </a>
    <a href="<?= e(url('/manager/reports')) ?>" class="card" style="text-decoration:none;color:inherit">
        <h3>التقارير الشهرية</h3>
        <p class="text-muted">درجة الحضور والأداء لكل موظف</p>
    </a>
</div>
