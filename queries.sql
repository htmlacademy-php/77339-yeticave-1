USE yeticave;

INSERT INTO bets (amount, user_id, lot_id) VALUES
(1000, 1, 1),
(2500, 2, 2);

INSERT INTO categories (designation, symbol_code) VALUES
('Лыжи и крепления', 'boards_and_skies'),
('Крепления', 'fasteners'),
('Ботинки', 'boots'),
('Одежда', 'clothes'),
('Инструменты', 'tools'),
('Разное', 'other');


INSERT INTO lots (title, img, initial_price, date_end, bet, author_id, category_id) VALUES
('2014 Rossignol District Snowboard', 'uploads/lot-1.jpg', 10999, '2025-01-01', 100, 1,1),
('DC Ply Mens 2016/2017 Snowboard', 'uploads/lot-2.jpg', 159999, '2024-12-31', 100, 1,1),
('Крепления Union Contact Pro 2015 года размер L/XL', 'uploads/lot-3.jpg', 8000,'2024-12-30', 100, 2, 2),
('Ботинки для сноуборда DC Mutiny Charocal', 'uploads/lot-4.jpg', 10999,'2024-12-28', 100, 2, 3),
('Куртка для сноуборда DC Mutiny Charocal', 'uploads/lot-5.jpg', 7500,'2024-12-29', 100, 1, 4),
('Маска Oakley Canopy', 'uploads/lot-6.jpg', 5400, '2024-12-30', 100, 2, 6);

INSERT INTO users (name, email, password, contacts) VALUES
('Артём Лебедев', 'artem_lebedev@tema.ru', 'blu3Ha!r', '+7(999)328-12-32'),
('Татьяна Вайлдберриз', 'tankawb@gmail.ru', 'sh0p_tO_live', '+7(983)243-56-00');

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