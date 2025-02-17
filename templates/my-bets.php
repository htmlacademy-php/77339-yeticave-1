<?php
/** @var array $rates */

/** @var string $userName */
?>

<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php
        foreach ($rates as $rate) : ?>
            <tr class="rates__item <?= $rate['isLotEnded'] &&
            !$rate['isRateWinning'] ? 'rates__item--end' : '' ?>
            <?= $rate['isRateWinning'] ? 'rates__item--win' : '' ?>">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="<?= screening($rate['lot_image']) ?>" width="54" height="40"
                             alt="<?= screening($rate['lot_title']) ?>">
                    </div>
                    <div>
                        <h3 class="rates__title">
                            <a href="lot.php?id=<?= $rate['lot_id'] ?>"><?= screening($rate['lot_title']) ?></a>
                        </h3>
                        <?php
                        if ($rate['isRateWinning']) : ?>
                            <p><?= screening($rate['contacts']) ?></p>
                            <?php
                        endif; ?>
                    </div>
                </td>
                <td class="rates__category">
                    <?= screening($rate['category_name']) ?>
                </td>
                <td class="rates__timer">
                    <div class="timer
                        <?= $rate['isRateWinning'] ? 'timer--win' : ($rate['isLotEnded'] ? 'timer--end' : '') ?>
                        <?= (!$rate['isRateWinning'] &&
                        !$rate['isLotEnded'] &&
                        (int)$rate['remaining_time'][0] == 0)
                        ? 'timer--finishing'
                        : '' ?>">
                        <?php
                        if ($rate['isLotEnded']) : ?>
                            <?= $rate['isRateWinning'] ? 'Ставка выиграла' : 'Торги окончены' ?>
                            <?php
                        else : ?>
                            <?= implode(':', $rate['remaining_time']) ?>
                            <?php
                        endif; ?>
                    </div>
                </td>
                <td class="rates__price">
                    <?= $rate['formatted_price'] ?>
                </td>
                <td class="rates__time">
                    <?= $rate['time_ago'] ?>
                </td>
            </tr>
            <?php
        endforeach; ?>
    </table>
</section>
