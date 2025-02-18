<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */
/** @var int|string $userId */
/** @var array $categories */

$lotId = getLotId($db);
$lot = getLotById($db, $lotId);

$remainingTime = getTimeRemaining($lot["ended_at"]);
$hours = $remainingTime[0];
$minutes = $remainingTime[1];
$class = ($hours < 1) ? 'timer--finishing' : '';

$lotPrices = calculateLotPrices($lot);
$currentPrice = $lotPrices['current_price'];
$minRate = $lotPrices['min_rate'];

$isLotOwner = (int) $lot['author_id'] === $userId;
$isLastRateByUser = lastRateUser($db, $lotId) === $userId;
$errors = [];

handleEndedAuction($db, $lotId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cost'])) {

    if (!$userId) {
        http_response_code(403);
        exit('Вы должны войти, чтобы делать ставки.');
    }

    $rateValue = trim($_POST['cost']);

    $lotPrices = calculateLotPrices($lot);
    $minRate = $lotPrices['min_rate'];

    $lastRateUserId = lastRateUser($db, $lotId);

    $error = validateRate($rateValue, $minRate, $userId, $lastRateUserId);

    if ($error) {
        $errors['cost'] = $error;
    } else {
        addRate($db, $userId, $lotId, (int) $rateValue);
        header("Location: lot.php?id=$lotId");
        exit();
    }
}

$rates = getLotRates($db, $lotId);

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'userName' => $userName,
    'lot' => $lot,
    'hours' => $hours,
    'minutes' => $minutes,
    'class' => $class,
    'currentPrice' => $currentPrice,
    'minRate' => $minRate,
    'errors' => $errors,
    'lotId' => $lotId,
    'isAuctionEnded' => strtotime($lot['ended_at']) < time(),
    'isLotOwner' => $isLotOwner,
    'isLastRateByUser' => $isLastRateByUser,
    'rates' => $rates,
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
