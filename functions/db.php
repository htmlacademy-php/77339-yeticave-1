<?php

/**
 * установка соединения с базой данных
 * @param array $config
 * @return mysqli|bool
 */

function connectDb(array $config): mysqli|bool
{
    $dbConfig = $config['db'];
    $con = mysqli_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);

    if ($con === false) {
        exit("Ошибка подключения: " . mysqli_connect_error());
    }

    mysqli_set_charset($con, 'utf8');

    return $con;
}

/**
 * поиск пользователя в базе данных по email.
 * @param mysqli $db
 * @param string $email
 * @return array|null
 */

function findUser(mysqli $db, string $email): ?array
{
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($db, $sql, [$email]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}

/**
 * получение списка лотов
 * @param mysqli $con
 * @param int|null $categoryId
 * @return array
 */

function getLots(mysqli $con, ?int $categoryId = null): array
{
    $sql = "SELECT l.id, l.title, l.initial_price, l.img, l.sign_up_date, l.date_end, l.category_id,
                   c.id as category_id, c.designation AS category,
                   COALESCE(MAX(b.amount), l.initial_price) AS initial_price
            FROM lots l
            JOIN categories c ON c.id = l.category_id
            LEFT JOIN bets b ON b.lot_id = l.id
            WHERE l.date_end > NOW()";

    if ($categoryId !== null) {
        $sql .= " AND l.category_id = ?";
    }

    $sql .= " GROUP BY l.id, l.title, l.initial_price, l.img, c.designation, l.date_end, l.sign_up_date, l.category_id
              ORDER BY l.date_end, l.sign_up_date DESC";

    if ($categoryId === null) {
        $result = mysqli_query($con, $sql);
    } else {
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    if (!$result) {
        error_log("SQL Error: " . mysqli_error($con));
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


/**
 * получение списка категорий
 * @param mysqli $con
 * @return array
 */

function getCategories(mysqli $con): array
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * добавление нового лота в базу данных
 * @param array $lotData
 * @param mysqli $con
 * @return array
 */

function addLotToDb(array $lotData, mysqli $con): array
{
    $response = [
        'success' => false,
        'lotId' => null,
        'error' => null
    ];

    $sql = 'INSERT INTO lots (sign_up_date, title, category_id, description, img, initial_price, bet_step, author_id, date_end)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = dbGetPrepareStmt($con, $sql, $lotData);

    if (!mysqli_stmt_execute($stmt)) {
        $response['error'] = "Ошибка выполнения запроса: " . mysqli_error($con);
        return $response;
    }

    $response['success'] = true;
    $response['lotId'] = mysqli_insert_id($con);

    return $response;
}

/**
 * получение лота по id
 * @param mysqli $con
 * @param int $id
 * @return array|false|null
 */

function getLotById(mysqli $con, int $id): array|false|null
{
    if ($id < 0 || $id == null || $id == '') {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    $sql = "SELECT  l.*,
                    c.designation AS category,
                    b.amount AS last_bet,
                    l.bet_step
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bets b ON l.id = b.lot_id
            WHERE l.id = $id
            ORDER BY b.sign_up_date DESC
            LIMIT 1;";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("Ошибка SQL: $error");
        return [];
    }

    return mysqli_fetch_assoc($result);
}

/**
 * добавление нового пользователя в базу данных
 * @param array $formData
 * @param mysqli $db
 * @return bool
 */

function addUser(array $formData, mysqli $db): bool
{
    $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, name, password, contacts) VALUES (?, ?, ?, ?)";
    $stmt = dbGetPrepareStmt($db, $sql, [
        $formData['email'],
        $formData['name'],
        $passwordHash,
        $formData['contacts']
    ]);

    return mysqli_stmt_execute($stmt);
}

/**
 * cоздаёт подготовленное выражение на основе готового SQL запроса и переданных данных
 * @param $link mysqli
 * @param $sql string
 * @param array $data
 * @return mysqli_stmt
 */

function dbGetPrepareStmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else {
                if (is_string($value)) {
                    $type = 's';
                } else {
                    if (is_double($value)) {
                        $type = 'd';
                    }
                }
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * поиск лотов по запросу
 * @param mysqli $db
 * @param string $search
 * @return array
 */

function searchLots(mysqli $db, string $search): array {
    $sql ="SELECT
                l.*,
                c.designation AS category
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE MATCH(l.title, l.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                  AND l.date_end > NOW()";

    $stmt = dbGetPrepareStmt($db, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
