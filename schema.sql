CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE yeticave;

-- Структура таблицы `bets`

CREATE TABLE `bets` (
  `id` int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `date_create` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `lot_id` int UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE CASCADE
);

-- Структура таблицы `categories`

CREATE TABLE `categories` (
  `id` int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `designation` varchar(100) NOT NULL UNIQUE,
  `symbol_code` varchar(50) NOT NULL UNIQUE
);

-- Структура таблицы `lot`

CREATE TABLE `lots` (
  `id` int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `title` varchar(255) NOT NULL,
  `img` varchar(255),
  `date_end` datetime NOT NULL,
  `initial_price` int UNSIGNED NOT NULL,
  `bet_step` int UNSIGNED NOT NULL,
  `author_id` int UNSIGNED NOT NULL,
  `winner_id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Структура таблицы `user`

CREATE TABLE `users` (
  `id` int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sign_up_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(60) NOT NULL UNIQUE,
  `name` varchar(60) NOT NULL,
  `password` varchar(60) NOT NULL,
  `contacts` text
);
