<?php

require_once 'data.php';

/** @var string $userName */
/** @var mysqli $db */

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Доступ запрещён. Пожалуйста, войдите в систему.');
}

$userId = $_SESSION['user_id']['id'];

$categories = getCategories($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = addLotForm($_POST, $_FILES, $db, $categories, $_SESSION['user_id']['id']);

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }

    $content = $result['content'];
} else {
    $content = includeTemplate('add.php', [
        'categories' => $categories,
    ]);
}


$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Добавление лота",
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
