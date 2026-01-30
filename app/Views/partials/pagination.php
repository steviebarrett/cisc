<?php
$pages = (int)($result['pages'] ?? 1);
$page  = (int)($result['page'] ?? 1);
if ($pages <= 1) return;
?>
<nav class="mt-3">
    <ul class="pagination">
        <?php
        $prev = max(1, $page - 1);
        $next = min($pages, $page + 1);
        ?>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= e(qs(['page' => $prev])) ?>">Prev</a>
        </li>

        <?php
        $start = max(1, $page - 3);
        $end = min($pages, $page + 3);
        for ($p = $start; $p <= $end; $p++):
            ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= e(qs(['page' => $p])) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= e(qs(['page' => $next])) ?>">Next</a>
        </li>
    </ul>
</nav>