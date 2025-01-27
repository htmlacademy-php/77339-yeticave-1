<?php

/**
 * Форматирует цену лота
 * @param int|float $price
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
 * Подсчитывает время до окончания показа лота
 * @param string $date
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

function connectDd(array $config): mysqli|bool
{
    $dbConfig = $config['db'];
    $con = mysqli_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);

    mysqli_set_charset($con, 'utf8');

    return $con;
}

function getLots(mysqli $con): array
{
    $sql = 'SELECT l.id, l.title, l.initial_price, l.img, c.designation, b.amount
    FROM lots l
        JOIN categories c ON c.id = l.category_id
        LEFT JOIN bets b ON b.lot_id = l.id
    WHERE l.date_end > NOW()
    GROUP BY l.id, l.title, l.initial_price, l.img, c.designation, b.amount
    ORDER BY l.date_create DESC;';
    $result = mysqli_query($con, $lots_sql);

    if (!$lots_result) {
        $error = mysqli_error($con);
        print('Ошибка SQL:' . $error);
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCategories(mysqli $con): array
{
    $sql = 'SELECT * FROM categories;';
    $result = mysqli_query($con, $categories_sql);

    if (!$categories_result) {
        $error = mysqli_error($con);
        print('Ошибка SQL:' . $error);
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function include_template($name, array $data = []) {
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

function getLotById(mysqli $con, int $id): array|false|null
{
    if ($id < 0 || $id == null || $id == '') {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    $sql = "SELECT  l.*,
                    c.name AS category,
                    r.amount AS last_rate,
                    l.rate_step
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN rates r ON l.id = r.lot_id
            WHERE l.id = $id
            ORDER BY r.created_at DESC
            LIMIT 1;";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("Ошибка SQL: $error");
        return [];
    }

    return mysqli_fetch_assoc($result);
}
