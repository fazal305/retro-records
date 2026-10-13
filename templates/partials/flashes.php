<div class="r-alert r-alert-warn offline-banner" id="offline-banner" role="status" hidden>
    You are offline. Changes can't be saved until your connection comes back.
</div>
<?php foreach ($flashes as $flash): ?>
    <div class="r-alert r-alert-<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'ok' ? 'status' : 'alert' ?>">
        <?= e($flash['message']) ?>
    </div>
<?php endforeach; ?>
