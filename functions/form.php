<?php

/**
 * обработка формы добавления лота
 * @param array $postData
 * @param array $fileData
 * @param mysqli $db
 * @param array $categories
 * @param int $userId
 * @return array
 */

function addLotForm(array $postData, array $fileData, mysqli $db, array $categories, int $userId): array
{
    $errors = validateAddLotForm($postData, $db);

    $fileName = null;

    if (!isset($errors['file'])) {
        $fileName = processFileUpload($fileData['img'], 'uploads');

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
            (float)$postData['initial_price'],  // initial_price
            (int)$postData['bet_step'],         // bet_step
            $userId,                             // author_id
            $postData['date_end'],              // date_end
        ];

        $result = addLotToDb($newLotData, $db);

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
