<div class="r-window auth-window">
    <div class="r-titlebar">
        <h1 class="r-titlebar-title">Create your account</h1>
    </div>
    <form method="post" action="/register" class="r-card-body" novalidate data-busy-label="Creating account…">
        <?= csrf_field() ?>
        <?php if ($errors): ?>
            <div class="r-alert r-alert-err" role="alert">Please fix the highlighted fields.</div>
        <?php endif; ?>

        <?= App\View::partial('field', [
            'name' => 'username', 'label' => 'Username', 'value' => $old['username'] ?? '',
            'hint' => '3–32 characters: letters, numbers, dots, dashes, underscores.',
            'error' => $errors['username'] ?? null,
            'attrs' => ['autocomplete' => 'username', 'required' => true, 'maxlength' => '32', 'pattern' => '[A-Za-z0-9_.\-]{3,32}'],
        ]) ?>
        <?= App\View::partial('field', [
            'name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $old['email'] ?? '',
            'hint' => 'Used only to sign in. We never send marketing email.',
            'error' => $errors['email'] ?? null,
            'attrs' => ['autocomplete' => 'email', 'required' => true, 'maxlength' => '254'],
        ]) ?>
        <?= App\View::partial('field', [
            'name' => 'password', 'label' => 'Password', 'type' => 'password',
            'hint' => 'At least 12 characters. A short sentence works well.',
            'error' => $errors['password'] ?? null,
            'attrs' => ['autocomplete' => 'new-password', 'required' => true, 'minlength' => '12', 'maxlength' => '128'],
        ]) ?>
        <?= App\View::partial('field', [
            'name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password',
            'attrs' => ['autocomplete' => 'new-password', 'required' => true],
        ]) ?>

        <div class="r-field<?= isset($errors['consent']) ? ' has-error' : '' ?>">
            <label class="checkbox-label">
                <input type="checkbox" name="consent" value="1" required
                    <?= isset($errors['consent']) ? 'aria-invalid="true" aria-describedby="consent-error"' : '' ?>>
                I accept the <a href="/terms">Terms of use</a> and have read the <a href="/privacy">Privacy policy</a>.
            </label>
            <?php if (isset($errors['consent'])): ?>
                <span class="r-hint field-error" id="consent-error"><?= e($errors['consent']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="r-btn-primary">Create account</button>
            <span class="r-muted">Already registered? <a href="/login">Sign in</a></span>
        </div>
    </form>
</div>
