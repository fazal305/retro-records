<?php $isNew = $mode === 'new'; ?>
<nav class="r-breadcrumb" aria-label="Breadcrumb">
    <a href="/assets">Server inventory</a>
    <span class="r-breadcrumb-sep">/</span>
    <span class="r-breadcrumb-current"><?= $isNew ? 'Add server' : e($asset['hostname'] ?? 'Edit') ?></span>
</nav>

<div class="r-window form-window">
    <div class="r-titlebar">
        <h1 class="r-titlebar-title"><?= $isNew ? 'Add server' : 'Edit server' ?></h1>
    </div>
    <form method="post" action="<?= $isNew ? '/assets' : '/assets/' . (int) $asset['id'] ?>" novalidate
          data-busy-label="Saving…" class="r-card-body">
        <?= csrf_field() ?>
        <?php if (!$isNew): ?><?= method_field('PUT') ?><?php endif; ?>
        <?php if ($errors): ?>
            <div class="r-alert r-alert-err" role="alert">Please fix the highlighted fields and save again.</div>
        <?php endif; ?>

        <?= App\View::partial('asset-fields', ['asset' => $asset, 'errors' => $errors]) ?>

        <div class="form-actions">
            <button type="submit" class="r-btn-primary"><?= $isNew ? 'Add server' : 'Save changes' ?></button>
            <a href="/assets" class="r-btn">Cancel</a>
        </div>
    </form>
</div>
