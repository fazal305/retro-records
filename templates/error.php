<?php
$titles = [
    403 => 'Access denied',
    404 => 'Page not found',
    405 => 'Method not allowed',
    419 => 'Form expired',
    429 => 'Slow down',
    500 => 'Something went wrong',
    503 => 'Service unavailable',
];
$defaults = [
    403 => 'You don\'t have permission to do that.',
    404 => 'The page you asked for doesn\'t exist. Check the address or head back to the start.',
    405 => 'That action isn\'t available at this address.',
    500 => 'An unexpected error stopped this request. It has been logged. Please try again.',
    503 => 'The service is temporarily unavailable. Please try again shortly.',
];
$heading = $titles[$status] ?? 'Error';
?>
<div class="r-window confirm-window error-window">
    <div class="r-titlebar">
        <h1 class="r-titlebar-title">Error <?= (int) $status ?>: <?= e($heading) ?></h1>
    </div>
    <div class="r-card-body r-flex r-gap-md">
        <span class="r-dialog-icon err-icon" aria-hidden="true">×</span>
        <p class="r-dialog-message"><?= e($message ?? $defaults[$status] ?? 'The request could not be completed.') ?></p>
    </div>
    <div class="r-card-footer">
        <a href="<?= $user ? '/assets' : '/' ?>" class="r-btn r-btn-primary"><?= $user ? 'Back to inventory' : 'Go to the home page' ?></a>
    </div>
</div>
