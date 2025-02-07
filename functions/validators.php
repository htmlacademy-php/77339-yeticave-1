<?php

/**
 * аутентификация пользователя по email и паролю
 * @param string $email
 * @param string $password
 * @param mysqli $db
 * @return array
 */

function authenticateUser(string $email, string $password, mysqli $db): array {
    $user = findUser($email, $db);

    if (!$user) {
        return ['errors' => ['email' => 'Пользователь с этим email не найден.']];
    }

    if (!password_verify($password, $user['password'])) {
        return ['errors' => ['password' => 'Неверный пароль.']];
    }

    return ['success' => true, 'user' => $user];
}

/** валидация формы авторизации
 * @param array $form
 * @return array
 */

function validateLoginForm(array $form): array {
    $errors = [];
    $required = ['email', 'password'];

    foreach ($required as $field) {
        if (empty(trim($form[$field] ?? ''))) {
            $errors[$field] = 'Поле обязательно для заполнения.';
        }
    }

    return $errors;
}

/**
 * проверка авторизации пользователя
 * @param mysqli $db
 * @return int
 */

function isUserAuthenticated(mysqli $db): int {
    if (!isset($_SESSION['user'])) {
        return 0;
    }

    $userId = $_SESSION['user']['id'];
    $query = "SELECT id FROM users WHERE id = ?";
    $stmt = dbGetPrepareStmt($db, $query, [$userId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        unset($_SESSION['user']);
        return 0;
    }

    return 1;
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
        $errors['email'] = 'Введите корректный e-mail.';
    }
    if (empty($formData['password'])) {
        $errors['password'] = 'Введите пароль.';
    }
    if (empty($formData['designation'])) {
        $errors['designation'] = 'Введите имя.';
    }
    if (empty($formData['contacts'])) {
        $errors['contacts'] = 'Укажите способы связи с Вами.';
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
        return "Email слишком длинный.";
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
        return "Имя лота слишком длинное.";
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
        return "Значение должно быть числом.";
    } elseif ($value <= 0) {
        return "Число должно быть больше нуля.";
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
        return "Шаг ставки должен быть целым числом больше 0.";
    } elseif ($value <= 0) {
        return "Число должно быть больше нуля.";
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
        return "Введите дату в формате 'ГГГГ-ММ-ДД'.";
    }

    $dateNow = date("Y-m-d");
    $timeDiff = strtotime($value) - strtotime($dateNow);

    if ($timeDiff < 24*60*60) {
        return "Укажите дату минимум через 24 часа.";
    }

    return null;
}

/**
 * проверка переданной дату на соответствие формату
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
        'title' => 'Введите наименование лота.',
        'category' => 'Выберите категорию.',
        'description' => 'Введите описание лота.',
        'img' => 'Загрузите изображение.',
        'initial-price' => 'Введите начальную цену.',
        'bet-step' => 'Введите шаг ставки.',
        'date-end' => 'Введите дату завершения торгов.'
    ];

    $rules = [
        'title' => function ($value) {
            return validateLotName($value);
        },
        'bet-step' => function ($value) {
            return validatePositiveFloat($value);
        },
        'initial-price' => function ($value) {
            return validatePositiveInt($value);
        },
        'date-end' => function ($value) {
            return validateDate($value);
        }
    ];

    $required = ['title', 'category', 'description', 'img', 'initial-price', 'bet-step', 'date-end'];
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
        $errors['category'] = "Выберите категорию из списка.";
    } else {
        $categoryId = (int)$_POST['category'];

        $categoryExistsQuery = "SELECT id FROM categories WHERE id = ?";
        $stmt = dbGetPrepareStmt($db, $categoryExistsQuery, [$categoryId]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            $errors['category'] = "Выбранная категория не существует.";
        }
    }

    return $errors;
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
