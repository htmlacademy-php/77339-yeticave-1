<?php

/**
 * подсчёт времени до окончания показа лота
 * @param string $date
 * @return array
 */
function getTimeRemaining(string $date): array
{
    $date_now = time();
    $date = strtotime($date);
    $time_diff = $date - $date_now;
    $hours = str_pad((floor($time_diff / (60 * 60))), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad((floor($time_diff / 60 - $hours * 60)), 2, '0', STR_PAD_LEFT);

    if ($date < $date_now) {
        $result[] = '00';
        $result[] = '00';
    }

    $result[] = $hours;
    $result[] = $minutes;
    return $result;
}

/**
 * форматирование цены лота
 * @param int|float $price
 * @return string
 */
function formatPrice(int|float $price): string
{
    $price = number_format($price, 0, '.', ' ');
    return $price . ' ₽';
}

/**
 * подключение шаблона
 * @param string $name
 * @param array $data
 * @return string
 */
function includeTemplate(string $name, array $data = []): string
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * получение ID лота
 * @param mysqli $db
 * @return int
 */
function getLotId(mysqli $db): int
{
    $lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($lotId === null || $lotId === false || $lotId === '') {
        header("Location: /404.php");
        exit();
    }
    $lot = getLotById($db, $lotId);

    if (!$lot) {
        header("Location: /404.php");
        exit();
    }
    return $lotId;
}
