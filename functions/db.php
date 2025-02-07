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
 * поиск пользователя в базе данных по email
 * @param string $email
 * @param mysqli $db
 * @return array|null
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
 * получение списка лотов
 * @param mysqli $con
 * @param int|null $categoryId
 * @return array
 */

function getLots(mysqli $con, ?int $categoryId = null): array
{
    if ($categoryId === null) {
        $sql = "SELECT l.id, l.title, l.initial_price, l.img, l.date_create, l.date_end, c.id as category_id, c.designation AS category,
                    COALESCE(MAX(b.amount), l.initial_price) AS current_price
                FROM lots l
                    JOIN categories c ON c.id = l.category_id
                    LEFT JOIN bets b ON b.lot_id = l.id
                WHERE l.ended_at > NOW()
                GROUP BY l.id, l.title, l.initial_price, l.img, l.date_create, l.date_end, c.id, c.designation
                ORDER BY l.date_end, l.date_create DESC;";

        $result = mysqli_query($con, $sql);
    } else {
        $sql = "SELECT l.id, l.title, l.initial_price, l.img, l.date_create, l.date_end, c.id as category_id, c.designation AS category
                    COALESCE(MAX(r.amount), l.start_price) AS current_price
                FROM lots l
                    JOIN categories c ON c.id = l.category_id
                    LEFT JOIN bets b ON b.lot_id = l.id
                WHERE l.ended_at > NOW() AND l.category_id = ?
                GROUP BY l.id, l.title, l.initial_price, l.img, l.date_create, l.date_end, c.id, c.designation
                ORDER BY l.date_end, l.date_create DESC;";

        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    if (!$result) {
        $error = mysqli_error($con);
        error_log("SQL Error: $error");
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

function addLot(array $lotData, mysqli $con): array
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
        error_log("SQL Error: $error");
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
    $sql = "INSERT INTO users (email, designation, password, contacts) VALUES (?, ?, ?, ?)";
    $stmt = dbGetPrepareStmt($db, $sql, [
        $formData['email'],
        $formData['designation'],
        $passwordHash,
        $formData['contacts']
    ]);

    return mysqli_stmt_execute($stmt);
}

/**
 * создаёт подготовленное выражение на основе готового SQL запроса и переданных данных
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
 * обработка формы добавления лота
 * @param array $postData
 * @param array $fileData
 * @param mysqli $db
 * @param array $categories
 * @return array
 */

function addLotForm(array $postData, array $fileData, mysqli $db, array $categories, int $userId): array
{
    $errors = validateAddLotForm($postData, $db);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($fileData['lot-img'], 'uploads');

        if ($fileName === null) {
            $errors['file'] = "Ошибка при загрузке изображения.";
        }
    }

    if (empty($errors)) {
        $newLotData = [
            $postData['title'],                 // title
            (int)$postData['category'],         // category_id
            $postData['description'],           // description
            'uploads/' . $fileName,             // img
            (float)$postData['initial-price'],  // initial_price
            (int)$postData['bet-step'],         // bet_step
            $userId,                            // author_id
            $postData['date-end'],              // date_end
        ];

        $result = addLot($newLotData, $db);

        if ($result['success']) {
            return [
                'success' => true,
                'redirect' => 'lot.php?id=' . $result['lotId']
            ];
        } else {
            $errors['database'] = $result['error'];
        }
    }

    return [
        'success' => false,
        'content' => includeTemplate('add.php', [
            'lotData' => $postData,
            'categories' => $categories,
            'errors' => $errors,
        ]),
        'errors' => $errors
    ];
}

/**
 * загрузка файла с валидацией по MIME-типу
 * @param array $file
 * @param string $uploadDir
 * @return string|null
 */

function processFileUpload(array $file, string $uploadDir): ?string
{
    $allowedMimeTypes = ['image/jpeg', 'image/png'];

    if (empty($file['designation']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $fileTmpPath = $file['tmp_name'];
    $fileMimeType = mime_content_type($fileTmpPath);

    if (!in_array($fileMimeType, $allowedMimeTypes)) {
        return null;
    }

    $fileExtension = $fileMimeType === 'image/jpeg' ? '.jpg' : '.png';
    $fileName = uniqid() . $fileExtension;
    $destinationPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
        return null;
    }

    return $fileName;
}
