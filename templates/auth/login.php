<div class="r-window auth-window">
    <div class="r-titlebar">
        <h1 class="r-titlebar-title">Sign in to Retro Records</h1>
    </div>
    <form method="post" action="/login" class="r-card-body" novalidate data-busy-label="Signing in…">
        <?= csrf_field() ?>
        <?php if (!empty($failed)): ?>
            <div class="r-alert r-alert-err" role="alert">
                Sign-in failed. Check your username or email and password.
                After 5 failed attempts an account is locked for 15 minutes.
            </div>
        <?php endif; ?>

        <?= App\View::partial('field', [
            'name' => 'identifier', 'label' => 'Username or email', 'value' => $identifier,
            'error' => $errors['identifier'] ?? null,
            'attrs' => ['autocomplete' => 'username', 'required' => true, 'maxlength' => '254', 'autofocus' => true],
        ]) ?>
        <?= App\View::partial('field', [
            'name' => 'password', 'label' => 'Password', 'type' => 'password',
            'error' => $errors['password'] ?? null,
            'attrs' => ['autocomplete' => 'current-password', 'required' => true],
        ]) ?>

        <div class="form-actions">
            <button type="submit" class="r-btn-primary">Sign in</button>
            <span class="r-muted">New here? <a href="/register">Create an account</a></span>
        </div>
    </form>
</div>
