<?php $title = 'لوحة الموظف'; ?>
<h1>مرحباً <?= e(Auth::name()) ?></h1>
<p class="text-muted">المنطقة الزمنية: <?= e(TimezoneHelper::commonTimezones()[$tz] ?? $tz) ?> — اليوم: <?= e($status['local_date']) ?></p>

<div class="card">
    <h2>تسجيل الحضور والانصراف</h2>
    <p class="text-muted">ارسم توقيعك في المربع ثم اضغط الزر المناسب.</p>

    <div class="grid-2" style="margin-bottom:1rem">
        <div class="stat-box">
            <div class="label">الحضور</div>
            <?php if ($status['check_in']): ?>
                <div class="value" style="font-size:1.1rem;color:var(--success)">
                    <?= e(TimezoneHelper::formatArabic($status['check_in']['signed_at_utc'], $tz)) ?>
                </div>
            <?php else: ?>
                <div class="value" style="font-size:1rem;color:var(--warning)">لم يُسجَّل بعد</div>
            <?php endif; ?>
        </div>
        <div class="stat-box">
            <div class="label">الانصراف</div>
            <?php if ($status['check_out']): ?>
                <div class="value" style="font-size:1.1rem;color:var(--success)">
                    <?= e(TimezoneHelper::formatArabic($status['check_out']['signed_at_utc'], $tz)) ?>
                </div>
            <?php else: ?>
                <div class="value" style="font-size:1rem;color:var(--warning)">لم يُسجَّل بعد</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$status['check_in']): ?>
    <form method="post" action="<?= e(url('/employee/attendance')) ?>" class="attendance-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="type" value="check_in">
        <input type="hidden" name="signature_data" id="signature-data-checkin">
        <h3>1 — توقيع الحضور</h3>
        <label>ارسم توقيعك هنا:</label>
        <canvas id="signature-checkin" class="signature-pad" width="600" height="150"></canvas>
        <div style="margin-top:0.75rem">
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
        <label>ارسم توقيعك هنا:</label>
        <canvas id="signature-checkout" class="signature-pad" width="600" height="150"></canvas>
        <div style="margin-top:0.75rem">
            <button type="button" id="clear-checkout" class="btn btn-outline">مسح التوقيع</button>
            <button type="submit" class="btn btn-warning">تسجيل الانصراف الآن</button>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-success">اكتمل تسجيل حضور وانصراف اليوم. شكراً لالتزامك!</div>
    <?php endif; ?>
</div>

<div class="grid-2">
    <div class="card">
        <h2>سجل الحضور (7 أيام)</h2>
        <table>
            <thead><tr><th>التاريخ</th><th>النوع</th><th>الوقت</th></tr></thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="3" class="text-muted">لا توجد سجلات</td></tr>
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
    <table>
        <thead>
            <tr><th>التاريخ</th><th>المهمة</th><th>الحالة</th><th>الدرجة</th><th>إجراء</th></tr>
        </thead>
        <tbody>
        <?php if (empty($tasks)): ?>
            <tr><td colspan="5" class="text-muted">لا توجد مهام</td></tr>
        <?php else: foreach ($tasks as $t): ?>
            <tr>
                <td><?= e($t['task_date']) ?></td>
                <td><strong><?= e($t['title']) ?></strong><br><small><?= e($t['description'] ?? '') ?></small></td>
                <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                <td><?= $t['score'] !== null ? e((string)$t['score']) . '/10' : '—' ?></td>
                <td>
                    <?php if ($t['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-success" onclick="openCompleteModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['title'])) ?>')">أتممت العمل</button>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div id="completeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100;align-items:center;justify-content:center;">
    <div class="card" style="max-width:420px;margin:2rem;">
        <h3 id="modalTitle">إتمام المهمة</h3>
        <form method="post" action="<?= e(url('/employee/task/complete')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="task_id" id="modalTaskId">
            <div class="form-group">
                <label>تاريخ ووقت الإتمام</label>
                <input type="datetime-local" name="completed_at" class="form-control" required
                       value="<?= e(date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="form-group">
                <label>ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success">تأكيد</button>
            <button type="button" class="btn btn-outline" onclick="closeCompleteModal()">إلغاء</button>
        </form>
    </div>
</div>

<script>
function openCompleteModal(id, title) {
    document.getElementById('modalTaskId').value = id;
    document.getElementById('modalTitle').textContent = 'إتمام: ' + title;
    document.getElementById('completeModal').style.display = 'flex';
}
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}
</script>
