<?php $page = 'login'; ?>
<div class="login-page">
    <div class="login-card">
        <h1><?= e(config('app.name')) ?></h1>
        <p class="text-center text-muted">جمعية مركز الإرشاد التربوي — نظام الحضور والمهام</p>
        <form method="post" action="<?= e(url('/login')) ?>">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn" style="width:100%">دخول</button>
        </form>
    </div>
</div>
