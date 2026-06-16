<?php $title = 'حضور الفريق'; ?>
<div class="page-header">
    <h1>حضور الفريق</h1>
    <p class="page-header__subtitle">متابعة حضور وانصراف الموظفين حسب التاريخ.</p>
</div>

<form method="get" class="filter-bar no-print">
    <div class="form-group">
        <label class="form-label">التاريخ</label>
        <input type="date" name="date" class="form-control" value="<?= e($date) ?>">
    </div>
    <button type="submit" class="btn">عرض</button>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>الموظف</th><th>المنطقة الزمنية</th><th>حضور</th><th>انصراف</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="5" class="table-empty">لا يوجد موظفون أو لا توجد بيانات</td></tr>
            <?php else: foreach ($attendance as $a): ?>
            <?php $complete = $a['check_in_utc'] && $a['check_out_utc']; ?>
            <tr>
                <td><strong><?= e($a['name']) ?></strong></td>
                <td><?= e(TimezoneHelper::commonTimezones()[$a['timezone']] ?? $a['timezone']) ?></td>
                <td><?= $a['check_in_utc'] ? e(TimezoneHelper::formatArabic($a['check_in_utc'], $a['timezone'])) : '—' ?></td>
                <td><?= $a['check_out_utc'] ? e(TimezoneHelper::formatArabic($a['check_out_utc'], $a['timezone'])) : '—' ?></td>
                <td><?= $complete ? '<span class="badge badge-evaluated">حضور كامل</span>' : '<span class="badge badge-pending">ناقص</span>' ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
