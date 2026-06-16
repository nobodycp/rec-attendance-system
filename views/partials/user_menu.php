<details class="rd-user-menu">
    <summary class="rd-user-menu-trigger" aria-haspopup="menu" aria-label="قائمة الحساب">
        <span class="rd-avatar rd-avatar-sm" aria-hidden="true">
            <?php if ($avatarUrl = avatarUrl(Auth::avatarPath())): ?>
                <img src="<?= e($avatarUrl) ?>" alt="">
            <?php else: ?>
                <span><?= e(userInitials(Auth::name())) ?></span>
            <?php endif; ?>
        </span>
        <span class="rd-user-menu-name"><?= e(Auth::name()) ?></span>
        <svg class="rd-user-menu-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </summary>

    <div class="rd-user-menu-panel" role="menu">
        <div class="rd-user-menu-header">
            <span class="rd-avatar rd-avatar-md" aria-hidden="true">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="">
                <?php else: ?>
                    <span><?= e(userInitials(Auth::name())) ?></span>
                <?php endif; ?>
            </span>
            <div class="rd-user-menu-identity">
                <div class="rd-user-menu-display"><?= e(Auth::name()) ?></div>
                <div class="rd-user-menu-handle"><?= e(userHandle(Auth::email())) ?> · <?= e(roleLabel(Auth::role())) ?></div>
            </div>
        </div>

        <a class="rd-user-menu-item" href="<?= e(url('/account/settings')) ?>" role="menuitem">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h.1A1.7 1.7 0 0 0 10 3.1V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v.1a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>
            </svg>
            <span>الإعدادات</span>
        </a>

        <div class="rd-user-menu-divider" aria-hidden="true"></div>

        <form method="post" action="<?= e(url('/logout')) ?>" class="rd-user-menu-form">
            <?= Csrf::field() ?>
            <button class="rd-user-menu-item rd-user-menu-item--danger" type="submit" role="menuitem">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </div>
</details>
