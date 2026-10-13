<!doctype html>
<html lang="en" data-oldweb="web20">
<head>
<?= App\View::partial('head', ['title' => $title, 'canonical' => $canonical ?? null]) ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<nav class="r-nav r-nav-top public-nav" aria-label="Main">
    <a class="r-nav-brand" href="/">Retro Records</a>
    <span class="r-nav-spacer"></span>
    <ul>
        <li><a href="/login"<?= ($nav ?? null) === 'login' ? ' class="active" aria-current="page"' : '' ?>>Sign in</a></li>
        <li><a href="/register"<?= ($nav ?? null) === 'register' ? ' class="active" aria-current="page"' : '' ?>>Create account</a></li>
    </ul>
</nav>
<main class="public-main" id="main" tabindex="-1">
    <?= App\View::partial('flashes', ['flashes' => $flashes]) ?>
    <?= $content ?>
</main>
<footer class="public-footer">
    <a href="/privacy">Privacy &amp; cookies</a> · <a href="/terms">Terms of use</a> ·
    <a href="https://github.com/fazal305/retro-records">Source on GitHub</a>
</footer>
</body>
</html>
