<?php

/**
 * установка соединения с базой данных
 * @param array $config настройки подключения
 * @return mysqli|bool ресурс соединения
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
 * получение массива самых новых актуальных лотов из базы данных
 * @param mysqli $con
 * @return array
 */

function getLots(mysqli $con): array
{
    $sql = 'SELECT l.id, l.title, l.initial_price, l.img, c.designation, b.amount
    FROM lots l
        JOIN categories c ON c.id = l.category_id
        LEFT JOIN bets b ON b.lot_id = l.id
    WHERE l.date_end > NOW()
    GROUP BY l.id, l.title, l.initial_price, l.img, c.designation, b.amount
    ORDER BY l.date_create DESC;';
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print('Ошибка SQL:' . $error);
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
    $sql = 'SELECT * FROM categories;';
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print('Ошибка SQL:' . $error);
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * получение лота по id
 * @param mysqli $con
 * @param int $id
 * @return array|false|null
 */

function getLotById(mysqli $con, int $id): array|false|null
{
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

/**
 * загрузка файла с валидацией
 * @param array $file данные о файле из формы
 * @param string $uploadDir папка, куда надо сохранить файл
 * @return string|null имя файла при успешной загрузке, null в противном случае
 */
function processFileUpload(array $file, string $uploadDir): ?string
{
    $allowedMimeTypes = ['image/jpeg', 'image/png'];

    if (empty($file['name']) || !is_uploaded_file($file['tmp_name'])) {
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

/**
 * создает подготовленное выражение на основе готового SQL запроса и переданных данных
 * @param $link mysqli ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data данные для вставки на место плейсхолдеров
 * @return mysqli_stmt Подготовленное выражение
 */
function dbGetPrepareStmt($link, $sql, $data = [])
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
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
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
 * добавление нового лота в базу данных
 * @param array $lotData данные из формы
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

    $sql = 'INSERT INTO lots (created_at, title, category_id, description, image_url, start_price, rate_step, author_id, ended_at)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, 1, ?)';

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
 * обработка формы добавления лота
 * @param array $form данные из формы
 * @param array $file данные о загруженных файлах
 * @param mysqli $db соединение с базой данных
 * @param array $categories список категорий (для валидации)
 * @return array массив с результатом обработки ['success' => bool, 'content' => string, 'errors' => array]
 */

function addLotForm(array $form, array $file, mysqli $db, array $categories): array
{
    $errors = validateAddLot($form, $db);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($file['lot-img'], 'uploads');

        if ($fileName === null) {
            $errors['file'] = "Ошибка при загрузке изображения.";
        }
    }

    if (empty($errors)) {
        $newLotData = [
            $form['lot-name'],         // title
            (int)$form['category'],    // category_id
            $form['message'],          // description
            'uploads/' . $fileName,    // image
            (float)$form['lot-rate'],  // start_price
            (int)$form['lot-step'],    // rate_step
            $form['lot-date'],         // ended_at
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
            'lotData' => $form,
            'categories' => $categories,
            'errors' => $errors,
        ]),
        'errors' => $errors
    ];
}

/**
 * проверка запроса на метод POST
 * @param array $form данные из формы
 * @param array $file данные файла
 * @param mysqli $db соединение с базой данных
 * @param array $categories
 * @return array массив с результатами:
 * [если запрос был POST, то возвращает массив с ключами:
 * 'success' (bool): Успех операции.
 * 'redirect' (string): URL, на который нужно выполнить редирект в случае успешной операции.
 * 'content' (string): HTML-контент, который будет выведен на странице.
 * если запрос не был POST, возвращается шаблон страницы с формой добавления лота.]
 */

function request(array $form, array $file, mysqli $db, array $categories): array
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = addLotForm($form, $file, $db, $categories);

        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit;
        }

        return [
            'content' => $result['content']
        ];
    }

    return [
        'content' => includeTemplate('add.php', [
            'categories' => $categories,
        ])
    ];
}
