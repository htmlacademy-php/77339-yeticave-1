<?php

require_once("data.php");

/** @var mysqli $db */
/** @var string $userName */
/** @var array $categories */
/** @var $pagination */

$searchQuery = trim($_GET['search'] ?? '');

if(empty($searchQuery)){
    header("Location: /");
    exit();
}

$lots = searchLots($db, $searchQuery);


$content = includeTemplate("main.php", [
    'categories' => $categories,
    'lots' => $lots,
    'searchQuery' => $searchQuery,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Поиск",
    'userName' => $userName,
    'categories' => $categories,
    'searchQuery' => $searchQuery,
    'pagination' => $pagination,
]);

print($layoutContent);
