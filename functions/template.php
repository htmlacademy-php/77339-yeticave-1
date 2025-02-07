<?php

/**
 * подсчитывание времени до окончания показа лота
 * @param string $expiringDate
 * @return array
 */

function getTimeRemaining(string $expiringDate): array
{
    $timeDifference = strtotime($expiringDate) - time();

    if ($timeDifference <= 0) {
        return [0, 0];
    }

    $hours = floor($timeDifference / 3600);
    $minutes = floor(($timeDifference % 3600) / 60);

    return [$hours, $minutes];
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
 * подключение шаблона, передача в него данных и возвращение итогового HTML контента
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
 * получение ID лота из параметров запроса и валидация
 * @param mysqli $db
 * @return int $lotId
 */

function getLotID(mysqli $db): int
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

/**
 * обрабатка входа пользователя.
 * @param array $user
 * @return void
 */

function handleSuccessfulLogin(array $user): void {
    $_SESSION['user'] = $user;
    header('Location: /');
    exit();
}
