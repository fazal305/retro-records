<?php if ($pages > 1):
    $window = array_unique(array_filter([1, $page - 1, $page, $page + 1, $pages], static fn ($p) => $p >= 1 && $p <= $pages));
    sort($window);
    ?>
    <nav aria-label="Pagination">
        <ul class="r-pagination">
            <?php if ($page > 1): ?>
                <li><a href="<?= e(url($base, $query + ['page' => $page - 1])) ?>" rel="prev">‹ Prev</a></li>
            <?php endif; ?>
            <?php $previous = 0; foreach ($window as $p): ?>
                <?php if ($p - $previous > 1): ?><li><span aria-hidden="true">…</span></li><?php endif; ?>
                <li>
                    <?php if ($p === $page): ?>
                        <a aria-current="page"><?= $p ?></a>
                    <?php else: ?>
                        <a href="<?= e(url($base, $query + ['page' => $p])) ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                </li>
            <?php $previous = $p; endforeach; ?>
            <?php if ($page < $pages): ?>
                <li><a href="<?= e(url($base, $query + ['page' => $page + 1])) ?>" rel="next">Next ›</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
