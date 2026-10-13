<h1>User accounts</h1>
<p class="r-muted">
    <?= $total ?> account<?= $total === 1 ? '' : 's' ?>. Administrators can unlock accounts but cannot see anyone's server records.
</p>

<div class="table-wrap">
    <table class="r-full">
        <caption class="visually-hidden">All user accounts</caption>
        <thead>
            <tr>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Joined</th>
                <th scope="col">Last sign-in</th>
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <th scope="row"><?= e($row['username']) ?></th>
                    <td class="break"><?= e($row['email']) ?></td>
                    <td><?= e(ucfirst($row['role'])) ?></td>
                    <td>
                        <?php if ($row['locked']): ?>
                            <span class="status-badge status-offline">Locked</span>
                        <?php else: ?>
                            <span class="status-badge status-online">Active</span>
                            <?php if ($row['failed_logins'] > 0): ?>
                                <span class="r-muted"><?= (int) $row['failed_logins'] ?> failed</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap"><?= e(fmt_date($row['created_at'])) ?></td>
                    <td class="nowrap"><?= e(fmt_date($row['last_login_at'])) ?></td>
                    <td>
                        <?php if ($row['locked'] || $row['failed_logins'] > 0): ?>
                            <form method="post" action="/admin/users/<?= (int) $row['id'] ?>/unlock">
                                <?= csrf_field() ?>
                                <button type="submit" class="r-btn-sm" aria-label="Unlock <?= e($row['username']) ?>">Unlock</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= App\View::partial('pagination', ['page' => $page, 'pages' => $pages, 'base' => '/admin/users', 'query' => []]) ?>
