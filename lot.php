<?php
require_once 'data.php';

$categories = getCategories($db);

$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (is_int($lotId) === false) {
    http_response_code(404);
    $content = includeTemplate('404.php', [
        'categories' => $categories
    ]);
    $lotTitle = '404 - Страница не найдена';
} else {
    $lot = getLotById($db, $lotId);

    if (!$lot) {
        http_response_code(404);
        $content = includetemplate('404.php', [
            'categories' => $categories
        ]);
        $lotTitle = '404 - Страница не найдена';
    } else {
        $content = includetemplate('lot.php', [
            'categories' => $categories,
            'lot' => $lot
        ]);
        $lotTitle = $lot['title'];
    }
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
