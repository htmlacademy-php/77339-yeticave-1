<?php

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
