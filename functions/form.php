<?php

/**
 * Обработка формы добавления лота
 *
 * @param array $postData Данные из формы
 * @param array $fileData Данные о загруженных файлах
 * @param mysqli $dbConnection Соединение с базой данных
 * @param array $categories Список категорий (для валидации)
 * @return array Массив с результатом обработки ['success' => bool, 'content' => string, 'errors' => array]
 */
function addLotForm(array $postData, array $fileData, mysqli $dbConnection, array $categories, int $userId): array
{
    $errors = validateAddLotForm($postData, $dbConnection);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($fileData['lot-img'], 'uploads');

        if ($fileName === null) {
            $errors['file'] = "Ошибка при загрузке изображения. Убедитесь, что файл выбран и имеет формат jpg, jpeg или png.";
        }
    }

    if (empty($errors)) {
        $newLotData = [
            $postData['lot-name'],         // title
            (int)$postData['category'],    // category_id
            $postData['description'],          // description
            'uploads/' . $fileName,        // img
            (float)$postData['lot-rate'],  // start_price
            (int)$postData['lot-step'],    // bet_step
            $userId,                       // author_id
            $postData['lot-date'],         // date_end
        ];

        $result = addLot($newLotData, $dbConnection);

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
