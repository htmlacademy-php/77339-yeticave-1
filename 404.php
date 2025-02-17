<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */
/** @var array $categories */

http_response_code(404);

$pageContent = includeTemplate('404.php', [
    'categories' => $categories,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "404-страница не найдена",
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
