<?php $title = 'لوحة الموظف'; ?>
<div class="page-header">
    <h1>مرحباً، <?= e(Auth::name()) ?></h1>
    <p class="page-header__subtitle">المنطقة الزمنية: <?= e(TimezoneHelper::commonTimezones()[$tz] ?? $tz) ?> — اليوم: <?= e($status['local_date']) ?></p>
</div>

<div class="card">
    <h2>تسجيل الحضور والانصراف</h2>
    <div style="padding: 0 1.25rem 1rem">
        <p class="text-muted">ارسم توقيعك في المربع ثم اضغط الزر المناسب.</p>
    </div>

    <div class="stats" style="padding: 0 1.25rem 1rem; margin-bottom: 0">
        <div class="stat-box stat-box--<?= $status['check_in'] ? 'success' : 'warning' ?>">
            <div class="label">الحضور</div>
            <?php if ($status['check_in']): ?>
                <div class="value" style="font-size:1.15rem"><?= e(TimezoneHelper::formatArabic($status['check_in']['signed_at_utc'], $tz)) ?></div>
            <?php else: ?>
                <div class="value stat-box--muted" style="font-size:1rem">لم يُسجَّل بعد</div>
            <?php endif; ?>
        </div>
        <div class="stat-box stat-box--<?= $status['check_out'] ? 'success' : 'warning' ?>">
            <div class="label">الانصراف</div>
            <?php if ($status['check_out']): ?>
                <div class="value" style="font-size:1.15rem"><?= e(TimezoneHelper::formatArabic($status['check_out']['signed_at_utc'], $tz)) ?></div>
            <?php else: ?>
                <div class="value stat-box--muted" style="font-size:1rem">لم يُسجَّل بعد</div>
            <?php endif; ?>
        </div>
    </div>

    <div style="padding: 0 1.25rem 1.25rem">
    <?php if (!$status['check_in']): ?>
    <form method="post" action="<?= e(url('/employee/attendance')) ?>" class="attendance-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="type" value="check_in">
        <input type="hidden" name="signature_data" id="signature-data-checkin">
        <h3>1 — توقيع الحضور</h3>
        <label class="form-label">ارسم توقيعك هنا</label>
        <canvas id="signature-checkin" class="signature-pad" width="600" height="150"></canvas>
        <div class="btn-group" style="margin-top:0.85rem">
            <button type="button" id="clear-checkin" class="btn btn-outline">مسح التوقيع</button>
            <button type="submit" class="btn btn-success">تسجيل الحضور الآن</button>
        </div>
    </form>
    <?php elseif (!$status['check_out']): ?>
    <form method="post" action="<?= e(url('/employee/attendance')) ?>" class="attendance-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="type" value="check_out">
        <input type="hidden" name="signature_data" id="signature-data-checkout">
        <h3>2 — توقيع الانصراف</h3>
        <p class="text-muted">تم تسجيل حضورك. عند الانتهاء من العمل، وقّع الانصراف.</p>
        <label class="form-label">ارسم توقيعك هنا</label>
        <canvas id="signature-checkout" class="signature-pad" width="600" height="150"></canvas>
        <div class="btn-group" style="margin-top:0.85rem">
            <button type="button" id="clear-checkout" class="btn btn-outline">مسح التوقيع</button>
            <button type="submit" class="btn btn-warning">تسجيل الانصراف الآن</button>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-success">اكتمل تسجيل حضور وانصراف اليوم. شكراً لالتزامك!</div>
    <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>سجل الحضور (7 أيام)</h2>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>التاريخ</th><th>النوع</th><th>الوقت</th></tr></thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="3" class="table-empty">لا توجد سجلات</td></tr>
            <?php else: foreach ($recent as $r): ?>
                <tr>
                    <td><?= e($r['local_work_date']) ?></td>
                    <td><?= $r['type'] === 'check_in' ? 'حضور' : 'انصراف' ?></td>
                    <td><?= e(TimezoneHelper::formatArabic($r['signed_at_utc'], $r['timezone'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h2>مهامي</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>التاريخ</th><th>المهمة</th><th>الحالة</th><th>الدرجة</th><th>إجراء</th></tr>
            </thead>
            <tbody>
            <?php if (empty($tasks)): ?>
                <tr><td colspan="5" class="table-empty">لا توجد مهام</td></tr>
            <?php else: foreach ($tasks as $t): ?>
                <tr>
                    <td><?= e($t['task_date']) ?></td>
                    <td><strong><?= e($t['title']) ?></strong><?php if ($t['description'] ?? ''): ?><small><?= e($t['description']) ?></small><?php endif; ?></td>
                    <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                    <td><?= $t['score'] !== null ? e((string)$t['score']) . '/10' : '—' ?></td>
                    <td>
                        <?php if ($t['status'] === 'pending'): ?>
                        <button type="button" class="btn btn-success btn-sm" onclick="openCompleteModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['title'])) ?>')">أتممت العمل</button>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="completeModal" class="modal" role="dialog" aria-modal="true">
    <div class="modal__dialog">
        <div class="modal__header">
            <h3 id="modalTitle">إتمام المهمة</h3>
        </div>
        <form method="post" action="<?= e(url('/employee/task/complete')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="task_id" id="modalTaskId">
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label">تاريخ ووقت الإتمام</label>
                    <input type="datetime-local" name="completed_at" class="form-control" required value="<?= e(date('Y-m-d\TH:i')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="submit" class="btn btn-success">تأكيد</button>
                <button type="button" class="btn btn-outline" data-modal-close>إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCompleteModal(id, title) {
    document.getElementById('modalTaskId').value = id;
    document.getElementById('modalTitle').textContent = 'إتمام: ' + title;
    openModal('completeModal');
}
</script>
