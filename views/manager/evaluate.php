<?php $title = 'تقييم المهمة'; ?>
<div class="page-header">
    <h1>تقييم المهمة</h1>
    <p class="page-header__subtitle"><?= e($task['employee_name']) ?> — <?= e($task['task_date']) ?></p>
</div>

<div class="card">
    <div class="card-body" style="padding-top:0">
        <div class="grid-2" style="margin-bottom:1rem">
            <p><strong>الموظف:</strong> <?= e($task['employee_name']) ?></p>
            <p><strong>التاريخ:</strong> <?= e($task['task_date']) ?></p>
        </div>
        <p><strong>المهمة:</strong> <?= e($task['title']) ?></p>
        <?php if ($task['description'] ?? ''): ?>
        <p class="text-muted"><?= e($task['description']) ?></p>
        <?php endif; ?>
    </div>

    <form method="post" action="<?= e(url('/manager/evaluate')) ?>" style="padding: 0 1.25rem 1.25rem">
        <?= Csrf::field() ?>
        <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
        <div class="form-group">
            <label class="form-label">درجة الأداء (1–10)</label>
            <input type="number" name="score" class="form-control" min="1" max="10" required value="8">
        </div>
        <div class="form-group">
            <label class="form-label">ملاحظات التقييم</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="ملاحظات للموظف..."></textarea>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">حفظ التقييم</button>
            <a href="<?= e(url('/manager/tasks')) ?>" class="btn btn-outline">رجوع</a>
        </div>
    </form>
</div>
