<?php

/**
 * Проверка, завершился ли аукцион, и если да, обновление победителя
 */
function handleEndedAuction(mysqli $db, int $lotId): void
{
    $lot = getLotById($db, $lotId);

    // Проверяем, завершен ли аукцион
    $isAuctionEnded = strtotime($lot['ended_at']) < time();
    if ($isAuctionEnded) {
        $winnerId = getWinnerIdFromRates($db, $lotId);

        if ($winnerId) {
            updateLotWinner($db, $lotId, $winnerId);
        }
    }
}

/**
 * Валидирует введенную ставку
 *
 * @param mixed $rateValue
 * @param int $minRate
 * @param int|null $lastUserId
 * @param int $currentUserId
 * @return string|null Ошибка или null, если ставка корректна
 */
function validateRate(mixed $rateValue, int $minRate, int $currentUserId, int|null $lastUserId = null): ?string {
    if (empty($rateValue)) {
        return "Сделайте вашу ставку.";
    }

    $error = validatePositiveInt($rateValue);
    if ($error) {
        return "Ставка должна быть целым положительным числом.";
    }

    if ((int) $rateValue < $minRate) {
        return "Ставка должна быть не меньше $minRate.";
    }

    if ($lastUserId === $currentUserId) {
        return "Вы не можете делать две ставки подряд.";
    }

    return null;
}

/**
 * Аутентификация пользователя по email и паролю.
 *
 * @param string $email Email пользователя, введённый для аутентификации.
 * @param string $password Пароль пользователя, введённый для аутентификации.
 * @param mysqli $db Объект подключения к базе данных.
 *
 * @return array Массив с результатом аутентификации:
 *               - ['errors' => array] если произошли ошибки (например, пользователь не найден или пароль неверный).
 *               - ['success' => true, 'user' => array] если аутентификация прошла успешно, где 'user' — массив с данными пользователя.
 */
function authenticateUser(string $email, string $password, mysqli $db): array {
    $user = findUserByEmail($email, $db);

    if (!$user) {
        return ['errors' => ['email' => 'Пользователь с этим email не найден']];
    }

    if (!password_verify($password, $user['password'])) {
        return ['errors' => ['password' => 'Неверный пароль']];
    }

    return ['success' => true, 'user' => $user];
}

/** Валидирует форму авторизации.
 * @param array $form Массив данных, полученных из формы авторизации.
 *
 * @return array массив с ошибками валидации
 */
function validateLoginForm(array $form): array {
    $errors = [];
    $required = ['email', 'password'];

    foreach ($required as $field) {
        if (empty(trim($form[$field] ?? ''))) {
            $errors[$field] = 'Это поле должно быть заполнено';
        }
    }

    return $errors;
}


/**
 * Проверяет, авторизован ли пользователь и возвращает его данные.
 * Если пользователь был удален из базы, разлогинивает его.
 *
 * @param mysqli $dbConnection ресурс соединения
 * @return array|null Массив с данными пользователя или null, если не авторизован.
 */
function getUserData(mysqli $dbConnection): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = (int) $_SESSION['user_id'];
    $query = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = dbGetPrepareStmt($dbConnection, $query, [$userId]);
    if (!mysqli_stmt_execute($stmt)) {
        return null;
    }
    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        return null;
    }

    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

/**
 * Валидация данных формы регистрации
 *
 * @param array $formData Данные формы
 * @return array Массив с ошибками
 */
function validateSignUpForm(array &$formData): array
{
    $errors = [];

    if (empty($formData['email'])) {
        $errors['email'] = 'Введите e-mail';
    } elseif (($emailLengthError = validateEmailLength($formData['email'])) !== null) {
        $errors['email'] = $emailLengthError;
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный e-mail';
    }
    if (empty($formData['password'])) {
        $errors['password'] = 'Введите пароль';
    }
    if (empty($formData['name'])) {
        $errors['name'] = 'Введите имя';
    }
    if (empty(trim($formData['contacts']))) {
        $errors['contacts'] = 'Напишите как с вами связаться';
    } else {
        $formData['contacts'] = trim($formData['contacts']);
    }

    return $errors;
}

/**
 * Проверка уникальности e-mail
 *
 * @param string $email Адрес электронной почты
 * @param mysqli $db Объект подключения к базе данных
 * @return bool true, если e-mail уникален, иначе false
 */
function isEmailUnique(string $email, mysqli $db): bool
{
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($db, $query, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) === 0;
}

/**
 * Валидация длины email
 *
 * @param string $name
 * @return string|null
 */
function validateEmailLength(string $name): ?string
{
    if (mb_strlen($name) > 255) {
        return "Длина email не должна превышать 255 символов.";
    }
    return null;
}

/**
 * Валидация длины имени лота
 *
 * @param string $name
 * @return string|null
 */
function validateLotName(string $name): ?string
{
    if (mb_strlen($name) > 255) {
        return "Длина имени лота не должна превышать 255 символов.";
    }
    return null;
}

/**
 * Проверка на положительное число
 * @param mixed $value
 * @return string|null
 */
function validatePositiveFloat (mixed $value): ?string
{
    if (!is_numeric($value)) {
        return "Значение должно быть числом.";
    }

    if ($value <= 0) {
        return "Число должно быть больше нуля.";
    }

    return null;
}

/**
 * Проверка на целое положительное число
 * @param mixed $value
 * @return string|null
 */
function validatePositiveInt (mixed $value): ?string {
    if (!is_numeric($value) || $value <= 0) {
        return "Шаг ставки должен быть целым числом больше 0.";
    }

    return null;
}

/**
 * Валидация даты
 * @param string $value
 * @return string|null
 */
function validateDate (string $value): ?string
{
    if (!isDateValid($value)) {
        return "Введите дату в формате 'ГГГГ-ММ-ДД'";
    }

    $dateNow = date("Y-m-d");
    $timeDiff = strtotime($value) - strtotime($dateNow);

    if ($timeDiff < 24*60*60) {
        return "Укажите дату минимум через 24 часа";
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * isDateValid('2019-01-01'); // true
 * isDateValid('2016-02-29'); // true
 * isDateValid('2019-04-31'); // false
 * isDateValid('10.10.2010'); // false
 * isDateValid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false;
}

/**
 * Валидация формы добавления лота
 *
 * @param array $postData данные полученные из формы
 * @param mysqli $db ресурс соединения
 * @return array Массив с ошибками
 */
function validateAddLotForm(array $postData, mysqli $db): array
{
    $errorMessages = [
        'lot-name' => 'Введите наименование лота',
        'category' => 'Выберите категорию',
        'description' => 'Напишите описание лота',
        'lot-img' => 'Загрузите изображение',
        'lot-rate' => 'Введите начальную цену',
        'lot-step' => 'Введите шаг ставки',
        'lot-date' => 'Введите дату завершения торгов'
    ];

    $rules = [
        'lot-name' => function ($value) {
            return validateLotName($value);
        },
        'lot-rate' => function ($value) {
            return validatePositiveFloat($value);
        },
        'lot-step' => function ($value) {
            return validatePositiveInt($value);
        },
        'lot-date' => function ($value) {
            return validateDate($value);
        }
    ];

    $required = ['lot-name', 'category', 'description', 'lot-img', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    foreach ($required as $field) {
        if (empty($postData[$field]) && $field !== 'lot-img') {
            $errors[$field] = $errorMessages[$field];
        }
    }

    foreach ($rules as $field => $rule) {
        if (!empty($postData[$field]) && $rule($postData[$field])) {
            $errors[$field] = $rule($postData[$field]);
        }
    }

    if (empty($postData['category']) || $postData['category'] === 'Выберите категорию') {
        $errors['category'] = "Выберите категорию из списка";
    } else {
        $categoryId = (int)$_POST['category'];

        $categoryExistsQuery = "SELECT id FROM categories WHERE id = ?";
        $stmt = dbGetPrepareStmt($db, $categoryExistsQuery, [$categoryId]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $errors['category'] = "Выбранная категория не существует";
        }
    }

    return $errors;
}

/**
 * Очищает входные данные, экранируя HTML-специальные символы.
 *
 * @param string|null $input Входная строка, которую нужно очистить.
 * @return string Очищенная строка. Если передано null, вернётся пустая строка.
 */
function screening(?string $input): string
{
    return $input === null ? '' : htmlspecialchars($input);
}


/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     getNounPluralForm(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественного числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}
