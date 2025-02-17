<?php

/**
 * Загрузка файла с валидацией по MIME-типу
 *
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
