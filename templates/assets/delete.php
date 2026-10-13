<div class="r-window confirm-window" role="alertdialog" aria-labelledby="confirm-title" aria-describedby="confirm-message">
    <div class="r-titlebar">
        <h1 class="r-titlebar-title" id="confirm-title">Confirm Delete</h1>
    </div>
    <form method="post" action="/assets/<?= (int) $asset['id'] ?>" data-busy-label="Deleting…">
        <div class="r-card-body r-flex r-gap-md">
            <span class="r-dialog-icon warn-icon" aria-hidden="true">!</span>
            <p class="r-dialog-message" id="confirm-message">
                Are you sure you want to permanently delete <strong class="mono"><?= e($asset['hostname']) ?></strong>?
                This can't be undone.
            </p>
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
        </div>
        <div class="r-card-footer">
            <button type="submit" class="r-btn-danger">Delete</button>
            <a href="/assets" class="r-btn">Cancel</a>
        </div>
    </form>
</div>
