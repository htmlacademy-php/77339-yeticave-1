<?php
require_once 'data.php';

$lots = getLots($db);
$categories = getCategories($db);
<<<<<<< Updated upstream
  
=======

>>>>>>> Stashed changes
$content = include_template('main.php', [
	'categories' => $categories,
	'lots' => $lots
]);
$layout = include_template('layout.php', [
	'title' => 'YetiCave - Главная',
	'content' => $content,
	'userName' => $userName,
	'auth' => $isAuth,
	'categories' => $categories
]);

print($layout);
