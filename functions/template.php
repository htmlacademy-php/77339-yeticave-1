<?php

/**
 * Форматирует дату в "5 минут назад", "вчера", "2 дня назад" и т. д.
 *
 * @param string $date Дата в формате "Y-m-d H:i:s"
 * @return string Отформатированное время
 */
function timeAgo(string $date): string
{
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return "Только что";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "$minutes " . getNounPluralForm($minutes, "минуту", "минуты", "минут") . " назад";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "$hours " . getNounPluralForm($hours, "час", "часа", "часов") . " назад";
    } else {
        return date("d.m.y в H:i", $timestamp);
    }
}

/**
 * Рассчитывает текущую цену лота и минимальную ставку
 * @param array $lot Данные лота (должны содержать 'last_rate', 'start_price' и 'bet_step')
 * @return array Ассоциативный массив с 'current_price' и 'min_rate'
 */
function calculateLotPrices(array $lot): array
{
    if ($lot['last_rate'] !== null) {
        $currentPrice = $lot['last_rate'];
        $minRate = $lot['last_rate'] + $lot['bet_step'];
    } else {
        $currentPrice = $lot['start_price'];
        $minRate = $lot['start_price'] + $lot['bet_step'];
    }

    return [
        'current_price' => $currentPrice,
        'min_rate' => $minRate
    ];
}

/**
 * Подсчитывает время до окончания показа лота
 * @param string $date
 * @return array
 *
 */
function getTimeRemaining(string $date): array
{
    $date_now = time();
    $date = strtotime($date);
    $time_diff = $date - $date_now;
    $hours = str_pad((floor($time_diff / (60 * 60))), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad((floor($time_diff / 60 - $hours * 60)), 2, '0', STR_PAD_LEFT);
    $seconds = str_pad($time_diff % 60, 2, '0', STR_PAD_LEFT);

    if ($date < $date_now) {
        $result[] = '00';
        $result[] = '00';
    }

    $result[] = $hours;
    $result[] = $minutes;
    $result[] = $seconds;
    return $result;
}

/**
 * Форматирует цену лота
 * @param int|float $price
 * @return string
 */
function formatPrice(int|float $price): string
{
    $price = number_format($price, 0, '.', ' ');
    return $price . ' ₽';
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
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
 * Получение ID лота из параметров запроса и валидация
 *
 * @param mysqli $db Соединение с базой данных
 * @return int $lotId Идентификатор лота
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
