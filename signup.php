<?php

require_once 'data.php';

/** @var mysqli $dbn */
/** @var bool $isAuth */
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
            $errors['database'] = 'Ошибка. Попробуйте позже.';
        }
    }
}

$pageContent = includeTemplate('sign-up.php', [
    'errors' => $errors,
    'formData' => $formData,
]);

$layoutContent = includeTemplate('layout.php', [
    'content' => $pageContent,
    'title' => "Регистрация",
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
]);

print($layoutContent);
