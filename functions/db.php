<?php

/**
 * Рассчитывает данные для пагинации.
 *
 * @param mysqli $dbConnection Подключение к базе данных.
 * @param int|null $categoryId ID категории (null, если без категории).
 * @param int $pageItems Количество лотов на одной странице.
 * @param int $curPage Текущая страница.
 *
 * @return array{
 *     offset: int,
 *     pages: int[],
 *     pagesCount: int,
 *     prevPageUrl: string,
 *     nextPageUrl: string
 * } Массив с данными пагинации:
 *   - offset: смещение для SQL-запроса,
 *   - pages: массив номеров страниц,
 *   - pagesCount: общее количество страниц,
 *   - prevPageUrl: ссылка на предыдущую страницу,
 *   - nextPageUrl: ссылка на следующую страницу.
 */
function getPaginationData(mysqli $dbConnection, ?int $categoryId, int $pageItems, int $curPage): array
{
    $offset = ($curPage - 1) * $pageItems;

    $sqlCount = "SELECT COUNT(*) AS cnt FROM lots WHERE date_end > NOW()";

    if ($categoryId) {
        $sqlCount .= " AND category_id = ?";
        $stmt = mysqli_prepare($dbConnection, $sqlCount);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($dbConnection, $sqlCount);
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
 * Получает ID победителя для лота, основываясь на последней ставке.
 *
 * @param mysqli $dbConnection Ресурс соединения с БД.
 * @param int $lotId ID лота.
 *
 * @return int|null ID пользователя, если ставка выиграла, иначе null.
 */
function getWinnerIdFromBets(mysqli $dbConnection, int $lotId): ?int
{
    $rate = getLastLotBet($dbConnection, $lotId);

    if ($rate) {
        return $rate['user_id'];
    }

    return null;
}

/**
 * Получает последнюю ставку для указанного лота
 *
 * @param mysqli $dbConnection Подключение к базе данных
 * @param int $lotId ID лота
 * @return array Массив последней ставки
 */
function getLastLotBet(mysqli $dbConnection, int $lotId): array {
    $sql = "SELECT b.amount, b.user_id, u.name, b.date_create
            FROM bets b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.date_create DESC
            LIMIT 1";

    $stmt = dbGetPrepareStmt($dbConnection, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

/**
 * Получает лоты, у которых закончился срок и есть ставки, но нет победителя.
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
 * Обновляет winner_id в таблице лотов для лота
 *
 * @param mysqli $db Ресурс соединения с базой данных
 * @param int $lotId ID лота, для которого нужно обновить winner_id
 * @param int $winnerId ID пользователя, чья ставка выиграла
 *
 * @return bool Возвращает true, если обновление прошло успешно, иначе false
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
 * Получает максимальную ставку для указанного лота.
 *
 * @param mysqli $db Подключение к базе данных.
 * @param int $lotId ID лота.
 * @return float Максимальная ставка.
 */
function getMaxBetForLot(mysqli $db, int $lotId): float
{
    $sql = "SELECT MAX(amount) AS max_amount FROM bets WHERE lot_id = ?";
    $stmt = dbGetPrepareStmt($db, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return (float)($row['max_amount'] ?? 0);
}

/**
 * Получает все ставки пользователя из базы данных.
 *
 * @param mysqli $db Подключение к базе данных.
 * @param int $userId ID пользователя.
 * @return array Массив ставок пользователя.
 */
function getUserBets(mysqli $db, int $userId): array
{
    $sql = "
        SELECT
            b.id AS rate_id,
            b.amount AS rate_amount,
            b.date_create AS bet_created_at,
            l.id AS lot_id,
            l.title AS lot_title,
            l.img AS lot_image,
            l.date_end AS lot_end_date,
            c.name AS category_name,
            u.contacts AS winner_contacts
        FROM
            bets b
        JOIN
            lots l ON b.lot_id = l.id
        JOIN
            categories c ON l.category_id = c.id
        JOIN
            users u ON l.author_id = u.id
        WHERE
            b.user_id = ?
        ORDER BY
            b.date_create DESC;
    ";

    $stmt = dbGetPrepareStmt($db, $sql, [$userId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Получает список ставок для указанного лота
 *
 * @param mysqli $db Подключение к базе данных
 * @param int $lotId ID лота
 * @return array Массив ставок
 */
function getLotBets(mysqli $db, int $lotId): array {
    $sql = "SELECT b.amount, b.user_id, u.name, b.date_create
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
 * Добавляет ставку в базу данных
 *
 * @param mysqli $db Соединение с базой данных
 * @param int $userId ID пользователя
 * @param int $lotId ID лота
 * @param int $rateValue Сумма ставки
 * @return bool Успешность добавления
 */
function addBet(mysqli $db, int $userId, int $lotId, int $rateValue): bool {
    $sql = "INSERT INTO bets (user_id, lot_id, amount, date_create) VALUES (?, ?, ?, NOW())";
    $stmt = dbGetPrepareStmt($db, $sql, [$userId, $lotId, $rateValue]);

    return mysqli_stmt_execute($stmt);
}

/**
 * Получает ID пользователя, который сделал последнюю ставку по лоту.
 *
 * @param mysqli $db Соединение с базой данных.
 * @param int $lotId ID лота.
 * @return int|null ID пользователя или null, если ставок нет.
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

/**
 * Поиск лотов по запросу
 *
 * @param mysqli $db Соединение с БД
 * @param string $searchQuery Поисковый запрос
 * @return array Найденные лоты
 */
function searchLots(mysqli $db, string $searchQuery): array
{
    $sql = "SELECT
                l.*,
                c.name AS category
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE MATCH(l.title, l.description) AGAINST(? IN NATURAL LANGUAGE MODE)
                  AND l.date_end > NOW()";

    $stmt = dbGetPrepareStmt($db, $sql, [$searchQuery]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Ищет пользователя в базе данных по email.
 * @param string $email Email пользователя для поиска.
 * @param mysqli $db Объект подключения к базе данных.
 * @return array|null Ассоциативный массив с данными пользователя, если он найден, иначе null.
 */
function findUser(string $email, mysqli $db): ?array
{
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($db, $sql, [$email]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}

/**
 * Установка соединения с базой данных
 * @param array $config Настройки подключения
 * @return mysqli|bool Ресурс соединения
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
 * Выполняет SQL-запрос для выборки активных лотов. Если передан параметр $categoryId,
 * то выполняется выборка только лотов из указанной категории.
 *
 * @param mysqli $con Подключение к базе данных.
 * @param int|null $categoryId Категории для фильтрации (по умолчанию null, если нужен полный список).
 * @return array Массив с лотами
 */

function getLots(mysqli $con, ?int $categoryId = null, int $limit = 9, int $offset = 0): array
{
    $sql = "SELECT l.id, l.title, l.initial_price, l.img, l.date_create, l.date_end, l.category_id,
                   c.id as category_id, c.name AS category,
                   COALESCE(MAX(b.amount), l.initial_price) AS current_price
            FROM lots l
            JOIN categories c ON c.id = l.category_id
            LEFT JOIN bets b ON b.lot_id = l.id
            WHERE l.date_end > NOW()";

    $params = [];
    $types = "";

    if ($categoryId !== null) {
        $sql .= " AND l.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }

    $sql .= " GROUP BY l.id, l.title, l.initial_price, l.img, c.name, l.date_end, l.date_create, l.category_id
              ORDER BY l.date_end, l.date_create DESC
              LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        error_log("SQL Error: " . mysqli_error($con));
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


/**
 * Получает список категорий.
 *
 * @param mysqli $con Подключение к базе данных.
 *
 * @return array Массив категорий.
 */
function getCategories(mysqli $con): array
{
    $sql = "SELECT * FROM categories;";
    $result = mysqli_query($con, $sql);

    if (!$result) {
        error_log("SQL Error: " . mysqli_error($con));
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Проверяет существование категории по ID.
 *
 * @param mysqli $con Подключение к базе данных.
 * @param int $categoryId ID категории для проверки.
 *
 * @return bool true, если категория существует, иначе false.
 */
function isCategoryExists(mysqli $con, int $categoryId): bool
{
    $sql = "SELECT COUNT(*) FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    return false;
}

/**
 * Добавление нового лота в базу данных
 *
 * @param array $lotData Отвалидированные данные из формы
 * @param mysqli $con ресурс соединения
 * @return array массив успех|id нового лота|ошибка
 */
function addLot(array $lotData, mysqli $con): array
{
    $response = [
        'success' => false,
        'lotId' => null,
        'error' => null
    ];

    $sql = 'INSERT INTO lots (date_create, title, category_id, description, img, initial_price, bet_step, author_id, date_end)
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
 * Получение лота по id
 *
 * @param mysqli $con
 * @param int $id
 *
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
                    c.name AS category,
                    b.amount AS last_rate,
                    l.bet_step
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bets b ON l.id = b.lot_id
            WHERE l.id = $id
            ORDER BY b.date_create DESC
            LIMIT 1;";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
        return [];
    }

    return mysqli_fetch_assoc($result);
}

/**
 * Добавление нового пользователя в базу данных
 *
 * @param array $formData Данные формы
 * @param mysqli $db Объект подключения к базе данных
 * @return bool true, если пользователь успешно добавлен, иначе false
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
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
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
