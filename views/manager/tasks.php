<?php $title = 'إدارة المهام'; ?>
<h1>إدارة المهام اليومية</h1>

<div class="card">
    <h2>إضافة مهمة جديدة</h2>
    <form method="post" action="<?= e(url('/manager/tasks/create')) ?>">
        <?= Csrf::field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label>الموظف</label>
                <select name="employee_id" class="form-control" required>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?= (int)$emp['id'] ?>"><?= e($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>تاريخ المهمة</label>
                <input type="date" name="task_date" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>عنوان المهمة</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>الوصف</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn">إضافة المهمة</button>
    </form>
</div>

<div class="card">
    <h2>قائمة المهام</h2>
    <table>
        <thead>
            <tr><th>التاريخ</th><th>الموظف</th><th>المهمة</th><th>الحالة</th><th>الدرجة</th><th>إجراء</th></tr>
        </thead>
        <tbody>
        <?php if (empty($tasks)): ?>
            <tr><td colspan="6">لا توجد مهام</td></tr>
        <?php else: foreach ($tasks as $t): ?>
        <tr>
            <td><?= e($t['task_date']) ?></td>
            <td><?= e($t['employee_name']) ?></td>
            <td><strong><?= e($t['title']) ?></strong></td>
            <td><span class="badge badge-<?= e($t['status']) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
            <td><?= isset($t['score']) && $t['score'] !== null ? e((string)$t['score']) . '/10' : '—' ?></td>
            <td>
                <?php if ($t['status'] === 'completed'): ?>
                <a href="<?= e(url('/manager/evaluate?id=' . (int)$t['id'])) ?>" class="btn btn-warning">تقييم</a>
                <?php elseif ($t['status'] === 'pending'): ?>
                <button type="button" class="btn btn-success" onclick="openCompleteModal(<?= (int)$t['id'] ?>)">تسجيل إتمام</button>
                <?php else: ?>—<?php endif; ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div id="completeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100;align-items:center;justify-content:center;">
    <div class="card" style="max-width:420px;margin:2rem;">
        <h3>تسجيل إتمام المهمة</h3>
        <form method="post" action="<?= e(url('/employee/task/complete')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="task_id" id="modalTaskId">
            <div class="form-group">
                <label>تاريخ ووقت الإتمام</label>
                <input type="datetime-local" name="completed_at" class="form-control" required value="<?= e(date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success">تأكيد</button>
            <button type="button" class="btn btn-outline" onclick="closeCompleteModal()">إلغاء</button>
        </form>
    </div>
</div>
<script>
function openCompleteModal(id) {
    document.getElementById('modalTaskId').value = id;
    document.getElementById('completeModal').style.display = 'flex';
}
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}
</script>
