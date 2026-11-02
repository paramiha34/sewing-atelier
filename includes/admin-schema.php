<?php
//Описание таблиц для админ-панели. Имена таблиц и столбцов берутся только отсюда,
//поэтому в SQL-запрос не может попасть ничего из адресной строки.
//Типы полей: text, textarea, int, decimal, date, datetime, email, url, fk, password
const ADMIN_TABLES = [
    'roles' => ['title' => 'Роли', 'pk' => 'id_role', 'columns' => [
        'role_name' => ['label' => 'Название роли', 'type' => 'text', 'required' => true, 'max' => 30],
    ]],
    'users' => ['title' => 'Пользователи', 'pk' => 'id_user', 'columns' => [
        'id_role'       => ['label' => 'Роль', 'type' => 'fk', 'ref' => ['roles', 'id_role', 'role_name'], 'required' => true],
        'last_name'     => ['label' => 'Фамилия', 'type' => 'text', 'required' => true, 'max' => 50],
        'first_name'    => ['label' => 'Имя', 'type' => 'text', 'required' => true, 'max' => 50],
        'phone'         => ['label' => 'Телефон', 'type' => 'text', 'required' => true, 'max' => 18, 'pattern' => '^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$'],
        'email'         => ['label' => 'Почта', 'type' => 'email', 'required' => true, 'max' => 100],
        'password_hash' => ['label' => 'Пароль', 'type' => 'password'],
        'created_at'    => ['label' => 'Дата регистрации', 'type' => 'datetime', 'auto' => true],
    ]],
    'measurements' => ['title' => 'Мерки', 'pk' => 'id_measurement', 'columns' => [
        'id_user'        => ['label' => 'Клиент', 'type' => 'fk', 'ref' => ['users', 'id_user', "CONCAT(last_name, ' ', first_name)"], 'required' => true],
        'height'         => ['label' => 'Рост', 'type' => 'decimal'],
        'head'           => ['label' => 'Голова', 'type' => 'decimal'],
        'neck'           => ['label' => 'Шея', 'type' => 'decimal'],
        'shoulder_width' => ['label' => 'Плечи', 'type' => 'decimal'],
        'chest'          => ['label' => 'Грудь', 'type' => 'decimal'],
        'underbust'      => ['label' => 'Под грудью', 'type' => 'decimal'],
        'waist'          => ['label' => 'Талия', 'type' => 'decimal'],
        'arm_length'     => ['label' => 'Длина руки', 'type' => 'decimal'],
        'hips'           => ['label' => 'Бёдра', 'type' => 'decimal'],
        'wrist'          => ['label' => 'Кисть', 'type' => 'decimal'],
        'inseam'         => ['label' => 'Длина ноги', 'type' => 'decimal'],
        'foot_length'    => ['label' => 'Стопа', 'type' => 'decimal'],
        'shoe_size'      => ['label' => 'Размер обуви', 'type' => 'decimal'],
        'product_length' => ['label' => 'Длина изделия', 'type' => 'decimal'],
        'updated_at'     => ['label' => 'Изменено', 'type' => 'datetime', 'auto' => true],
    ]],
    'reviews' => ['title' => 'Отзывы', 'pk' => 'id_review', 'columns' => [
        'id_user'     => ['label' => 'Клиент', 'type' => 'fk', 'ref' => ['users', 'id_user', "CONCAT(last_name, ' ', first_name)"], 'required' => true],
        'rating'      => ['label' => 'Оценка', 'type' => 'int', 'required' => true, 'min' => 1, 'max' => 5],
        'review_text' => ['label' => 'Текст', 'type' => 'textarea', 'required' => true],
        'review_date' => ['label' => 'Дата', 'type' => 'date', 'required' => true],
    ]],
    'service_types' => ['title' => 'Типы услуг', 'pk' => 'id_service_type', 'columns' => [
        'type_name' => ['label' => 'Название типа', 'type' => 'text', 'required' => true, 'max' => 50],
    ]],
    'services' => ['title' => 'Услуги', 'pk' => 'id_service', 'columns' => [
        'id_service_type' => ['label' => 'Тип', 'type' => 'fk', 'ref' => ['service_types', 'id_service_type', 'type_name'], 'required' => true],
        'service_name'    => ['label' => 'Название', 'type' => 'text', 'required' => true, 'max' => 100],
        'description'     => ['label' => 'Описание', 'type' => 'textarea'],
        'price'           => ['label' => 'Цена, ₽', 'type' => 'decimal', 'required' => true, 'min' => 0, 'max' => 1000000],
        'duration_days'   => ['label' => 'Срок, дней', 'type' => 'int', 'required' => true, 'min' => 1, 'max' => 365],
    ]],
    'works' => ['title' => 'Примеры работ', 'pk' => 'id_work', 'columns' => [
        'id_service_type' => ['label' => 'Тип', 'type' => 'fk', 'ref' => ['service_types', 'id_service_type', 'type_name'], 'required' => true],
        'title'           => ['label' => 'Название', 'type' => 'text', 'required' => true, 'max' => 100],
        'description'     => ['label' => 'Описание', 'type' => 'textarea'],
        'image'           => ['label' => 'Файл изображения', 'type' => 'text', 'required' => true, 'max' => 255],
        'character_url'   => ['label' => 'Персонаж на Shikimori', 'type' => 'url'],
        'created_at'      => ['label' => 'Дата', 'type' => 'date', 'required' => true],
    ]],
    'collaborations' => ['title' => 'Коллаборации', 'pk' => 'id_collaboration', 'columns' => [
        'id_service_type' => ['label' => 'Тип', 'type' => 'fk', 'ref' => ['service_types', 'id_service_type', 'type_name'], 'required' => true],
        'title'           => ['label' => 'Название', 'type' => 'text', 'required' => true, 'max' => 100],
        'description'     => ['label' => 'Описание', 'type' => 'textarea'],
        'start_date'      => ['label' => 'Начало', 'type' => 'date', 'required' => true],
        'end_date'        => ['label' => 'Окончание', 'type' => 'date', 'required' => true],
        'image'           => ['label' => 'Файл изображения', 'type' => 'text', 'max' => 255],
        'image_source'    => ['label' => 'Источник изображения', 'type' => 'url'],
    ]],
    'order_statuses' => ['title' => 'Статусы заказов', 'pk' => 'id_status', 'columns' => [
        'status_name' => ['label' => 'Статус', 'type' => 'text', 'required' => true, 'max' => 30],
    ]],
    'orders' => ['title' => 'Заказы', 'pk' => 'id_order', 'columns' => [
        'id_user'        => ['label' => 'Клиент', 'type' => 'fk', 'ref' => ['users', 'id_user', "CONCAT(last_name, ' ', first_name)"], 'required' => true],
        'id_service'     => ['label' => 'Услуга', 'type' => 'fk', 'ref' => ['services', 'id_service', 'service_name'], 'required' => true],
        'id_status'      => ['label' => 'Статус', 'type' => 'fk', 'ref' => ['order_statuses', 'id_status', 'status_name'], 'required' => true],
        'order_date'     => ['label' => 'Дата заказа', 'type' => 'date', 'required' => true],
        'due_date'       => ['label' => 'Срок', 'type' => 'date', 'required' => true],
        'completed_date' => ['label' => 'Завершён', 'type' => 'date'],
        'total_price'    => ['label' => 'Стоимость, ₽', 'type' => 'decimal', 'required' => true, 'min' => 0, 'max' => 1000000],
        'description'    => ['label' => 'Описание', 'type' => 'textarea'],
        'reference_url'  => ['label' => 'Референс', 'type' => 'url'],
    ]],
    'fittings' => ['title' => 'Фурнитура', 'pk' => 'id_fitting', 'columns' => [
        'fitting_name' => ['label' => 'Название', 'type' => 'text', 'required' => true, 'max' => 100],
        'fitting_type' => ['label' => 'Тип', 'type' => 'text', 'required' => true, 'max' => 50],
        'color'        => ['label' => 'Цвет', 'type' => 'text', 'max' => 30],
        'quantity'     => ['label' => 'Количество', 'type' => 'int', 'required' => true, 'min' => 0, 'max' => 100000],
        'unit'         => ['label' => 'Ед. изм.', 'type' => 'text', 'required' => true, 'max' => 10],
        'price'        => ['label' => 'Цена, ₽', 'type' => 'decimal', 'required' => true, 'min' => 0, 'max' => 1000000],
    ]],
    'order_fittings' => ['title' => 'Фурнитура заказов', 'pk' => 'id_order_fitting', 'columns' => [
        'id_order'   => ['label' => 'Заказ', 'type' => 'fk', 'ref' => ['orders', 'id_order', "CONCAT('№', id_order)"], 'required' => true],
        'id_fitting' => ['label' => 'Фурнитура', 'type' => 'fk', 'ref' => ['fittings', 'id_fitting', 'fitting_name'], 'required' => true],
        'quantity'   => ['label' => 'Количество', 'type' => 'int', 'required' => true, 'min' => 1, 'max' => 10000],
    ]],
];

//Представления только для просмотра, filter - столбец, по которому можно отобрать строки
const ADMIN_VIEWS = [
    'v_orders_by_service_type' => ['title' => 'Заказы по типу услуги', 'filter' => 'type_name', 'filter_label' => 'Тип услуги', 'options' => 'SELECT type_name FROM service_types ORDER BY id_service_type'],
    'v_completed_orders_month' => ['title' => 'Завершённые за месяц'],
    'v_orders_by_client'       => ['title' => 'Заказы по клиенту', 'filter' => 'client', 'filter_label' => 'Клиент', 'options' => "SELECT DISTINCT CONCAT(u.last_name, ' ', u.first_name) FROM users u JOIN orders o ON o.id_user = u.id_user ORDER BY 1"],
    'v_current_orders'         => ['title' => 'Текущие заказы'],
    'v_fittings_in_stock'      => ['title' => 'Фурнитура в наличии'],
];

const VIEW_COLUMNS = [
    'type_name' => 'Тип услуги', 'id_order' => '№ заказа', 'service_name' => 'Услуга', 'client' => 'Клиент',
    'order_date' => 'Дата заказа', 'due_date' => 'Срок', 'status_name' => 'Статус', 'total_price' => 'Стоимость',
    'completed_date' => 'Завершён', 'email' => 'Почта', 'phone' => 'Телефон', 'id_fitting' => '№',
    'fitting_name' => 'Название', 'fitting_type' => 'Тип', 'color' => 'Цвет', 'quantity' => 'Количество',
    'unit' => 'Ед.', 'price' => 'Цена',
];
