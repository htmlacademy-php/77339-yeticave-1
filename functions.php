<?php

/**
<<<<<<< Updated upstream
 * Форматирует цену лота
=======
 * форматирует цену лота
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
 * Подсчитывает время до окончания показа лота
=======
 * подсчитывает время до окончания показа лота
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
=======
/**
 * установка соединения с базой данных
 * @param array $config настройки подключения
 * @return mysqli|bool ресурс соединения
 */

>>>>>>> Stashed changes
function connectDd(array $config): mysqli|bool
{
    $dbConfig = $config['db'];
    $con = mysqli_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);

<<<<<<< Updated upstream
=======
    if ($con === false) {
        exit("Ошибка подключения: " . mysqli_connect_error());
    }

>>>>>>> Stashed changes
    mysqli_set_charset($con, 'utf8');

    return $con;
}

<<<<<<< Updated upstream
=======
/**
 * получение массива самых новых актуальных лотов из базы данных
 * @param mysqli $con
 * @return array
 */

>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
=======
/**
 * получение списка категорий
 * @param mysqli $con
 * @return array
 */

>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
=======
/**
 * подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name путь к файлу шаблона относительно папки templates
 * @param array $data ассоциативный массив с данными для шаблона
 * @return string итоговый HTML
 */

>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
function getLotById(mysqli $con, int $id): array|false|null
{
    if ($id < 0 || $id == null || $id == '') {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

=======
/**
 * получение лота по id
 * @param mysqli $con
 * @param int $id
 * @return array|false|null
 */

function getLotById(mysqli $con, int $id): array|false|null
{}
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
=======

/**
 * показывает страницу с ошибками
 * @param $content
 * @param $error
 * @return void
 */

function error(&$content, $error)
{
    $content = includeTemplate('error.php', ['error' => $error]);
}
>>>>>>> Stashed changes
