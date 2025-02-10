<?php
/** @var array $bets */
/** @var string $userName */
?>

<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php
        foreach ($bets as $bet): ?>
            <tr class="rates__item <?= $bet['isLotEnded'] && !$bet['isBetWinning'] ? 'rates__item--end' : '' ?> <?= $bet['isRateWinning'] ? 'rates__item--win' : '' ?>">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="<?= screening($bet['lot_image']) ?>" width="54" height="40"
                             alt="<?= screening($bet['lot_title']) ?>">
                    </div>
                    <div>
                        <h3 class="rates__title"><a href="lot.php?id=<?= $bet['lot_id'] ?>"><?= screening($rate['lot_title']) ?></a>
                        </h3>
                        <?php
                        if ($bet['isRateWinning']): ?>
                            <p><?= screening($bet['contacts']) ?></p>
                        <?php
                        endif; ?>
                    </div>
                </td>
                <td class="rates__category">
                    <?= screening($bet['category_name']) ?>
                </td>
                <td class="rates__timer">
                    <div class="timer
                        <?= $bet['isBetWinning'] ? 'timer--win' : ($bet['isLotEnded'] ? 'timer--end' : '') ?>
                        <?= (!$bet['isBetWinning'] && !$bet['isLotEnded'] && (int)$bet['remaining_time'][0] == 0) ? 'timer--finishing' : '' ?>">
                        <?php
                        if ($bet['isLotEnded']): ?>
                            <?= $bet['isBetWinning'] ? 'Ставка выиграла' : 'Торги окончены' ?>
                        <?php
                        else: ?>
                            <?= implode(':', $bet['remaining_time']) ?>
                        <?php
                        endif; ?>
                    </div>
                </td>
                <td class="rates__price">
                    <?= $bet['formatted_price'] ?>
                </td>
                <td class="rates__time">
                    <?= $bet['time_ago'] ?>
                </td>
            </tr>
        <?php
        endforeach; ?>
    </table>
</section>
