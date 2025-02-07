<?php
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once 'functions/template.php';
require_once 'functions/validators.php';
require_once 'functions/db.php';

$config = require 'config.php';
$db = connectDb($config);

$isAuth = isUserAuthenticated($db);
$userName = $isAuth ? $_SESSION['user']['designation'] : '';
