<?php

require_once 'data.php';
require_once 'getwinner.php';

/** @var mysqli $db */
/** @var string $userName */
/** @var array $categories */
/** @var $categoryId */
/** @var $pageItems */
/** @var $paginationData */
/** @var $pagination */

$lots = getLots($db, $categoryId, $pageItems, $paginationData['offset']);

$categoryName = null;
if ($categoryId) {
    $category = current(array_filter($categories, fn($cat) => $cat['id'] == $categoryId));
    $categoryName = $category['designation'] ?? null;
}

$pageContent = includeTemplate('main.php', [
    'categories' => $categories,
    'categoryId' => $categoryId,
    'categoryName' => $categoryName,
    'lots' => $lots,
]);

if (empty($lots)) {
    if($categoryId) {
        $pageContent = "<pre><h2>Нет доступных лотов в категории <span>«" . screening($categoryName) . "»</span></h2>";
    } else {
        $pageContent = "<h2>На данный момент нет доступных лотов.</h2>";
    }
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Yeti Cave - Главная",
    'categoryId' => $categoryId,
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => $pagination,
]);

print($layoutContent);
