<?php $title = 'إدارة المهام'; ?>
<div class="page-header">
    <h1>إدارة المهام اليومية</h1>
    <p class="page-header__subtitle">أضف مهاماً للموظفين وتابع إتمامها وتقييمها.</p>
</div>

<div class="card">
    <h2>إضافة مهمة جديدة</h2>
    <form method="post" action="<?= e(url('/manager/tasks/create')) ?>">
        <?= Csrf::field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-control" required>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?= (int)$emp['id'] ?>"><?= e($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">تاريخ المهمة</label>
                <input type="date" name="task_date" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">عنوان المهمة</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn">إضافة المهمة</button>
    </form>
</div>

<div class="card">
    <h2>قائمة المهام</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>التاريخ</th><th>الموظف</th><th>المهمة</th><th>الحالة</th><th>الدرجة</th><th>إجراء</th></tr>
            </thead>
            <tbody>
            <?php if (empty($tasks)): ?>
                <tr><td colspan="6" class="table-empty">لا توجد مهام</td></tr>
            <?php else: foreach ($tasks as $t): ?>
            <tr>
                <td><?= e($t['task_date']) ?></td>
                <td><?= e($t['employee_name']) ?></td>
                <td><strong><?= e($t['title']) ?></strong></td>
                <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                <td><?= isset($t['score']) && $t['score'] !== null ? e((string)$t['score']) . '/10' : '—' ?></td>
                <td>
                    <?php if ($t['status'] === 'completed'): ?>
                    <a href="<?= e(url('/manager/evaluate?id=' . (int)$t['id'])) ?>" class="btn btn-warning btn-sm">تقييم</a>
                    <?php elseif ($t['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-success btn-sm" onclick="openCompleteModal(<?= (int)$t['id'] ?>)">تسجيل إتمام</button>
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
            <h3>تسجيل إتمام المهمة</h3>
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
                    <label class="form-label">ملاحظات</label>
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
function openCompleteModal(id) {
    document.getElementById('modalTaskId').value = id;
    openModal('completeModal');
}
</script>
