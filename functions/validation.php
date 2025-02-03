<?php
/**
 * проверка на положительное число
 * @param mixed $value
 * @return string|null
 */
function validatePositiveFloat ($value): ?string
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
function validatePositiveInt ($value): ?string {
    if (!is_numeric($value) || $value <= 0) {
        return "Шаг ставки должен быть целым числом больше 0.";
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
 * проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 * @param string $date дата в виде строки
 * @return bool true при соответствии, иначе false
 */
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && date_get_last_errors()['warning_count'] === 0 && date_get_last_errors()['error_count'] === 0;
}

/**
 * валидация длины имени лота
 * @param string $name
 * @return string|null
 */
function validateLotName(string $name): ?string
{
    if (mb_strlen($name) > 255) {
        return "Название лота слишком длинное.";
    }
    return null;
}

/**
 * валидация формы добавления лота
 * @param array $form данные полученные из формы
 * @param mysqli $db ресурс соединения
 * @return array массив с ошибками
 */
function validateAddLot(array $form, mysqli $db): array
{
    $errorMessages = [
        'name' => 'Введите наименование лота',
        'category' => 'Выберите категорию',
        'message' => 'Напишите описание лота',
        'img' => 'Загрузите изображение',
        'initial-price' => 'Введите начальную цену',
        'bet-step' => 'Введите шаг ставки',
        'date-end' => 'Введите дату завершения торгов'
    ];

    $rules = [
        'name' => function ($value) {
            return validateLotName($value);
        },
        'initial-price' => function ($value) {
            return validatePositiveFloat($value);
        },
        'bet-step' => function ($value) {
            return validatePositiveInt($value);
        },
        'lot-date' => function ($value) {
            return validateDate($value);
        }
    ];

    $required = ['name', 'category', 'message', 'img', 'initial-price', 'bet-step', 'date-end'];
    $errors = [];

    foreach ($required as $field) {
        if (empty($form[$field]) && $field !== 'img') {
            $errors[$field] = $errorMessages[$field];
        }
    }

    foreach ($rules as $field => $rule) {
        if (!empty($form[$field]) && $rule($form[$field])) {
            $errors[$field] = $rule($form[$field]);
        }
    }

    if (empty($form['category']) || $form['category'] === 'Выберите категорию') {
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
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
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
 * @return string Рассчитанная форма множественнго числа
 */
function getNounPluralForm (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
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
