<?php

require_once 'data.php';

/** @var int $isAuth */
/** @var string $userName */
/** @var mysqli $db */

$categories = getCategories($db);

$lotId = getLotID($db);
$lot = getLotById($db, $lotId);

$content = includeTemplate('lot.php', [
    'isAuth' => $isAuth,
    'categories' => $categories,
    'lot' => $lot
]);

$lotTitle = $lot['title'];

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
