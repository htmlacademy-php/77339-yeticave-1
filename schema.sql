CREATE DATABASE yeticave
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE yeticave;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  designation VARCHAR(100) NOT NULL UNIQUE,
  symbol_code VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sign_up_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  email VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(60) NOT NULL,
  password CHAR(60) NOT NULL,
  contacts VARCHAR(500)
);

CREATE TABLE lots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  date_create TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  img VARCHAR(255),
  initial_price INT UNSIGNED NOT NULL,
  date_end DATETIME NOT NULL,
  bet_step INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  winner_id INT UNSIGNED DEFAULT NULL,
  category_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE bets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  date_create DATETIME DEFAULT CURRENT_TIMESTAMP,
  amount INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  lot_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lot_id) REFERENCES lots(id) ON DELETE CASCADE
);

/* Создание индексов для поиска
CREATE INDEX idx_lots_category_id ON lots(category_id);
CREATE INDEX idx_lots_author_id ON lots(author_id);
CREATE INDEX idx_lots_winner_id ON lots(winner_id);
CREATE INDEX idx_bids_user_id ON rates(user_id);
CREATE INDEX idx_bids_lot_id ON rates(lot_id);
 */
