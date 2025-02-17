<?php

/** @var  $pagesCount */

/** @var  $pages */
/** @var  $curPage */
/** @var  $prevPageUrl */
/** @var  $nextPageUrl */

?>

<?php
if ($pagesCount > 1) : ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <a href="<?= $prevPageUrl ?>">Назад</a>
        </li>
        <?php
        foreach ($pages as $page) : ?>
            <?php
            $queryParams['page'] = $page;
            $pageUrl = "/?" . http_build_query($queryParams);
            ?>
            <li class="pagination-item <?= $page === $curPage ? 'pagination-item-active' : '' ?>">
                <a href="<?= $pageUrl ?>"><?= $page; ?></a>
            </li>
            <?php
        endforeach; ?>
        <li class="pagination-item pagination-item-next">
            <a href="<?= $nextPageUrl ?>">Вперед</a>
        </li>
    </ul>
    <?php
endif; ?>
