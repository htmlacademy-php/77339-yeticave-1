<?php

/**
 * аутентификация пользователя по email и паролю.
 * @param mysqli $link
 * @param string $email
 * @param string $password
 * @return array
 */

function authenticateUser(mysqli $link, string $email, string $password): array {
    $user = findUser($link, $email);

    if (!$user) {
        return ['errors' => ['email' => 'Пользователь с этим email не найден']];
    } elseif (!password_verify($password, $user['password'])) {
        return ['errors' => ['password' => 'Неверный пароль']];
    }

    return ['success' => true, 'user' => $user];
}

/** валидация данных формы авторизации.
 * @param array $form
 * @return array
 */

function validateLoginForm(array $form): array {
    $errors = [];
    $required = ['email', 'password'];

    foreach ($required as $field) {
        if (empty(trim($form[$field] ?? ''))) {
            $errors[$field] = 'Поле обязательно для заполнения';
        }
    }

    return $errors;
}

/**
 * проверка авторизации пользователя
 * @param mysqli $db
 * @return array|null
 */

function getUserData(mysqli $db): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $userId = $_SESSION['user_id']['id'];
    $query = "SELECT id, designation, email FROM users WHERE id = ?";
    $stmt = dbGetPrepareStmt($db, $query, [$userId]);
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
 * валидация данных формы регистрации
 * @param array $formData
 * @return array
 */

function validateSignUpForm(array $formData): array
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
    if (empty($formData['designation'])) {
        $errors['designation'] = 'Введите имя';
    }
    if (empty($formData['contacts'])) {
        $errors['contacts'] = 'Укажите способы связи';
    }

    return $errors;
}

/**
 * проверка уникальности e-mail
 * @param string $email
 * @param mysqli $db
 * @return bool
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
 * валидация длины email
 * @param string $name
 * @return string|null
 */

function validateEmailLength(string $name): ?string
{
    if (mb_strlen($name) > 255) {
        return "Email слишком длинный";
    }
    return null;
}

/**
 * валидация длины имени лота
 * @param string $name
 * @return string|null
 */

function validateLotName(string $name): ?string
{
    if (mb_strlen($name) > 255) {
        return "Название лота слишком длинное";
    }
    return null;
}

/**
 * проверка на положительное число
 * @param mixed $value
 * @return string|null
 */

function validatePositiveFloat (mixed $value): ?string
{
    if (!is_numeric($value)) {
        return "Значение должно быть числом";
    } elseif ($value <= 0) {
        return "Число должно быть больше нуля";
    }

    return null;
}

/**
 * проверка на целое положительное число
 * @param mixed $value
 * @return string|null
 */

function validatePositiveInt (mixed $value): ?string {
    if (!is_numeric($value)) {
        return "Шаг ставки должен быть целым числом больше 0";
    } elseif ($value <= 0) {
        return "Число должно быть больше нуля";
    }

    return null;
}

/**
 * валидация даты
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
 * проверка даты на соответствие формату
 * @param string $date
 * @return bool
 */

function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false;
}

/**
 * валидация формы добавления лота
 * @param array $postData
 * @param mysqli $db
 * @return array
 */

function validateAddLotForm(array $postData, mysqli $db): array
{
    $errorMessages = [
        'title' => 'Введите наименование лота',
        'category' => 'Выберите категорию',
        'description' => 'Введите описание лота',
        'img' => 'Загрузите изображение',
        'initial_price' => 'Введите начальную цену',
        'bet_step' => 'Введите шаг ставки',
        'date_end' => 'Введите дату завершения торгов'
    ];

    $rules = [
        'title' => function ($value) {
            return validateLotName($value);
        },
        'bet_step' => function ($value) {
            return validatePositiveFloat($value);
        },
        'initial_price' => function ($value) {
            return validatePositiveInt($value);
        },
        'date_end' => function ($value) {
            return validateDate($value);
        }
    ];

    $required = ['title', 'category', 'description', 'img', 'initial_price', 'bet_step', 'date_end'];
    $errors = [];

    foreach ($required as $field) {
        if (empty($postData[$field]) && $field !== 'img') {
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
 * экранирование HTML-специальных символов.
 * @param string|null $input
 * @return string
 */

function screening(?string $input): string
{
    return $input === null ? '' : htmlspecialchars($input);
}


/**
 * возвращает корректную форму множественного числа
 * @param int $number
 * @param string $one
 * @param string $two
 * @param string $many
 * @return string
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

/**
 * валидация ставки
 * @param mixed $betValue
 * @param int $minBet
 * @return string|null
 */
function validateBet(mixed $betValue, int $minBet): ?string {
    if (empty($betValue)) {
        return "Сделайте вашу ставку.";
    }

    $error = validatePositiveInt($betValue);
    if ($error) {
        return "Ставка должна быть целым положительным числом.";
    }
    if ((int) $betValue < $minBet) {
        return "Ставка должна быть не меньше $minBet.";
    }
    return null;
}
