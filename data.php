<?php
session_start();

date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'functions/template.php';
require_once 'functions/validators.php';
require_once 'functions/db.php';
require_once 'functions/file.php';
require_once 'functions/form.php';
require_once 'functions/email.php';

$config = require 'config.php';
$db = dbConnect($config);
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$categories = getCategories($db);

if ($categoryId !== null && !isCategoryExists($db, $categoryId)) {
    http_response_code(404);
    header('Location: /404.php');
}

$pageItems = $config['lots_per_page'];
$curPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$paginationData = getPaginationData($db, $categoryId, $pageItems, $curPage);

if ($paginationData['pagesCount'] === 1) {
    $pagination = '';
} else {
    $pagination = includeTemplate('pagination.php', [
        'pages' => $paginationData['pages'],
        'pagesCount' => $paginationData['pagesCount'],
        'curPage' => $curPage,
        'prevPageUrl' => $paginationData['prevPageUrl'],
        'nextPageUrl' => $paginationData['nextPageUrl']
    ]);
}

$user = getUserData($db);
$userName = $user ? $user['name'] : '';
$userId = $user ? $user['id'] : '';
