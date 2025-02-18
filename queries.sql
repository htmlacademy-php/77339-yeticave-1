USE yeticave;

INSERT INTO categories (symbol_code, name)
VALUES ('boards', 'Доски и лыжи'),
       ('attachment', 'Крепления'),
       ('boots', 'Ботинки'),
       ('clothing', 'Одежда'),
       ('tools', 'Инструменты'),
       ('other', 'Разное');

INSERT INTO users (name, email, password, contacts)
VALUES ('Олег Яндекс', 'olezha@yandex.ru', 'blu3Ha!r', '+7(999)328-12-32'),
       ('Алёна Вайлдберриз', 'alyonkawb@shop.ru', 'sh0p_tO_live', '+7(983)243-56-00');

INSERT INTO lots (title, description, image_url, start_price, ended_at, rate_step, author_id, category_id)
VALUES ('2014 Rossignol District Snowboard', 'Добавьте описание лота.',
        'img/lot-1.jpg', 10999, '2025-02-21', 100, 1,
        1),
       ('DC Ply Mens 2016/2017 Snowboard', 'Добавьте описание лота.',
        'img/lot-2.jpg', 159999, '2025-02-22', 100, 1,
        1),
       ('Крепления Union Contact Pro 2015 года размер L/XL',
        'Добавьте описание лота.', 'img/lot-3.jpg', 8000,
        '2025-02-23', 100, 2, 2),
       ('Ботинки для сноуборда DC Mutiny Charocal', 'Добавьте описание лота.',
        'img/lot-4.jpg', 10999,
        '2025-02-24', 100, 2, 3),
       ('Куртка для сноуборда DC Mutiny Charocal', 'Добавьте описание лота.',
        'img/lot-5.jpg', 7500,
        '2025-02-25', 100, 1, 4),
       ('Маска Oakley Canopy', 'Добавьте описание лота.', 'img/lot-6.jpg', 5400,
        '2025-02-26', 100, 2, 6);

INSERT INTO rates (amount, user_id, lot_id)
VALUES (11099, 1, 1);
INSERT INTO rates (amount, user_id, lot_id)
VALUES (160099, 2, 2);

# получаем все категории
SELECT *
FROM categories;

# получить самые новые, открытые лоты, каждый лот включает название, стартовую цену, ссылку на изображение, цену, название категории
SELECT l.id,
       l.title,
       l.start_price,
       l.image_url,
       c.name                                 AS category_name,
       COALESCE(MAX(r.amount), l.start_price) AS current_price
FROM lots l
       JOIN categories c ON c.id = l.category_id
       LEFT JOIN rates r ON r.lot_id = l.id
WHERE l.ended_at > NOW()
GROUP BY l.id, l.title, l.start_price, l.image_url, c.name, l.created_at
ORDER BY l.created_at DESC;

# показываем лот по его ID, получаем название категории, к которой принадлежит лот
SELECT l.*, c.name
FROM lots l
       JOIN categories c ON c.id = l.category_id
WHERE l.id = 3;

# обновляем название лота по его идентификатору
UPDATE lots
SET title = 'Снегоступы'
WHERE id = 4;

# получаем список ставок для лота по его идентификатору с сортировкой по дате
SELECT r.id, r.created_at, r.amount, l.title, u.name
FROM rates r
       JOIN lots l ON l.id = r.lot_id
       JOIN users u ON u.id = r.user_id
WHERE l.id = 7
ORDER BY r.created_at DESC;
