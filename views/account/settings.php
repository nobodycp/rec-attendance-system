<?php $title = 'إعدادات الحساب'; ?>
<?php $user = Auth::user(); ?>
<?php $avatarUrl = avatarUrl($user['avatar_path'] ?? null); ?>

<div class="settings-page">
    <div class="page-header">
        <h1>إعدادات الحساب</h1>
        <p class="page-header__subtitle">إدارة بريدك الإلكتروني وصورتك الشخصية وكلمة المرور.</p>
    </div>

    <div class="settings-layout">
        <div class="card settings-card">
            <h2>الملف الشخصي</h2>

            <div class="settings-profile">
                <span class="rd-avatar rd-avatar-lg" aria-hidden="true">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= e($avatarUrl) ?>" alt="">
                    <?php else: ?>
                        <span><?= e(userInitials($user['name'])) ?></span>
                    <?php endif; ?>
                </span>
                <div class="settings-profile__info">
                    <div class="settings-profile__name"><?= e($user['name']) ?></div>
                    <div class="settings-profile__meta text-muted"><?= e(roleLabel($user['role'])) ?></div>
                </div>
            </div>

            <div class="settings-section">
                <h3 class="settings-section__title">البريد الإلكتروني</h3>
                <form method="post" action="<?= e(url('/account/email')) ?>" class="settings-form">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label class="form-label" for="account-email">البريد الإلكتروني</label>
                        <input
                            type="email"
                            id="account-email"
                            name="email"
                            class="form-control"
                            value="<?= e($user['email']) ?>"
                            required
                            autocomplete="email"
                            dir="ltr"
                        >
                        <p class="form-hint">يُستخدم لتسجيل الدخول إلى النظام.</p>
                    </div>
                    <button type="submit" class="btn">حفظ البريد</button>
                </form>
            </div>

            <div class="settings-section">
                <h3 class="settings-section__title">صورة الملف الشخصي</h3>
                <form method="post" action="<?= e(url('/account/avatar')) ?>" enctype="multipart/form-data" class="settings-form">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label class="form-label" for="account-avatar">اختر صورة</label>
                        <input
                            type="file"
                            id="account-avatar"
                            name="avatar"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            required
                        >
                        <p class="form-hint">PNG أو JPG أو WebP — حتى 2 ميغابايت.</p>
                    </div>
                    <button type="submit" class="btn">رفع الصورة</button>
                </form>
            </div>
        </div>

        <div class="card settings-card">
            <h2>تغيير كلمة المرور</h2>
            <p class="settings-card__intro text-muted">استخدم كلمة مرور قوية لا تقل عن 8 أحرف.</p>
            <form method="post" action="<?= e(url('/account/password')) ?>" class="settings-form">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label class="form-label" for="current-password">كلمة المرور الحالية</label>
                    <input type="password" id="current-password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-password">كلمة المرور الجديدة</label>
                    <input type="password" id="new-password" name="new_password" class="form-control" minlength="8" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm-password">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" id="confirm-password" name="confirm_password" class="form-control" minlength="8" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn">تحديث كلمة المرور</button>
            </form>
        </div>
    </div>
</div>
