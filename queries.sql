USE yeticave;

INSERT INTO categories (symbol_code, designation) VALUES
('boards', 'Доски и лыжи'),
('attachment', 'Крепления'),
('boots', 'Ботинки'),
('clothing', 'Одежда'),
('tools', 'Инструменты'),
('other', 'Разное');

INSERT INTO users (name, email, password, contacts) VALUES
('Олег Яндекс', 'olezha@yandex.ru', 'blu3Ha!r', '+7(999)328-12-32'),
('Алёна Вайлдберриз', 'alyonkawb@shop.ru', 'sh0p_tO_live', '+7(983)243-56-00');

INSERT INTO lots (title, img, initial_price, date_end, bet_step, author_id, category_id) VALUES
('2014 Rossignol District Snowboard', 'img/lot-1.jpg', 10999, '2025-02-28', 100, 1, 1),
('DC Ply Mens 2016/2017 Snowboard', 'img/lot-2.jpg', 159999, '2025-02-18', 100, 1, 1),
('Крепления Union Contact Pro 2015 года размер L/XL', 'img/lot-3.jpg', 8000, '2025-02-15', 100, 2, 2),
('Ботинки для сноуборда DC Mutiny Charocal', 'img/lot-4.jpg', 10999, '2025-02-20', 100, 2, 3),
('Куртка для сноуборда DC Mutiny Charocal', 'img/lot-5.jpg', 7500, '2025-02-14', 100, 1, 4),
('Маска Oakley Canopy', 'img/lot-6.jpg', 5400, '2025-02-24', 100, 2, 6);

INSERT INTO bets (amount, user_id, lot_id) VALUES
(1000, 1, 1),
(2000, 2, 3);

#получаем все категории
SELECT * FROM categories;

#получаем самые новые, открытые лоты
SELECT l.id, l.title, l.initial_price, l.img, c.designation, b.amount
FROM lots l
       JOIN categories c ON c.id = l.category_id
       LEFT JOIN bets b ON b.lot_id = l.id
WHERE l.date_end > NOW()
GROUP BY l.id, l.title, l.initial_price, l.img, c.designation, b.amount
ORDER BY l.date_create DESC;

#показываем лот по ID
SELECT l.id, c.designation
FROM lots l
       JOIN categories c ON c.id = l.category_id
WHERE l.id = 1;

#обновление названия лота по ID
UPDATE lots
SET title = 'Снегоступы'
WHERE id = 4;

#получаем список ставок для лота по ID с сортировкой по дате
SELECT b.id, b.date_create, b.amount, l.title, u.name
FROM bets b
       JOIN lots l ON l.id = b.lot_id
       JOIN users u ON u.id = b.user_id
WHERE l.id = 3
ORDER BY b.date_create DESC;
