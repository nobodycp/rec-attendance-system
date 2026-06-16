<?php if (!empty($pagination) && ($pagination['pages'] ?? 1) > 1): ?>
<nav class="pagination no-print" aria-label="التصفح">
    <div class="pagination__info">
        عرض <?= (int) (($pagination['page'] - 1) * $pagination['per_page'] + 1) ?>
        –
        <?= (int) min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?>
        من <?= (int) $pagination['total'] ?>
    </div>
    <div class="pagination__links">
        <?php
        $query = $_GET;
        unset($query['route']);
        $baseQuery = http_build_query($query);
        $sep = $baseQuery !== '' ? '&' : '';
        ?>
        <?php if ($pagination['page'] > 1): ?>
            <a class="btn btn-outline btn-sm" href="?<?= e($baseQuery . $sep . 'page=' . ($pagination['page'] - 1)) ?>">السابق</a>
        <?php endif; ?>
        <?php if ($pagination['page'] < $pagination['pages']): ?>
            <a class="btn btn-outline btn-sm" href="?<?= e($baseQuery . $sep . 'page=' . ($pagination['page'] + 1)) ?>">التالي</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
