<?php

require_once 'data.php';

/** @var mysqli $db */
/** @var int $isAuth */
/** @var string $userName */

$categories = getCategories($db);
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$lots = getLots($db, $categoryId);

$pageContent = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

if (empty($lots)) {
    $pageContent = "На данный момент нет доступных лотов.";
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "YetiCave - Главная",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
