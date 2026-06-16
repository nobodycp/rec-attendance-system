<?php
$title = 'التقارير الشهرية';
$months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
?>
<div class="page-header">
    <h1>التقارير الشهرية</h1>
    <p class="page-header__subtitle">عرض درجة الحضور والأداء — أيام العمل: الأحد–الخميس (باستثناء العطل الرسمية).</p>
</div>

<form method="get" class="filter-bar filter-bar--grid no-print">
    <div class="form-group">
        <label class="form-label">الموظف</label>
        <select name="employee_id" class="form-control">
            <?php foreach ($employees as $emp): ?>
            <option value="<?= (int)$emp['id'] ?>" <?= $employeeId === (int)$emp['id'] ? 'selected' : '' ?>><?= e($emp['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">السنة</label>
        <input type="number" name="year" class="form-control" value="<?= (int)$year ?>">
    </div>
    <div class="form-group">
        <label class="form-label">الشهر</label>
        <select name="month" class="form-control">
            <?php foreach ($months as $m => $label): ?>
            <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="btn-group">
        <button type="submit" class="btn">عرض التقرير</button>
        <?php if ($report): ?>
            <button type="button" class="btn btn-outline" onclick="window.print()">طباعة</button>
            <a class="btn btn-outline" href="<?= e(url('/manager/reports?' . http_build_query(['employee_id' => $employeeId, 'year' => $year, 'month' => $month, 'export' => 'csv']))) ?>">تصدير CSV</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($report): ?>
<div class="page-header" style="margin-top:0">
    <h2 style="margin:0;font-size:1.15rem"><?= e($report['user']['name']) ?> — <?= e($months[$month] ?? '') ?> <?= (int)$year ?></h2>
</div>

<div class="stats">
    <div class="stat-box">
        <div class="label">درجة الحضور</div>
        <div class="value"><?= e((string)$report['attendance']['attendance_score']) ?>%</div>
    </div>
    <div class="stat-box">
        <div class="label">درجة الأداء</div>
        <div class="value"><?= $report['performance']['performance_score'] !== null ? e((string)$report['performance']['performance_score']) . '%' : '—' ?></div>
    </div>
</div>

<div class="card">
    <h2>ملخص الحضور</h2>
    <p style="padding: 0 1.25rem 1.25rem; margin:0">أيام حضور كامل: <strong><?= (int)$report['attendance']['full_days'] ?></strong> من <strong><?= (int)$report['attendance']['expected_workdays'] ?></strong> يوم عمل</p>
</div>

<div class="card">
    <h2>المهام</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>التاريخ</th><th>المهمة</th><th>الحالة</th><th>الدرجة</th></tr></thead>
            <tbody>
            <?php if (empty($report['performance']['tasks'])): ?>
                <tr><td colspan="4" class="table-empty">لا توجد مهام</td></tr>
            <?php else: foreach ($report['performance']['tasks'] as $t): ?>
            <tr>
                <td><?= e($t['task_date']) ?></td>
                <td><?= e($t['title']) ?></td>
                <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                <td><?= $t['score'] !== null ? e((string)$t['score']) . '/10' : '—' ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif (empty($employees)): ?>
<div class="card"><p class="table-empty" style="padding:2rem">لا يوجد موظفون لعرض تقاريرهم — <a href="<?= e(url('/manager/users')) ?>">أضف موظفاً</a>.</p></div>
<?php endif; ?>
