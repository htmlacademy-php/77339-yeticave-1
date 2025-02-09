<?php

require_once 'data.php';

/** @var mysqli $db */
/** @var string $userName */

$categories = getCategories($db);
$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = $_POST;

    $errors = validateSignUpForm($formData);

    if (empty($errors['email']) && !isEmailUnique($formData['email'], $db)) {
        $errors['email'] = 'Пользователь с таким e-mail уже зарегистрирован';
    }

    if (empty($errors)) {
        if (addUser($formData, $db)) {
            header('Location: login.php');
            exit;
        } else {
            $errors['database'] = 'Ошибка записи в базу данных. Попробуйте позже.';
        }
    }
}

$pageContent = includeTemplate('signup.php', [
    'errors' => $errors,
    'formData' => $formData,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Регистрация",
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
