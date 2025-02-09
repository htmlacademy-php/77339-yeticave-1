<?php
/** @var array $categories */
/** @var int $categoryId */
/** @var string $categoryName */
/** @var array $lots */
?>

<section class="lots">
    <div class="lots__header"><?php
        if (!empty($search)): ?>
            <h2>Результаты поиска по запросу «<span><?= screening($search) ?></span>»</h2>
        <?php elseif(!empty($categoryId)): ?>
            <h2>Все лоты в категории <span>«<?= $categoryName; ?>»</span></h2>
        <?php
        else: ?>
            <h2>Открытые лоты</h2>
        <?php
        endif; ?>
    </div>
    <?php
    if (empty($lots)): ?>
        <p>Ничего не найдено по вашему запросу</p>
    <?php
    else: ?>
        <ul class="lots__list">
            <?php
            foreach ($lots as $lot): ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?= screening($lot["img"]) ?>" width="350" height="260" alt="<?= $lot["title"] ?>">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= screening($lot["category"]) ?></span>
                        <h3 class="lot__title">
                            <a class="text-link" href="lot.php?id=<?= screening($lot['id']) ?>">
                                <?= screening($lot['title']) ?>
                            </a>
                        </h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">Начальная цена</span>
                                <span class="lot__cost"><?= screening(formatPrice($lot["initial_price"])) ?></span>
                            </div>
                            <?php
                            list($hours, $minutes) = getTimeRemaining($lot['date_end']);
                            $time = sprintf('%02d:%02d', $hours, $minutes);
                            $finishing = ($hours < 1) ? "timer--finishing" : '';
                            ?>
                            <div class="lot__timer timer <?= $finishing; ?>">
                                <?=$time;?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php
            endforeach; ?>
        </ul>
    <?php
    endif; ?>
</section>
