<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */

http_response_code(404);

$categories = getCategories($db);

$pageContent = includeTemplate('404.php', [
    'categories' => $categories,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "404 Страница не найдена",
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
