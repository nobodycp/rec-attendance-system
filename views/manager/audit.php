<?php $title = 'سجل التدقيق'; ?>
<div class="page-header">
    <h1>سجل التدقيق</h1>
    <p class="page-header__subtitle">آخر 200 عملية في النظام.</p>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>الوقت</th><th>المستخدم</th><th>الإجراء</th><th>الهدف</th><th>التفاصيل</th></tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="table-empty">لا توجد سجلات بعد</td></tr>
            <?php else: foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><?= e($log['actor_name']) ?></td>
                <td><?= e(auditActionLabel($log['action'])) ?></td>
                <td><?= e($log['target_name'] ?? '—') ?></td>
                <td><?= e($log['details'] ?? '—') ?></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
