<?php
require_once 'helpers.php';
require_once 'functions.php';

$content = include_template('main.php', ['categories' => $categories, 'lots' => $lots]);
$layout = include_template('layout.php', ['content' => $content, 'title' => 'YetiCave - Главная', 'userName' => $userName]);
$con = mysql_connect('77339-yeticave-1', 'root', 'root', 'yeticave');
mysqli_set_charset($con, 'utf8');

$lots_sql = 'SELECT l.id, l.title, l.initial_price, l.img, c.designation, b.amount
FROM lots l
	JOIN categories c ON c.id = l.category_id
	LEFT JOIN bets b ON b.lot_id = l.id
WHERE l.date_end > NOW()
GROUP BY l.id, l.title, l.initial_price, l.img, c.designation, b.amount
ORDER BY l.date_create DESC;';

$lots_reult = mysqli_query($con, $lots_sql);

if (!$lots_result) {
	$error = mysqli_error($con);
	print('Ошибка SQL:' . $error);
}

$lots = mysqli_fetch_all($, MYSQLI_ASSOC);

$categories_sql = 'SELECT *FROM categories;';
$categories_result = mysqli_query($con, $categories_sql);

if (!$categories_result) {
	$error = mysqli_error($con);
	print('Ошибка SQL:' . $error);
}

$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

print($layout);
?>