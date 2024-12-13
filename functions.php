<?php
function format(float $amount) {
    $amount = ceil($amount);

    if ($amount >= 1000) {
        $formattedAmount = number_format($amount, 0, '', ' ');
    } else {
        $formattedAmount = (string)$amount;
    }

    return $formattedAmount . ' ₽';
}

function getTimeRemaining(string $expiringDate) {
    $timeDifference = strtotime($expiringDate) - time();

    if ($timeDifference <= 0) {
        return [0, 0];
    }

    $hours = floor($timeDifference / 3600);
    $minutes = floor(($timeDifference % 3600) / 60);

    return [$hours, $minutes];
}
?>