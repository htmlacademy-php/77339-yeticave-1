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

/**
 * получение списка ставок для указанного лота
 * @param mysqli $db
 * @param int $lotId
 * @return array
 */

function getLotBets(mysqli $db, int $lotId): array {
    $sql = "SELECT b.amount, u.name, b.created_at
            FROM bets b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.date_create DESC";

    $stmt = dbGetPrepareStmt($db, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * получение ставок пользователя
 * @param mysqli $db
 * @param int $userId
 * @return array
 */

function getUserBets(mysqli $db, int $userId): array
{
    $sql = "
        SELECT
            b.id AS bet_id,
            b.amount AS bet_amount,
            b.date_create AS bet_creation,
            l.id AS lot_id,
            l.title AS lot_title,
            l.img AS lot_image,
            l.date_end AS lot_end_date,
            c.designation AS category_name,
            u.contacts AS winner_contacts
        FROM bets b
        JOIN lots l ON r.lot_id = l.id
        JOIN categories c ON l.category_id = c.id
        JOIN users u ON l.author_id = u.id
        WHERE b.user_id = ?
        ORDER BY b.date_create DESC;
    ";

    $stmt = dbGetPrepareStmt($db, $sql, [$userId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * добавление ставки в базу данных
 * @param mysqli $db
 * @param int $userId
 * @param int $lotId
 * @param int $betValue
 * @return bool
 */

function addBet(mysqli $db, int $userId, int $lotId, int $betValue): bool {
    $sql = "INSERT INTO bets (user_id, lot_id, amount, date_create) VALUES (?, ?, ?, NOW())";
    $stmt = dbGetPrepareStmt($db, $sql, [$userId, $lotId, $betValue]);

    return mysqli_stmt_execute($stmt);
}

/**
 * получение максимальной ставки для лота
 * @param mysqli $db
 * @param int $lotId
 * @return float
 */

function getMaxBet(mysqli $db, int $lotId): float
{
    $sql = "SELECT MAX(amount) AS max_amount FROM bets WHERE lot_id = ?";
    $stmt = dbGetPrepareStmt($db, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return (float)($row['max_amount'] ?? 0);
}

/**
 * рассчёт данных для пагинации
 * @param mysqli $db
 * @param int|null $categoryId
 * @param int $pageItems
 * @param int $curPage
 */

function getPaginationData(mysqli $db, ?int $categoryId, int $pageItems, int $curPage): array
{
    $offset = ($curPage - 1) * $pageItems;

    $sqlCount = "SELECT COUNT(*) AS cnt FROM lots WHERE date_ens > NOW()";

    if ($categoryId) {
        $sqlCount .= " AND category_id = ?";
        $stmt = mysqli_prepare($db, $sqlCount);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($db, $sqlCount);
    }

    $itemsCount = mysqli_fetch_assoc($result)['cnt'];
    $pagesCount = (int) ceil($itemsCount / $pageItems);
    $pages = range(1, $pagesCount);

    $queryParams = $_GET;
    $queryParams['page'] = max(1, $curPage - 1);
    $prevPageUrl = "/?" . http_build_query($queryParams);

    $queryParams['page'] = min($pagesCount, $curPage + 1);
    $nextPageUrl = "/?" . http_build_query($queryParams);

    return [
        'offset' => $offset,
        'pages' => $pages,
        'pagesCount' => $pagesCount,
        'prevPageUrl' => $prevPageUrl,
        'nextPageUrl' => $nextPageUrl
    ];
}

/**
 * получение ID победителя для лота, основываясь на последней ставке
 * @param mysqli $db
 * @param int $lotId
 * @return int|null
 */

function getWinnerIdFromBets(mysqli $db, int $lotId): ?int
{
    $rate = getLastLotBet($db, $lotId);

    if ($rate) {
        return $rate['user_id'];
    }

    return null;
}

/**
 * получение последней ставки для указанного лота
 * @param mysqli $db
 * @param int $lotId
 * @return array
 */
function getLastLotBet(mysqli $db, int $lotId): array {
    $sql = "SELECT b.amount, b.user_id, u.name, b.date_create
            FROM bets b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.date_create DESC
            LIMIT 1";

    $stmt = dbGetPrepareStmt($db, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

/**
 * получение лотов, у которых закончился срок и есть ставки, но нет победителя
 * @param mysqli $db
 * @return array
 */

function getLotsWithoutWinners(mysqli $db): array
{
    $sql = "SELECT l.id, l.title, b.user_id, u.email, u.name
            FROM lots l
            JOIN bets b ON l.id = b.lot_id
            JOIN users u ON b.user_id = u.id
            WHERE l.date_end <= NOW()
              AND l.winner_id IS NULL
            ORDER BY b.date_create DESC";

    $result = mysqli_query($db, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * обновление winner_id в таблице lots
 * @param mysqli $db
 * @param int $lotId
 * @param int $winnerId
 * @return bool
 */
function updateLotWinner(mysqli $db, int $lotId, int $winnerId): bool
{
    $sql = "UPDATE lots SET winner_id = ? WHERE id = ?";
    $data = [$winnerId, $lotId];
    $stmt = dbGetPrepareStmt($db, $sql, $data);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * получение ID пользователя, который сделал последнюю ставку по лоту
 * @param mysqli $db
 * @param int $lotId
 * @return int|null
 */
function lastBetUser(mysqli $db, int $lotId): ?int
{
    $sql = "SELECT user_id FROM bets WHERE lot_id = ? ORDER BY date_create DESC LIMIT 1";
    $stmt = dbGetPrepareStmt($db, $sql, [$lotId]);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['user_id'] ?? null;
    }

    return null;
}
