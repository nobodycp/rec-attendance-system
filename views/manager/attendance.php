<?php $title = 'حضور الفريق'; ?>
<div class="page-header">
    <h1>حضور الفريق</h1>
    <p class="page-header__subtitle">متابعة حضور وانصراف الموظفين وتصحيح السجلات الناقصة.</p>
</div>

<form method="get" class="filter-bar no-print">
    <div class="form-group">
        <label class="form-label">التاريخ</label>
        <input type="date" name="date" class="form-control" value="<?= e($date) ?>">
    </div>
    <?php if (Auth::role() === 'admin'): ?>
    <div class="form-group">
        <label class="form-label">المشرف</label>
        <select name="manager_id" class="form-control">
            <option value="0">جميع الموظفين</option>
            <?php foreach ($supervisors as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= $managerFilter === (int) $s['id'] ? 'selected' : '' ?>>
                <?= e($s['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit" class="btn">عرض</button>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>الموظف</th><?php if (Auth::role() === 'admin'): ?><th>المشرف</th><?php endif; ?><th>حضور</th><th>انصراف</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="<?= Auth::role() === 'admin' ? 5 : 4 ?>" class="table-empty">لا يوجد موظفون أو لا توجد بيانات</td></tr>
            <?php else: foreach ($attendance as $a): ?>
            <?php $complete = $a['check_in_utc'] && $a['check_out_utc']; ?>
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
                <td><?= $complete ? '<span class="badge badge-evaluated">حضور كامل</span>' : '<span class="badge badge-pending">ناقص</span>' ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($employees)): ?>
<div class="card no-print">
    <h2>تصحيح حضور يدوي</h2>
    <p class="text-muted" style="padding: 0 1.25rem">للموظفين الذين نسوا تسجيل الحضور أو الانصراف.</p>
    <form method="post" action="<?= e(url('/manager/attendance/correct')) ?>" class="settings-form" style="padding: 0 1.25rem 1.25rem" data-confirm="تأكيد تسجيل الحضور اليدوي؟">
        <?= Csrf::field() ?>
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-control" required>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?= (int) $emp['id'] ?>"><?= e($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">نوع التسجيل</label>
                <select name="type" class="form-control" required>
                    <option value="check_in">حضور</option>
                    <option value="check_out">انصراف</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">سبب التصحيح</label>
            <input type="text" name="reason" class="form-control" required placeholder="مثال: نسي الموظف تسجيل الحضور">
        </div>
        <button type="submit" class="btn">تسجيل التصحيح</button>
    </form>
</div>
<?php endif; ?>
