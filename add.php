<?php

require_once 'data.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $db */

$categories = getCategories($db);

$request = request($_POST, $_FILES, $db, $categories);


$layoutContent = includeTemplate('layout.php', [
    'content' => $request['content'],
    'title' => "Добавление лота",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
