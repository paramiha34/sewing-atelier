-- ============================================================
--  База данных веб-приложения «Швейное ателье»
--  УП 09.01, Парамонов М.М., ВД-1-23
--  Импорт: phpMyAdmin → Импорт → выбрать этот файл → Вперёд
-- ============================================================

SET NAMES utf8mb4;
DROP DATABASE IF EXISTS sewing_atelier;
CREATE DATABASE sewing_atelier CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sewing_atelier;

-- ---------- Справочники ----------

CREATE TABLE roles (
    id_role     INT AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE service_types (
    id_service_type INT AUTO_INCREMENT PRIMARY KEY,
    type_name       VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE order_statuses (
    id_status   INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------- Пользователи ----------

CREATE TABLE users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    id_role       INT NOT NULL,
    last_name     VARCHAR(50) NOT NULL,
    first_name    VARCHAR(50) NOT NULL,
    phone         VARCHAR(18) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_roles FOREIGN KEY (id_role) REFERENCES roles (id_role)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE measurements (
    id_measurement INT AUTO_INCREMENT PRIMARY KEY,
    id_user        INT NOT NULL UNIQUE,
    height         DECIMAL(5,1) NULL,
    head           DECIMAL(5,1) NULL,
    neck           DECIMAL(5,1) NULL,
    shoulder_width DECIMAL(5,1) NULL,
    chest          DECIMAL(5,1) NULL,
    underbust      DECIMAL(5,1) NULL,
    waist          DECIMAL(5,1) NULL,
    arm_length     DECIMAL(5,1) NULL,
    hips           DECIMAL(5,1) NULL,
    wrist          DECIMAL(5,1) NULL,
    inseam         DECIMAL(5,1) NULL,
    foot_length    DECIMAL(5,1) NULL,
    shoe_size      DECIMAL(4,1) NULL,
    product_length DECIMAL(5,1) NULL,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_measurements_users FOREIGN KEY (id_user) REFERENCES users (id_user)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id_review   INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    review_date DATE NOT NULL,
    CONSTRAINT fk_reviews_users FOREIGN KEY (id_user) REFERENCES users (id_user)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Услуги, работы, коллаборации ----------

CREATE TABLE services (
    id_service      INT AUTO_INCREMENT PRIMARY KEY,
    id_service_type INT NOT NULL,
    service_name    VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    price           DECIMAL(10,2) NOT NULL,
    duration_days   INT NOT NULL,
    CONSTRAINT fk_services_types FOREIGN KEY (id_service_type) REFERENCES service_types (id_service_type)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE works (
    id_work         INT AUTO_INCREMENT PRIMARY KEY,
    id_service_type INT NOT NULL,
    title           VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    image           VARCHAR(255) NOT NULL,
    character_url   VARCHAR(255) NULL,
    character_info  TEXT NULL,
    created_at      DATE NOT NULL,
    CONSTRAINT fk_works_types FOREIGN KEY (id_service_type) REFERENCES service_types (id_service_type)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE collaborations (
    id_collaboration INT AUTO_INCREMENT PRIMARY KEY,
    id_service_type  INT NOT NULL,
    title            VARCHAR(100) NOT NULL,
    description      TEXT NULL,
    start_date       DATE NOT NULL,
    end_date         DATE NOT NULL,
    image            VARCHAR(255) NULL,
    image_source     VARCHAR(255) NULL,
    CONSTRAINT fk_collaborations_types FOREIGN KEY (id_service_type) REFERENCES service_types (id_service_type)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- Заказы и фурнитура ----------

CREATE TABLE orders (
    id_order       INT AUTO_INCREMENT PRIMARY KEY,
    id_user        INT NOT NULL,
    id_service     INT NOT NULL,
    id_status      INT NOT NULL,
    order_date     DATE NOT NULL,
    due_date       DATE NOT NULL,
    completed_date DATE NULL,
    total_price    DECIMAL(10,2) NOT NULL,
    description    TEXT NULL,
    reference_url  VARCHAR(255) NULL,
    CONSTRAINT fk_orders_users    FOREIGN KEY (id_user)    REFERENCES users (id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_services FOREIGN KEY (id_service) REFERENCES services (id_service)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_statuses FOREIGN KEY (id_status)  REFERENCES order_statuses (id_status)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE fittings (
    id_fitting   INT AUTO_INCREMENT PRIMARY KEY,
    fitting_name VARCHAR(100) NOT NULL,
    fitting_type VARCHAR(50) NOT NULL,
    color        VARCHAR(30) NULL,
    quantity     INT NOT NULL DEFAULT 0,
    unit         VARCHAR(10) NOT NULL,
    price        DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE order_fittings (
    id_order_fitting INT AUTO_INCREMENT PRIMARY KEY,
    id_order         INT NOT NULL,
    id_fitting       INT NOT NULL,
    quantity         INT NOT NULL,
    CONSTRAINT fk_order_fittings_orders   FOREIGN KEY (id_order)   REFERENCES orders (id_order)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_fittings_fittings FOREIGN KEY (id_fitting) REFERENCES fittings (id_fitting)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
--  Данные
-- ============================================================

INSERT INTO roles (role_name) VALUES ('Администратор'), ('Пользователь');

INSERT INTO service_types (type_name) VALUES
('Пошив'), ('Ремонт'), ('Подгонка по фигуре'), ('Костюм для косплея');

INSERT INTO order_statuses (status_name) VALUES
('Принят'), ('В работе'), ('Примерка'), ('Готов к выдаче'), ('Выполнен'), ('Отменён');

-- Пароли: администратор — Admin2026, клиенты — Client2026
INSERT INTO users (id_role, last_name, first_name, phone, email, password_hash, created_at) VALUES
(1, 'Парамонов', 'Михаил', '+7 (900) 000-00-01', 'admin@atelier.ru',   '$2y$10$/MtcE8XXCJwAQ/X.7vBZeujMkkhK9kyTD6Xiy0mKwjDUsl1NGseOy', '2026-05-25 10:00:00'),
(2, 'Иванова',   'Анна',   '+7 (915) 123-45-67', 'ivanova@mail.ru',    '$2y$10$7jGiMwSEa38fpu9lvF6oyut/xc0HwdjoQJZ6XAMI9WoikxB/OYdfG', '2026-06-02 12:15:00'),
(2, 'Петров',    'Сергей', '+7 (926) 234-56-78', 'petrov@yandex.ru',   '$2y$10$7jGiMwSEa38fpu9lvF6oyut/xc0HwdjoQJZ6XAMI9WoikxB/OYdfG', '2026-06-10 18:40:00'),
(2, 'Ким',       'Алина',  '+7 (903) 345-67-89', 'kim.cos@gmail.com',  '$2y$10$7jGiMwSEa38fpu9lvF6oyut/xc0HwdjoQJZ6XAMI9WoikxB/OYdfG', '2026-07-01 09:05:00'),
(2, 'Смирнова',  'Ольга',  '+7 (916) 456-78-90', 'smirnova@mail.ru',   '$2y$10$7jGiMwSEa38fpu9lvF6oyut/xc0HwdjoQJZ6XAMI9WoikxB/OYdfG', '2026-08-14 14:30:00'),
(2, 'Орлов',     'Даниил', '+7 (999) 567-89-01', 'orlov.d@yandex.ru',  '$2y$10$7jGiMwSEa38fpu9lvF6oyut/xc0HwdjoQJZ6XAMI9WoikxB/OYdfG', '2026-09-20 20:10:00');

INSERT INTO measurements (id_user, height, head, neck, shoulder_width, chest, underbust, waist, arm_length, hips, wrist, inseam, foot_length, shoe_size, product_length) VALUES
(2, 168.0, 55.0, 33.0, 38.0, 88.0, 74.0, 68.0, 60.0, 96.0, 15.0, 78.0, 24.0, 38.0, 105.0),
(3, 182.0, 58.0, 40.0, 46.0, 102.0, NULL, 86.0, 64.0, 100.0, 18.0, 84.0, 27.5, 43.0, 75.0),
(4, 160.0, 54.0, 31.0, 36.0, 82.0, 70.0, 62.0, 57.0, 90.0, 14.5, 74.0, 23.0, 36.0, 95.0),
(5, 172.0, 56.0, 34.0, 39.0, 94.0, 79.0, 76.0, 61.0, 102.0, 15.5, 80.0, 24.5, 39.0, 110.0),
(6, 176.0, 57.0, 39.0, 45.0, 98.0, NULL, 80.0, 63.0, 98.0, 17.5, 81.0, 27.0, 42.0, 72.0);

INSERT INTO services (id_service_type, service_name, description, price, duration_days) VALUES
(1, 'Пошив платья',               'Платье по индивидуальным меркам из ткани заказчика или ателье', 9500.00, 14),
(1, 'Пошив брюк',                 'Классические или повседневные брюки по меркам',                 4500.00, 7),
(1, 'Пошив пальто',               'Демисезонное или зимнее пальто с подкладкой',                 18000.00, 21),
(2, 'Замена молнии',              'Замена молнии в куртке, брюках или платье',                     900.00, 2),
(2, 'Ремонт подкладки',           'Восстановление или полная замена подкладки',                   1800.00, 4),
(3, 'Подшив брюк',                'Укорачивание брюк или джинсов с сохранением фабричного шва',    600.00, 1),
(3, 'Ушив по фигуре',             'Подгонка платья, жакета или рубашки по фигуре',                2200.00, 3),
(4, 'Костюм персонажа (базовый)', 'Костюм по референсам без сложного реквизита',                 14000.00, 21),
(4, 'Костюм персонажа (сложный)', 'Многослойный костюм с элементами доспехов и аксессуарами',    32000.00, 35),
(4, 'Реквизит и аксессуары',      'Пояса, накидки, броши, головные уборы к образу',               3500.00, 7);

INSERT INTO works (id_service_type, title, description, image, character_url, character_info, created_at) VALUES
(1, 'Вечернее платье из шёлка',   'Платье в пол с лифом-«сердечком» на тонких бретелях и юбкой-клёш', 'evening-dress.svg', NULL, NULL, '2026-06-15'),
(1, 'Шерстяное пальто',           'Двубортное пальто прямого кроя с подкладкой из вискозы',          'wool-coat.svg',     NULL, NULL, '2026-09-30'),
(3, 'Подгонка свадебного платья', 'Ушив лифа по боковым швам и укорачивание подола',                  'wedding-fit.svg',   NULL, NULL, '2026-08-02'),
(4, 'Тето Касанэ',  'Готическое красное платье с кружевными рукавами и оборками, бант-галстук и корсетный пояс; парик с фирменными «буравчиками» уложен по референсу', 'kasane-teto.jpg',
    'https://shikimori.io/characters/24552-teto-kasane', 'Певица с более милым характером, чем у Хацунэ Мику: весёлая, нетерпеливая и подчас озорная. Тето любит быть в центре внимания, поэтому иногда кажется самовлюблённой. Очень любит хлеб и может выйти из себя, если рядом едят французский багет и не делятся.', '2026-10-28'),
(4, 'Фрирен',       'Белая накидка и платье с золотой каймой, полосатая вставка и пояс; посох с красным камнем сделан из лёгких материалов', 'frieren.jpg',
    'https://shikimori.io/characters/184947-frieren', 'Представительница расы эльфов. Всегда спокойная, Фрирен кажется окружающим холодной и отчуждённой, однако обладает чувством юмора. Желает лучше понимать людей, несмотря на скоротечность их жизни. Ленива и неуклюжа в простых бытовых делах, поэтому во многом полагается на товарищей.', '2026-10-12');

INSERT INTO collaborations (id_service_type, title, description, start_date, end_date, image, image_source) VALUES
(4, 'Хэллоуин в ателье',          'Скидка 15 % на костюмы персонажей и тематический реквизит', '2026-10-01', '2026-11-03', 'halloween.png', NULL),
(4, 'Герои манги и ранобэ',       'Коллекция образов по мотивам популярных произведений',       '2026-09-01', '2026-12-31', 'manga-heroes.png', 'https://imgur.com/a/anime-vector-wallpapers-2-1920x1080-jxdWS'),
(1, 'Новогодний образ',           'Нарядные платья и костюмы к праздникам',                     '2026-12-01', '2027-01-10', 'new-year.png', NULL);

INSERT INTO fittings (fitting_name, fitting_type, color, quantity, unit, price) VALUES
('Молния потайная 20 см',      'Молния',   'Чёрный',     40, 'шт.',  45.00),
('Молния разъёмная 70 см',     'Молния',   'Тёмно-синий', 12, 'шт.', 180.00),
('Пуговица костюмная 20 мм',   'Пуговица', 'Бежевый',   150, 'шт.',  15.00),
('Нитки армированные 200 м',   'Нитки',    'Бордовый',   25, 'шт.',  90.00),
('Лента атласная 25 мм',       'Лента',    'Золотой',    30, 'м',    35.00),
('Кнопки металлические 15 мм', 'Кнопка',   'Серебряный',  0, 'шт.',  20.00),
('Пряжка декоративная',        'Пряжка',   'Бронзовый',   8, 'шт.', 250.00),
('EVA-пена 5 мм, лист',        'Материал', 'Серый',       0, 'шт.', 600.00);

-- Даты заказов отсчитываются от текущей даты, чтобы представления
-- «текущие заказы» и «завершённые за месяц» всегда показывали данные
INSERT INTO orders (id_user, id_service, id_status, order_date, due_date, completed_date, total_price, description, reference_url) VALUES
(2, 1, 5, CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 6 DAY,  CURDATE() - INTERVAL 5 DAY,  9500.00, 'Платье-футляр из ткани заказчика', NULL),
(3, 6, 5, CURDATE() - INTERVAL 9 DAY,  CURDATE() - INTERVAL 8 DAY,  CURDATE() - INTERVAL 8 DAY,   600.00, 'Подшить две пары брюк', NULL),
(4, 9, 2, CURDATE() - INTERVAL 14 DAY, CURDATE() + INTERVAL 21 DAY, NULL,                       32000.00, 'Костюм рыцаря к фестивалю', 'https://myanimelist.net/character/'),
(4, 10, 1, CURDATE() - INTERVAL 2 DAY, CURDATE() + INTERVAL 5 DAY,  NULL,                        3500.00, 'Пояс и брошь к костюму', NULL),
(5, 7, 3, CURDATE() - INTERVAL 4 DAY,  CURDATE() + INTERVAL 1 DAY,  NULL,                        2200.00, 'Ушить жакет по талии', NULL),
(6, 4, 4, CURDATE() - INTERVAL 3 DAY,  CURDATE() - INTERVAL 1 DAY,  NULL,                         900.00, 'Молния в зимней куртке', NULL),
(2, 3, 2, CURDATE() - INTERVAL 7 DAY,  CURDATE() + INTERVAL 14 DAY, NULL,                       18000.00, 'Пальто из шерсти, с капюшоном', NULL),
(3, 5, 5, CURDATE() - INTERVAL 70 DAY, CURDATE() - INTERVAL 66 DAY, CURDATE() - INTERVAL 66 DAY, 1800.00, 'Замена подкладки в пиджаке', NULL),
(6, 8, 6, CURDATE() - INTERVAL 30 DAY, CURDATE() - INTERVAL 9 DAY,  NULL,                       14000.00, 'Заказ отменён клиентом', NULL);

INSERT INTO order_fittings (id_order, id_fitting, quantity) VALUES
(1, 1, 1), (1, 4, 1), (3, 5, 4), (3, 7, 2), (4, 7, 1), (6, 2, 1), (7, 3, 6);

INSERT INTO reviews (id_user, rating, review_text, review_date) VALUES
(2, 5, 'Платье сидит идеально, мастер учла все пожелания. Обязательно вернусь за пальто.', CURDATE() - INTERVAL 4 DAY),
(3, 5, 'Подшили брюки за один день, шов как фабричный.',                                  CURDATE() - INTERVAL 7 DAY),
(4, 4, 'Помогли продумать костюм по референсам, жду примерку.',                           CURDATE() - INTERVAL 10 DAY),
(5, 5, 'Удобно, что мерки сохранены в личном кабинете — повторно не пришлось ничего снимать.', CURDATE() - INTERVAL 2 DAY);

-- ============================================================
--  Представления (запросы темы №18)
-- ============================================================

-- 1. Найти заказы по типу услуги (в админ-панели фильтруется по type_name)
CREATE VIEW v_orders_by_service_type AS
SELECT st.type_name, o.id_order, s.service_name,
       CONCAT(u.last_name, ' ', u.first_name) AS client,
       o.order_date, o.due_date, os.status_name, o.total_price
FROM orders o
JOIN services s        ON s.id_service = o.id_service
JOIN service_types st  ON st.id_service_type = s.id_service_type
JOIN users u           ON u.id_user = o.id_user
JOIN order_statuses os ON os.id_status = o.id_status;

-- 2. Показать завершённые заказы за месяц (последние 30 дней)
CREATE VIEW v_completed_orders_month AS
SELECT o.id_order, s.service_name,
       CONCAT(u.last_name, ' ', u.first_name) AS client,
       o.order_date, o.completed_date, o.total_price
FROM orders o
JOIN services s        ON s.id_service = o.id_service
JOIN users u           ON u.id_user = o.id_user
JOIN order_statuses os ON os.id_status = o.id_status
WHERE os.status_name = 'Выполнен'
  AND o.completed_date >= CURDATE() - INTERVAL 1 MONTH;

-- 3. Найти заказы по клиенту (в админ-панели фильтруется по client / email)
CREATE VIEW v_orders_by_client AS
SELECT CONCAT(u.last_name, ' ', u.first_name) AS client, u.email, u.phone,
       o.id_order, s.service_name, o.order_date, os.status_name, o.total_price
FROM orders o
JOIN users u           ON u.id_user = o.id_user
JOIN services s        ON s.id_service = o.id_service
JOIN order_statuses os ON os.id_status = o.id_status;

-- 4. Показать текущие заказы (не выполнены и не отменены)
CREATE VIEW v_current_orders AS
SELECT o.id_order, s.service_name,
       CONCAT(u.last_name, ' ', u.first_name) AS client,
       o.order_date, o.due_date, os.status_name, o.total_price
FROM orders o
JOIN services s        ON s.id_service = o.id_service
JOIN users u           ON u.id_user = o.id_user
JOIN order_statuses os ON os.id_status = o.id_status
WHERE os.status_name NOT IN ('Выполнен', 'Отменён');

-- 5. Показать фурнитуру в наличии
CREATE VIEW v_fittings_in_stock AS
SELECT id_fitting, fitting_name, fitting_type, color, quantity, unit, price
FROM fittings
WHERE quantity > 0;
