<?php

require_once 'data.php';

/** @var mysqli $db */
/** @var string $userName */
/** @var array $categories */
/** @var $pagination */

$search = trim($_GET['search'] ?? '');

if(empty($searchQuery)){
    header("Location: /");
    exit();
}

$lots = searchLots($db, $searchQuery);


$content = includeTemplate("main.php", [
    'categories' => $categories,
    'lots' => $lots,
    'searchQuery' => $search,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Поиск",
    'userName' => $userName,
    'categories' => $categories,
    'searchQuery' => $search,
    'pagination' => $pagination,
]);

print($layoutContent);
