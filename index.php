<?php
$userName = "Антон Башко";
$categories = ["Доски и лыжи", "Крепления", "Ботинки", "Одежда", "Инструменты", "Разное"];
$lots = [
    [
        'name' => '2014 Rossignol District Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 10999,
        'pic' => 'img/lot-1.jpg'    ],
    [
        'name' => 'DC Ply Mens 2016/2017 Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 159999,
        'pic' => 'img/lot-2.jpg'
    ],
    [
        'name' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'category' => 'Крепления',
        'price' => 8000,
        'pic' => 'img/lot-3.jpg'
    ],
    [
        'name' => 'Ботинки для сноуборда DC Mutiny Charocal',
        'category' => 'Ботинки',
        'price' => 10999,
        'pic' => 'img/lot-4.jpg'
    ],
    [
        'name' => 'Куртка для сноуборда DC Mutiny Charocal',
        'category' => 'Одежда',
        'price' => 7500,
        'pic' => 'img/lot-5.jpg'
    ],
    [
        'name' => 'Маска Oakley Canopy',
        'category' => 'Разное',
        'price' => 5400,
        'pic' => 'img/lot-6.jpg'
    ]
];

function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

function format(float $amount): string {
    $amount = ceil($amount);
    if ($amount >= 1000) {
        $formattedAmount = number_format($amount, 0, '', ' ');
    } else {
        $formattedAmount = (string)$amount;
    }
    return $formattedAmount . ' ₽';
}

$content = include_template('main.php', ['categories' => $categories, 'lots' => $lots]);
$layout = include_template('layout.php', ['content' => $content, 'title' => 'YetiCave - Главная', 'userName' => $userName]);

print($layout);
?>