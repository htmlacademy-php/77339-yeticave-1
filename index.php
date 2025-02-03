<?php
require_once 'data.php';

$lots = getLots($db);
$categories = getCategories($db);

$content = includeTemplate('main.php', [
	'categories' => $categories,
	'lots' => $lots
]);

$layout = includeTemplate('layout.php', [
	'title' => 'YetiCave - Главная',
	'content' => $content,
	'userName' => $userName,
	'auth' => $isAuth,
	'categories' => $categories
]);

print($layout);
