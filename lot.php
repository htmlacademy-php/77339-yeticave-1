<?php
require_once 'data.php';

$categories = getCategories($dbConnection);

$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($lotId === null || $lotId === false || $lotId === '') {
    http_response_code(404);
    $content = include_template('404.php', [
        'categories' => $categories
    ]);
    $lotTitle = '404 - Страница не найдена';
} else {
    $lot = getLotById($dbConnection, $lotId);

    if (!$lot) {
        http_response_code(404);
        $content = include_template('404.php', [
            'categories' => $categories
        ]);
        $lotTitle = '404 - Страница не найдена';
    } else {
        $content = include_template('lot.php', [
            'categories' => $categories,
            'lot' => $lot
        ]);
        $lotTitle = $lot['title'];
    }
}

$layoutContent = include_template('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);