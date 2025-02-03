<?php
require_once 'data.php';

$categories = getCategories($db);

$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$lot = getLotById($db, $lotId);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => $lotTitle,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
