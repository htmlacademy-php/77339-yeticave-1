<?php

require_once("data.php");

/** @var mysqli $db */
/** @var string $userName */
/** @var array $categories */

$content = includeTemplate('login.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form = $_POST;

    $required = ['email', 'password'];
    $errors = validateLoginForm($form);

    if (empty($errors)) {
        $authResult = authenticateUser($form['email'], $form['password'], $db);

        if (isset($authResult['success']) && $authResult['success'] === true) {
            $_SESSION['user_id'] = $authResult['user']['id'];
            header('Location: /');
            exit();
        }

        $errors = $authResult['errors'] ?? [];
    }

    if (!empty($errors)) {
        $content = includeTemplate('login.php', [
            'form' => $form,
            'errors' => $errors
        ]);
    }
} else {
    if (isset($_SESSION['user_id'])) {
        header('Location: /');
        exit();
    }
}

$layoutContent = includeTemplate('layout.php', [
    'content' => $content,
    'title' => "Вход на сайт",
    'userName' => $userName,
    'categories' => $categories,
    'pagination' => '',
]);

print($layoutContent);
