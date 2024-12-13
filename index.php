<?php
require_once 'helpers.php';
require_once 'functions.php';
require_once 'data.php';

$content = include_template('main.php', ['categories' => $categories, 'lots' => $lots]);
$layout = include_template('layout.php', ['content' => $content, 'title' => 'YetiCave - Главная', 'userName' => $userName]);

print($layout);
?>