<!doctype html>
<html lang="en" data-oldweb="web20">
<head>
<?= App\View::partial('head', ['title' => $title, 'canonical' => $canonical ?? null]) ?>
</head>
<body data-session-timeout="<?= App\Security\Session::IDLE_TIMEOUT ?>">
<a class="skip-link" href="#main">Skip to content</a>
<div class="r-layout-sidebar app-shell">
    <aside class="r-sidebar">
        <div class="r-sidebar-brand"><a href="/assets" class="brand-link">Retro Records</a></div>
        <nav class="r-sidebar-nav" aria-label="Main">
            <a href="/assets"<?= $nav === 'assets' ? ' class="active" aria-current="page"' : '' ?>>Server inventory</a>
            <a href="/assets/new"<?= $nav === 'new' ? ' class="active" aria-current="page"' : '' ?>>Add server</a>
            <a href="/account"<?= $nav === 'account' ? ' class="active" aria-current="page"' : '' ?>>My account</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="/admin/users"<?= $nav === 'admin' ? ' class="active" aria-current="page"' : '' ?>>User accounts</a>
            <?php endif; ?>
        </nav>
        <div class="r-sidebar-footer">
            <a href="/privacy">Privacy</a> · <a href="/terms">Terms</a>
        </div>
    </aside>

    <main class="r-main" id="main" tabindex="-1">
        <nav class="r-nav r-nav-top" aria-label="Account">
            <span class="r-nav-brand"><?= e($title ?? 'Retro Records') ?></span>
            <span class="r-nav-spacer"></span>
            <ul>
                <li class="r-dropdown">
                    <button type="button" class="r-menu-trigger" aria-haspopup="true" aria-expanded="false" data-dropdown>
                        <?= e($user['username']) ?><?= $user['role'] === 'admin' ? ' (admin)' : '' ?> ▼
                    </button>
                    <div class="r-dropdown-menu r-dropdown-menu-right">
                        <a href="/account" class="r-menu-item">Account settings</a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="/admin/users" class="r-menu-item">Administration</a>
                        <?php endif; ?>
                        <div class="r-menu-divider"></div>
                        <form method="post" action="/logout">
                            <?= csrf_field() ?>
                            <button type="submit" class="r-menu-item">Sign out</button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="main-body">
            <?= App\View::partial('flashes', ['flashes' => $flashes]) ?>
            <?= $content ?>
        </div>
    </main>
</div>
</body>
</html>
