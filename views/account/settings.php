<?php $title = 'إعدادات الحساب'; ?>
<?php $user = Auth::user(); ?>
<?php $avatarUrl = avatarUrl($user['avatar_path'] ?? null); ?>

<div class="page-header">
    <h1>إعدادات الحساب</h1>
    <p class="page-header__subtitle">إدارة صورتك الشخصية وكلمة المرور.</p>
</div>

<div class="settings-stack">
    <div class="card">
        <h2>الملف الشخصي</h2>
        <div class="settings-profile">
            <div class="settings-profile__avatar">
                <span class="rd-avatar rd-avatar-lg" aria-hidden="true">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="">
                    <?php else: ?>
                        <span><?= e(userInitials($user['name'])) ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="settings-profile__info">
                <div class="settings-profile__name"><?= e($user['name']) ?></div>
                <div class="settings-profile__meta text-muted"><?= e(userHandle($user['email'])) ?> · <?= e(roleLabel($user['role'])) ?></div>
            </div>
        </div>

        <form method="post" action="<?= e(url('/account/avatar')) ?>" enctype="multipart/form-data" class="settings-form">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label class="form-label">صورة الملف الشخصي</label>
                <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" required>
                <p class="form-hint">PNG أو JPG أو WebP — حتى 2 ميغابايت.</p>
            </div>
            <button type="submit" class="btn">رفع الصورة</button>
        </form>
    </div>

    <div class="card">
        <h2>تغيير كلمة المرور</h2>
        <form method="post" action="<?= e(url('/account/password')) ?>" class="settings-form">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label class="form-label">كلمة المرور الحالية</label>
                <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label">كلمة المرور الجديدة</label>
                <input type="password" name="new_password" class="form-control" minlength="8" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                <input type="password" name="confirm_password" class="form-control" minlength="8" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn">تحديث كلمة المرور</button>
        </form>
    </div>
</div>
