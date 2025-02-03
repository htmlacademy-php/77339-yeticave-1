<?php
require_once 'functions/database.php';
require_once 'functions/functions.php';
require_once 'functions/validation.php';

$config = require 'config.php';
$db = connectDb($config);

$isAuth = rand(0, 1);
$userName = "Антон Башко";
