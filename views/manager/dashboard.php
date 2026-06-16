<?php $title = 'لوحة التحكم'; ?>
<div class="page-header">
    <h1><?= Auth::role() === 'admin' ? 'لوحة مسؤول النظام' : 'لوحة المشرف' ?></h1>
    <p class="page-header__subtitle">تاريخ اليوم: <?= e($today) ?><?= ($stats && !$stats['is_workday']) ? ' — يوم عطلة' : '' ?></p>
</div>

<?php if ($stats): ?>
<div class="stats">
    <div class="stat-box">
        <div class="label">الموظفون النشطون</div>
        <div class="value"><?= (int) $stats['total_employees'] ?></div>
    </div>
    <div class="stat-box">
        <div class="label">حاضر اليوم</div>
        <div class="value"><?= (int) $stats['present_today'] ?></div>
    </div>
    <div class="stat-box">
        <div class="label">غائب / لم يسجّل</div>
        <div class="value"><?= (int) $stats['absent_today'] ?></div>
    </div>
    <div class="stat-box">
        <div class="label">مهام معلّقة</div>
        <div class="value"><?= (int) $stats['pending_tasks'] ?></div>
    </div>
    <div class="stat-box">
        <div class="label">بانتظار التقييم</div>
        <div class="value"><?= (int) $stats['awaiting_evaluation'] ?></div>
    </div>
</div>
<?php else: ?>
<div class="stats">
    <div class="stat-box">
        <div class="label">عدد الموظفين</div>
        <div class="value"><?= count($team) ?></div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2>حضور الفريق اليوم</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>الموظف</th>
                    <?php if (Auth::role() === 'admin'): ?><th>المشرف</th><?php endif; ?>
                    <th>حضور</th>
                    <th>انصراف</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="<?= Auth::role() === 'admin' ? 4 : 3 ?>" class="table-empty">لا يوجد موظفون أو لا توجد بيانات</td></tr>
            <?php else: foreach ($attendance as $a): ?>
            <tr>
                <td><strong><?= e($a['name']) ?></strong></td>
                <?php if (Auth::role() === 'admin'): ?><td><?= e($a['manager_name'] ?? '—') ?></td><?php endif; ?>
                <td>
                    <?= $a['check_in_utc'] ? e(TimezoneHelper::formatArabic($a['check_in_utc'], $a['timezone'])) : '—' ?>
                    <?php if (!empty($a['check_in_manual'])): ?><span class="badge badge-pending">يدوي</span><?php endif; ?>
                </td>
                <td>
                    <?= $a['check_out_utc'] ? e(TimezoneHelper::formatArabic($a['check_out_utc'], $a['timezone'])) : '—' ?>
                    <?php if (!empty($a['check_out_manual'])): ?><span class="badge badge-pending">يدوي</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="grid-2">
    <a href="<?= e(url('/manager/users')) ?>" class="card link-card">
        <h3>إدارة الموظفين</h3>
        <p class="text-muted">إضافة وتعديل المستخدمين</p>
    </a>
    <a href="<?= e(url('/manager/tasks')) ?>" class="card link-card">
        <h3>إدارة المهام</h3>
        <p class="text-muted">إضافة مهام يومية ومتابعة إتمامها</p>
    </a>
    <a href="<?= e(url('/manager/reports')) ?>" class="card link-card">
        <h3>التقارير الشهرية</h3>
        <p class="text-muted">درجة الحضور والأداء لكل موظف</p>
    </a>
    <a href="<?= e(url('/manager/attendance')) ?>" class="card link-card">
        <h3>حضور الفريق</h3>
        <p class="text-muted">متابعة وتصحيح الحضور</p>
    </a>
</div>
