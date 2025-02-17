<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */
/** @var int|string $userId */
/** @var array $categories */
/** @var $pagination */

$rates = getUserRates($db, $userId);

$processedRates = [];
foreach ($rates as $rate) {
    $lotEndDate = new DateTime($rate['lot_end_date']);
    $now = new DateTime();
    $isLotEnded = $now > $lotEndDate;
    $isRateWinning = $isLotEnded && ($rate['rate_amount'] == getMaxBetForLot($db, $rate['lot_id']));

    $remainingTime = $isLotEnded ? ['00', '00'] : getTimeRemaining($rate['lot_end_date']);

    $processedRates[] = [
        'lot_id' => $rate['lot_id'],
        'lot_title' => screening($rate['lot_title']),
        'lot_image' => screening($rate['lot_image']),
        'category_name' => screening($rate['category_name']),
        'rate_amount' => $rate['rate_amount'],
        'rate_created_at' => $rate['rate_created_at'],
        'isLotEnded' => $isLotEnded,
        'isRateWinning' => $isRateWinning,
        'remaining_time' => $remainingTime,
        'formatted_price' => formatPrice($rate['rate_amount']),
        'contacts' => $rate['winner_contacts'],
        'time_ago' => timeAgo($rate['rate_created_at']),
    ];
}

$content = includeTemplate('my-bets.php', [
    'rates' => $processedRates,
    'userName' => screening($userName),
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => 'Мои ставки',
    'userName' => screening($userName),
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
