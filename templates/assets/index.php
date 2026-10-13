<?php
use App\Repositories\AssetRepository;

$environments = ['' => 'All environments'] + array_combine(AssetRepository::ENVIRONMENTS, array_map('ucfirst', AssetRepository::ENVIRONMENTS));
$statuses = ['' => 'All statuses'] + array_combine(AssetRepository::STATUSES, array_map('ucfirst', AssetRepository::STATUSES));
$sorts = ['updated' => 'Recently updated', 'hostname' => 'Hostname A–Z', 'status' => 'Status', 'environment' => 'Environment'];
$query = array_filter($filters, static fn ($v) => $v !== '' && $v !== 'updated');
$returnTo = url('/assets', $query + ['page' => $page > 1 ? $page : null]);
?>
<div class="r-flex r-flex-between r-flex-wrap r-gap-sm page-head">
    <h1>Server inventory</h1>
    <a class="r-btn r-btn-primary" href="/assets/new">+ Add server</a>
</div>

<ul class="status-summary" aria-label="Servers by status">
    <?php foreach ($counts as $status => $n): ?>
        <li><span class="status-badge status-<?= e($status) ?>"><?= e(ucfirst($status)) ?></span> <strong><?= $n ?></strong></li>
    <?php endforeach; ?>
</ul>

<?php if ($total === 0 && !$filtered): ?>
    <div class="r-card empty-state">
        <div class="r-card-body">
            <h2>No servers yet</h2>
            <p>Add the first machine you look after: its hostname, IP address, environment and current status.</p>
            <a class="r-btn r-btn-primary" href="/assets/new">Add your first server</a>
        </div>
    </div>
<?php else: ?>
    <form method="get" action="/assets" class="r-card filter-bar" role="search" data-no-busy>
        <div class="r-card-body filter-grid">
            <div class="r-field">
                <label for="q">Search</label>
                <input type="search" id="q" name="q" class="r-input-block" value="<?= e($filters['q']) ?>"
                       placeholder="Hostname, IP or location" maxlength="100">
            </div>
            <?= App\View::partial('field', ['name' => 'environment', 'id' => 'filter-environment', 'label' => 'Environment', 'type' => 'select', 'options' => $environments, 'value' => $filters['environment']]) ?>
            <?= App\View::partial('field', ['name' => 'status', 'id' => 'filter-status', 'label' => 'Status', 'type' => 'select', 'options' => $statuses, 'value' => $filters['status']]) ?>
            <?= App\View::partial('field', ['name' => 'sort', 'id' => 'filter-sort', 'label' => 'Sort by', 'type' => 'select', 'options' => $sorts, 'value' => $filters['sort']]) ?>
            <div class="filter-actions">
                <button type="submit">Apply</button>
                <?php if ($filtered): ?><a href="/assets" class="r-btn">Clear</a><?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($total === 0): ?>
        <div class="r-alert r-alert-warn" role="status">
            No servers match these filters. <a href="/assets">Clear the filters</a> to see everything.
        </div>
    <?php else: ?>
        <p class="r-muted result-count" role="status">
            Showing <?= ($page - 1) * App\Controllers\AssetController::PER_PAGE + 1 ?>–<?= ($page - 1) * App\Controllers\AssetController::PER_PAGE + count($assets) ?>
            of <?= $total ?> server<?= $total === 1 ? '' : 's' ?>
        </p>
        <div class="table-wrap">
            <table class="r-full asset-table">
                <caption class="visually-hidden">Your servers</caption>
                <thead>
                    <tr>
                        <th scope="col">Hostname</th>
                        <th scope="col">IP address</th>
                        <th scope="col">Environment</th>
                        <th scope="col">Status</th>
                        <th scope="col">Location</th>
                        <th scope="col">Updated</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): ?>
                        <tr>
                            <th scope="row" class="mono">
                                <?= e($asset['hostname']) ?>
                                <?php if ($asset['notes'] !== ''): ?>
                                    <span class="row-notes"><?= e(mb_strimwidth($asset['notes'], 0, 80, '…')) ?></span>
                                <?php endif; ?>
                            </th>
                            <td class="mono"><?= e($asset['ip_address'] ?? '—') ?></td>
                            <td><?= e(ucfirst($asset['environment'])) ?></td>
                            <td><span class="status-badge status-<?= e($asset['status']) ?>"><?= e(ucfirst($asset['status'])) ?></span></td>
                            <td><?= e($asset['location'] !== '' ? $asset['location'] : '—') ?></td>
                            <td class="nowrap"><time datetime="<?= e((new DateTimeImmutable($asset['updated_at']))->format(DATE_ATOM)) ?>" title="<?= e(fmt_date($asset['updated_at'])) ?>"><?= e((new DateTimeImmutable($asset['updated_at']))->setTimezone(new DateTimeZone('UTC'))->format('j M Y')) ?></time></td>
                            <td class="row-actions">
                                <a class="r-btn r-btn-sm" href="/assets/<?= (int) $asset['id'] ?>/edit"
                                   data-edit="<?= e(json_encode([
                                       'id' => $asset['id'], 'hostname' => $asset['hostname'], 'ip_address' => $asset['ip_address'] ?? '',
                                       'location' => $asset['location'], 'environment' => $asset['environment'],
                                       'status' => $asset['status'], 'notes' => $asset['notes'],
                                   ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
                                   aria-label="Edit <?= e($asset['hostname']) ?>">Edit</a>
                                <a class="r-btn r-btn-sm r-btn-danger" href="/assets/<?= (int) $asset['id'] ?>/delete"
                                   data-delete="<?= (int) $asset['id'] ?>" data-hostname="<?= e($asset['hostname']) ?>"
                                   aria-label="Delete <?= e($asset['hostname']) ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= App\View::partial('pagination', ['page' => $page, 'pages' => $pages, 'base' => '/assets', 'query' => $query]) ?>
    <?php endif; ?>
<?php endif; ?>

<dialog class="r-modal" id="edit-dialog" aria-labelledby="edit-dialog-title">
    <form method="post" action="/assets" id="edit-form" novalidate data-busy-label="Saving…">
        <div class="r-titlebar">
            <h2 class="r-titlebar-title" id="edit-dialog-title">Edit server</h2>
            <div class="r-titlebar-controls">
                <button type="button" class="r-close" data-close aria-label="Close">X</button>
            </div>
        </div>
        <div class="r-modal-body">
            <?= csrf_field() ?>
            <?= method_field('PUT') ?>
            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
            <?= App\View::partial('asset-fields', ['asset' => [], 'errors' => [], 'prefix' => 'edit-']) ?>
        </div>
        <div class="r-modal-footer">
            <button type="submit" class="r-btn-primary">Save changes</button>
            <button type="button" data-close>Cancel</button>
        </div>
    </form>
</dialog>

<dialog class="r-modal delete-dialog" id="delete-dialog" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-message">
    <form method="post" action="/assets" id="delete-form" data-busy-label="Deleting…">
        <div class="r-titlebar">
            <h2 class="r-titlebar-title" id="delete-dialog-title">Confirm Delete</h2>
            <div class="r-titlebar-controls">
                <button type="button" class="r-close" data-close aria-label="Close">X</button>
            </div>
        </div>
        <div class="r-modal-body r-flex r-gap-md">
            <span class="r-dialog-icon warn-icon" aria-hidden="true">!</span>
            <p class="r-dialog-message" id="delete-dialog-message">
                Are you sure you want to permanently delete <strong class="mono" id="delete-hostname"></strong>?
                This can't be undone.
            </p>
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
        </div>
        <div class="r-modal-footer">
            <button type="submit" class="r-btn-danger">Delete</button>
            <button type="button" data-close autofocus>Cancel</button>
        </div>
    </form>
</dialog>
