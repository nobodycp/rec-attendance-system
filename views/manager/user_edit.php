<?php $title = 'تعديل المستخدم'; ?>
<div class="page-header">
    <h1>تعديل المستخدم</h1>
    <p class="page-header__subtitle"><?= e($user['email']) ?></p>
</div>

<div class="settings-layout">
    <div class="card settings-card">
        <h2>البيانات الأساسية</h2>
        <form method="post" action="<?= e(url('/manager/users/update')) ?>" class="settings-form">
            <?= Csrf::field() ?>
            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
            <div class="form-group">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required dir="ltr">
            </div>
            <?php if ($isAdmin): ?>
            <div class="form-group">
                <label class="form-label">الدور</label>
                <select name="role" class="form-control" id="editUserRole" onchange="toggleEditManagerField()">
                    <?php foreach ($availableRoles as $roleKey => $roleName): ?>
                    <option value="<?= e($roleKey) ?>" <?= $user['role'] === $roleKey ? 'selected' : '' ?>><?= e($roleName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="role" value="employee">
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label">المنطقة الزمنية</label>
                <select name="timezone" class="form-control">
                    <?php foreach (TimezoneHelper::commonTimezones() as $tz => $label): ?>
                    <option value="<?= e($tz) ?>" <?= $user['timezone'] === $tz ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="editManagerField">
                <label class="form-label">المشرف</label>
                <select name="manager_id" class="form-control">
                    <?php foreach ($supervisors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) ($user['manager_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>>
                        <?= e($m['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">حفظ التعديلات</button>
        </form>
    </div>

    <div class="card settings-card">
        <h2>إعادة تعيين كلمة المرور</h2>
        <form method="post" action="<?= e(url('/manager/users/reset-password')) ?>" class="settings-form" data-confirm="تأكيد إعادة تعيين كلمة المرور؟">
            <?= Csrf::field() ?>
            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
            <div class="form-group">
                <label class="form-label">كلمة المرور الجديدة</label>
                <div class="password-field">
                    <input type="password" name="password" class="form-control" minlength="<?= passwordMinLength() ?>" required data-password-input>
                    <button type="button" class="password-field__toggle" data-password-toggle aria-label="إظهار كلمة المرور">👁</button>
                </div>
                <p class="form-hint"><?= passwordMinLength() ?> أحرف على الأقل.</p>
            </div>
            <button type="submit" class="btn btn-warning">إعادة التعيين</button>
        </form>
    </div>
</div>

<script>
function toggleEditManagerField() {
    var roleEl = document.getElementById('editUserRole');
    if (!roleEl) return;
    document.getElementById('editManagerField').style.display = roleEl.value === 'employee' ? 'block' : 'none';
}
toggleEditManagerField();
</script>
