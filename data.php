<?php
require_once 'functions.php';

$config = require 'config.php';
$db = connectDb($config);

$isAuth = rand(0, 1);
$userName = "Антон Башко";
