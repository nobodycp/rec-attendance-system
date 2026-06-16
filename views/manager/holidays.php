<?php $title = 'العطل الرسمية'; ?>
<div class="page-header">
    <h1>العطل الرسمية</h1>
    <p class="page-header__subtitle">أيام لا تُحسب ضمن أيام العمل في تقارير الحضور (بالإضافة إلى الجمعة والسبت).</p>
</div>

<div class="card">
    <h2>إضافة عطلة</h2>
    <form method="post" action="<?= e(url('/manager/holidays/create')) ?>" class="settings-form" style="padding: 0 1.25rem 1.25rem">
        <?= Csrf::field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">التاريخ</label>
                <input type="date" name="holiday_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">اسم العطلة</label>
                <input type="text" name="name" class="form-control" required placeholder="مثال: اليوم الوطني">
            </div>
        </div>
        <button type="submit" class="btn">إضافة</button>
    </form>
</div>

<div class="card">
    <h2>قائمة العطل</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>التاريخ</th><th>الاسم</th><th>إجراء</th></tr></thead>
            <tbody>
            <?php if (empty($holidays)): ?>
                <tr><td colspan="3" class="table-empty">لا توجد عطل مسجّلة</td></tr>
            <?php else: foreach ($holidays as $h): ?>
            <tr>
                <td><?= e($h['holiday_date']) ?></td>
                <td><?= e($h['name']) ?></td>
                <td>
                    <form method="post" action="<?= e(url('/manager/holidays/delete')) ?>" data-confirm="حذف هذه العطلة؟">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="holiday_id" value="<?= (int) $h['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
