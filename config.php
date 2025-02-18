<?php
return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => 'root',
        'database' => 'yeticave',
    ],
    'mailer' => [
        'user' => 'test@mail.ru', // Почтовый логин
        'password' => 'kartoshka5', // Почтовый пароль
        'smtp_server' => 'smtp.mail.ru', // SMTP сервер
        'smtp_port' => 465, // Порт для TLS
    ],
    'site' => [
        'base_url' => 'http://localhost:8000'
    ],
    'lots_per_page' => 9,
];
