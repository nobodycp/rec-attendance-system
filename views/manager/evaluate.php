<?php $title = 'تقييم المهمة'; ?>
<h1>تقييم المهمة</h1>

<div class="card">
    <p><strong>الموظف:</strong> <?= e($task['employee_name']) ?></p>
    <p><strong>المهمة:</strong> <?= e($task['title']) ?></p>
    <p><strong>التاريخ:</strong> <?= e($task['task_date']) ?></p>
    <p><strong>الوصف:</strong> <?= e($task['description'] ?? '') ?></p>

    <form method="post" action="<?= e(url('/manager/evaluate')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
        <div class="form-group">
            <label>درجة الأداء (1–10)</label>
            <input type="number" name="score" class="form-control" min="1" max="10" required value="8">
        </div>
        <div class="form-group">
            <label>ملاحظات التقييم</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="ملاحظات للموظف..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">حفظ التقييم</button>
        <a href="<?= e(url('/manager/tasks')) ?>" class="btn btn-outline">رجوع</a>
    </form>
</div>
