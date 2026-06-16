<?php $title = 'إدارة الموظفين'; ?>
<div class="page-header">
    <h1>إدارة الموظفين والمستخدمين</h1>
    <p class="page-header__subtitle">أضف مستخدمين جدد واختر الدور المناسب: موظف، مشرف، مدير قسم، أو مسؤول نظام.</p>
</div>

<div class="card">
    <h2>إضافة مستخدم جديد</h2>
    <form method="post" action="<?= e(url('/manager/users/create')) ?>">
        <?= Csrf::field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="form-group">
                <label class="form-label">الدور</label>
                <select name="role" class="form-control" id="userRole" onchange="toggleManagerField()">
                    <?php foreach ($availableRoles as $roleKey => $roleName): ?>
                    <option value="<?= e($roleKey) ?>" <?= $roleKey === 'employee' ? 'selected' : '' ?>>
                        <?= e($roleName) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">المنطقة الزمنية</label>
                <select name="timezone" class="form-control">
                    <?php foreach (TimezoneHelper::commonTimezones() as $tz => $label): ?>
                    <option value="<?= e($tz) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="managerField">
                <label class="form-label">المشرف / مدير القسم المسؤول</label>
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
    <div class="table-wrap">
        <table class="table">
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
                <tr><td colspan="7" class="table-empty">لا يوجد مستخدمون</td></tr>
            <?php else: foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= e($u['name']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e(RoleHelper::label($u['role'])) ?></td>
                    <td><?= e($u['manager_name'] ?? '—') ?></td>
                    <td><?= e(TimezoneHelper::commonTimezones()[$u['timezone']] ?? $u['timezone']) ?></td>
                    <td>
                        <?php if ((int)$u['is_active'] === 1): ?>
                            <span class="badge badge-active">نشط</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$u['id'] !== Auth::id()): ?>
                        <div class="actions-cell">
                            <form method="post" action="<?= e(url('/manager/users/toggle')) ?>">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm">
                                    <?= (int)$u['is_active'] === 1 ? 'تعطيل' : 'تفعيل' ?>
                                </button>
                            </form>
                            <?php if ($isAdmin): ?>
                            <form method="post" action="<?= e(url('/manager/users/delete')) ?>"
                                  onsubmit="return confirm('هل أنت متأكد من حذف <?= e(addslashes($u['name'])) ?>؟\n\nسيتم حذف سجلات الحضور والمهام المرتبطة به نهائياً.');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleManagerField() {
    var role = document.getElementById('userRole').value;
    document.getElementById('managerField').style.display = role === 'employee' ? 'block' : 'none';
}
toggleManagerField();
</script>
