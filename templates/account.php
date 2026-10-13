<h1>My account</h1>

<div class="r-grid r-grid-2 account-grid">
    <section class="r-window">
        <div class="r-titlebar"><h2 class="r-titlebar-title">Account details</h2></div>
        <div class="r-card-body">
            <dl class="details-list">
                <dt>Username</dt><dd><?= e($user['username']) ?></dd>
                <dt>Email</dt><dd><?= e($user['email']) ?></dd>
                <dt>Role</dt><dd><?= e(ucfirst($user['role'])) ?></dd>
                <dt>Member since</dt><dd><?= e(fmt_date($user['created_at'])) ?></dd>
                <dt>Last sign-in</dt><dd><?= e(fmt_date($user['last_login_at'])) ?></dd>
            </dl>
        </div>
    </section>

    <section class="r-window">
        <div class="r-titlebar"><h2 class="r-titlebar-title">Change password</h2></div>
        <form method="post" action="/account/password" class="r-card-body" novalidate data-busy-label="Saving…">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>
            <input type="text" name="username" value="<?= e($user['username']) ?>" autocomplete="username" hidden>
            <?= App\View::partial('field', [
                'name' => 'current_password', 'label' => 'Current password', 'type' => 'password',
                'error' => $errors['current_password'] ?? null,
                'attrs' => ['autocomplete' => 'current-password', 'required' => true],
            ]) ?>
            <?= App\View::partial('field', [
                'name' => 'password', 'label' => 'New password', 'type' => 'password',
                'hint' => 'At least 12 characters.', 'error' => $errors['password'] ?? null,
                'attrs' => ['autocomplete' => 'new-password', 'required' => true, 'minlength' => '12', 'maxlength' => '128'],
            ]) ?>
            <?= App\View::partial('field', [
                'name' => 'password_confirmation', 'label' => 'Confirm new password', 'type' => 'password',
                'attrs' => ['autocomplete' => 'new-password', 'required' => true],
            ]) ?>
            <button type="submit" class="r-btn-primary">Change password</button>
        </form>
    </section>
</div>

<section class="r-window danger-zone">
    <div class="r-titlebar"><h2 class="r-titlebar-title">Delete account</h2></div>
    <form method="post" action="/account" class="r-card-body" novalidate data-busy-label="Deleting…">
        <?= csrf_field() ?>
        <?= method_field('DELETE') ?>
        <p>This permanently deletes your account and every server record you've added. It can't be undone.</p>
        <?= App\View::partial('field', [
            'name' => 'delete_password', 'label' => 'Enter your password to confirm', 'type' => 'password',
            'error' => $errors['delete_password'] ?? null,
            'attrs' => ['autocomplete' => 'current-password', 'required' => true],
        ]) ?>
        <button type="submit" class="r-btn-danger">Delete my account</button>
    </form>
</section>
