<?php
$appUrl = rtrim((string) App\Config::get('APP_URL', ''), '/');
$pageTitle = $title ? $title . ' · Retro Records' : 'Retro Records · Secure server inventory';
$canonicalPath = $canonical ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$description = 'Retro Records is a secure server inventory built with PHP and PostgreSQL, styled like a classic Web 2.0 admin dashboard.';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="theme-color" content="#1f6fd0">
<?php if ($appUrl !== ''): ?>
<link rel="canonical" href="<?= e($appUrl . $canonicalPath) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e($appUrl . $canonicalPath) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:image" content="<?= e($appUrl) ?>/og-image.png">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="twitter:image" content="<?= e($appUrl) ?>/og-image.png">
<?php endif; ?>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/oldweb-web20.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<script defer src="/_vercel/insights/script.js"></script>
<script defer src="/_vercel/speed-insights/script.js"></script>
