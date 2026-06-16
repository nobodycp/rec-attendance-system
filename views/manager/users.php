<?php $title = 'إدارة الموظفين'; ?>
<h1>إدارة الموظفين والمستخدمين</h1>
<p class="text-muted">أضف مستخدمين جدد واختر الدور المناسب: موظف، مشرف، مدير قسم، أو مسؤول نظام.</p>

<div class="card">
    <h2>إضافة مستخدم جديد</h2>
    <form method="post" action="<?= e(url('/manager/users/create')) ?>">
        <?= Csrf::field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="form-group">
                <label>الدور</label>
                <select name="role" class="form-control" id="userRole" onchange="toggleManagerField()">
                    <?php foreach ($availableRoles as $roleKey => $roleName): ?>
                    <option value="<?= e($roleKey) ?>" <?= $roleKey === 'employee' ? 'selected' : '' ?>>
                        <?= e($roleName) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>المنطقة الزمنية</label>
                <select name="timezone" class="form-control">
                    <?php foreach (TimezoneHelper::commonTimezones() as $tz => $label): ?>
                    <option value="<?= e($tz) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="managerField">
                <label>المشرف / مدير القسم المسؤول</label>
                <select name="manager_id" class="form-control">
                    <?php foreach ($supervisors as $m): ?>
                    <option value="<?= (int)$m['id'] ?>" <?= (int)$m['id'] === Auth::id() ? 'selected' : '' ?>>
                        <?= e($m['name']) ?> (<?= e(RoleHelper::label($m['role'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">إضافة المستخدم</button>
    </form>
</div>

<div class="card">
    <h2>قائمة المستخدمين</h2>
    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد</th>
                <th>الدور</th>
                <th>المشرف</th>
                <th>المنطقة الزمنية</th>
                <th>الحالة</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
            <tr><td colspan="7">لا يوجد مستخدمون</td></tr>
        <?php else: foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e(RoleHelper::label($u['role'])) ?></td>
                <td><?= e($u['manager_name'] ?? '—') ?></td>
                <td><?= e(TimezoneHelper::commonTimezones()[$u['timezone']] ?? $u['timezone']) ?></td>
                <td>
                    <?php if ((int)$u['is_active'] === 1): ?>
                        <span class="badge badge-evaluated">نشط</span>
                    <?php else: ?>
                        <span class="badge badge-pending">معطّل</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ((int)$u['id'] !== Auth::id()): ?>
                    <form method="post" action="<?= e(url('/manager/users/toggle')) ?>" style="display:inline">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-outline" style="padding:0.25rem 0.5rem;font-size:0.85rem">
                            <?= (int)$u['is_active'] === 1 ? 'تعطيل' : 'تفعيل' ?>
                        </button>
                    </form>
                    <?php if ($isAdmin): ?>
                    <form method="post" action="<?= e(url('/manager/users/delete')) ?>" style="display:inline"
                          onsubmit="return confirm('هل أنت متأكد من حذف <?= e(addslashes($u['name'])) ?>؟\n\nسيتم حذف سجلات الحضور والمهام المرتبطة به نهائياً.');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding:0.25rem 0.5rem;font-size:0.85rem">حذف</button>
                    </form>
                    <?php endif; ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleManagerField() {
    var role = document.getElementById('userRole').value;
    document.getElementById('managerField').style.display = role === 'employee' ? 'block' : 'none';
}
toggleManagerField();
</script>
