<?php
require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */
/** @var int|string $userId */
/** @var array $categories */

$bets = getUserBets($db, $userId);

$processedBets = [];
foreach ($bets as $bet) {
    $lotEndDate = new DateTime($bet['lot_end_date']);
    $now = new DateTime();
    $isLotEnded = $now > $lotEndDate;
    $isBetWinning = $isLotEnded && ($bet['bet_amount'] == getMaxBet($db, $bet['lot_id']));

    $remainingTime = $isLotEnded ? ['00', '00'] : getTimeRemaining($bet['lot_end_date']);

    $processedBets[] = [
        'lot_id' => $bet['lot_id'],
        'lot_title' => screening($bet['lot_title']),
        'lot_image' => screening($bet['lot_image']),
        'category_name' => screening($bet['category_name']),
        'bet_amount' => $bet['bet_amount'],
        'bet_creation' => $bet['bet_creation'],
        'isLotEnded' => $isLotEnded,
        'isBetWinning' => $isBetWinning,
        'remaining_time' => $remainingTime,
        'formatted_price' => formatPrice($bet['bet_amount']),
        'contacts' => $bet['winner_contacts'],
        'time_ago' => timeAgo($bet['bet_creation']),
    ];
}

$content = includeTemplate('my-bets.php', [
    'bets' => $processedBets,
    'userName' => screening($userName),
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => 'Мои ставки',
    'userName' => screening($userName),
    'categories' => $categories,
]);

print($layoutContent);
