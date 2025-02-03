<?php

/**
 * форматирует цену лота
 * @param float $amount
 * @return string
 */

function formatPrice(float $amount): string
{
    $amount = ceil($amount);

    if ($amount >= 1000) {
        $formattedAmount = number_format($amount, 0, '', ' ');
    } else {
        $formattedAmount = (string)$amount;
    }

    return $formattedAmount . ' ₽';
}

/**
 * подсчитывает время до окончания показа лота
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
 * подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name путь к файлу шаблона относительно папки templates
 * @param array $data ассоциативный массив с данными для шаблона
 * @return string итоговый HTML
 */

function includeTemplate($name, array $data = []): string
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
 * показывает страницу с ошибками
 * @param $content
 * @param $error
 * @return void
 */

function error(&$content, $error): void
{
    $content = includeTemplate('error.php', ['error' => $error]);
}
