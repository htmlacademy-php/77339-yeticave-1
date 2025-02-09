<?php

require_once 'data.php';

/** @var mysqli $db */
/** @var string $userName */

$categories = getCategories($db);

$search = trim($_GET['search'] ?? '');
$lots = $search ? searchLots($db, $search) : getLots($db);


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
]);

print($layoutContent);
