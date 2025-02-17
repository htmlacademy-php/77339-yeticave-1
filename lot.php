<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */
/** @var int|string $userId */
/** @var array $categories */

$lotId = getLotId($db);
$lot = getLotById($db, $lotId);

$remainingTime = getTimeRemaining($lot["date_end"]);
$hours = $remainingTime[0];
$minutes = $remainingTime[1];
$class = ($hours < 1) ? 'timer--finishing' : '';

$lotPrices = calculateLotPrices($lot);
$initialPrice = $lotPrices['current_price'];
$minBet = $lotPrices['min_bet'];

$isLotOwner = (int) $lot['author_id'] === $userId;
$isLastBetByUser = lastBetUser($db, $lotId) === $userId;
$errors = [];

handleEndedAuction($db, $lotId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cost'])) {

    if (!$userId) {
        http_response_code(403);
        exit('Вы должны войти, чтобы делать ставки.');
    }

    $betValue = trim($_POST['cost']);

    $lotPrices = calculateLotPrices($lot);
    $minBet = $lotPrices['min_bet'];

    $lastBetUserId = lastBetUser($db, $lotId);

        $error = validateBet($betValue, $minBet, $userId, $lastBetUserId);

    if ($error) {
        $errors['cost'] = $error;
    } else {
        addBet($db, $userId, $lotId, (int) $betValue);
        header("Location: lot.php?id=$lotId");
        exit();
    }
}

$bets = getLotBets($db, $lotId);

$content = includeTemplate('lot.php', [
    'categories' => $categories,
    'userName' => $userName,
    'lot' => $lot,
    'hours' => $hours,
    'minutes' => $minutes,
    'class' => $class,
    'initialPrice' => $initialPrice,
    'minBet' => $minBet,
    'errors' => $errors,
    'lotId' => $lotId,
    'isAuctionEnded' => strtotime($lot['date_end']) < time(),
    'isLotOwner' => $isLotOwner,
    'isLastBetByUser' => $isLastBetByUser,
    'bets' => $bets,
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
