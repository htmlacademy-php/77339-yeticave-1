<?php

require_once 'data.php';

/** @var mysqli $db */
/** @var string $userName */

$categories = getCategories($db);

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;

$lots = getLots($db, $categoryId);

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
        $pageContent = "<pre><h2>Нет доступных лотов в категории <span>«" . htmlspecialchars($categoryName) . "»</span></h2>";
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
]);

print($layoutContent);
