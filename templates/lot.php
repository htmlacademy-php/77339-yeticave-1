<?php
/** @var array $categories */
/** @var array|null $lot Данные лота */
/** @var $hours */
/** @var $minutes */
/** @var $class */
/** @var $errors */
/** @var $currentPrice */
/** @var $minRate */
/** @var $lotId */
/** @var $userName */
/** @var $isAuctionEnded */
/** @var $isLotOwner */
/** @var $isLastRateByUser */
/** @var $rates */



?>

<section class="lot-item container">
    <h2><?= screening($lot['title']); ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?= screening($lot['img']) ?>" width="730" height="548" alt="Сноуборд">
            </div>
            <p class="lot-item__category"> Категория: <span><?= $lot['category'] ?></span></p>
            <p class="lot-item__description"><?= screening($lot['description'])?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
                <div class="lot-item__timer timer <?= $class ?>">
                    <?=$hours ?>:<?=$minutes ?>
                </div>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount"> Текущая цена </span>
                        <span class="lot-item__cost"><?= screening(formatPrice($currentPrice)) ?></span>
                    </div>
                    <div class="lot-item__min-cost">
                        Мин. ставка <span><?= screening(formatPrice($minRate)) ?></span>
                    </div>
                </div>
                <?php if ($userName && !$isAuctionEnded && !$isLotOwner && !$isLastRateByUser) :?>
                <form class="lot-item__form" action="lot.php?id=<?= $lotId ?>" method="post" autocomplete="off">
                    <p class="lot-item__form-item form__item <?= isset($errors['cost']) ? 'form__item--invalid' : '' ?>"> <!-- form__item--invalid -->
                        <label for="cost"> Ваша ставка </label>
                        <input id="cost" type="text" name="cost" placeholder="<?= screening(formatPrice($minRate)) ?>">
                        <?php if (isset($errors['cost'])) : ?>
                            <span class="form__error"
                                  style="display: block">
                                <?= $errors['cost'] ?>
                            </span>
                        <?php endif; ?>
                    </p>
                    <button type="submit" class="button"> Сделать ставку</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="history">
                <h3> История ставок (<span><?= count($rates) ?></span>
                    <?= getNounPluralForm(count($rates), 'ставка', 'ставки', 'ставок') ?>
                    )</h3>
                <table class="history__list">
                    <?php foreach ($rates as $rate) : ?>
                        <tr class="history__item">
                            <td class="history__name"><?= screening($rate['name']) ?></td>
                            <td class="history__price"><?= screening(formatPrice($rate['amount'])) ?></td>
                            <td class="history__time"><?= timeAgo($rate['date_create']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

    </div>
</section>
