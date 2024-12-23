<?php

/**
 * Форматирует цену лота
 * @param int|float $price
 * @return string
 */

function formatPrice(float $amount): string {
    $amount = ceil($amount);

    if ($amount >= 1000) {
        $formattedAmount = number_format($amount, 0, '', ' ');
    } else {
        $formattedAmount = (string)$amount;
    }

    return $formattedAmount . ' ₽';
}

/**
 * Подсчитывает время до окончания показа лота
 * @param string $date
 * @return array
 */

function getTimeRemaining(string $expiringDate): array {
    $timeDifference = strtotime($expiringDate) - time();

    if ($timeDifference <= 0) {
        return [0, 0];
    }

    $hours = floor($timeDifference / 3600);
    $minutes = floor(($timeDifference % 3600) / 60);

    return [$hours, $minutes];
}
?>