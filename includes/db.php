<?php
require_once __DIR__ . '/config.php';

//Подключение к базе через PDO, создаётся один раз за запрос.
//Все запросы с данными пользователя идут через подготовленные выражения.
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(503);
            exit('<p style="font-family:sans-serif;padding:24px">Нет подключения к базе данных. '
                . 'Запустите MySQL в панели XAMPP и импортируйте файл sql/sewing_atelier.sql через phpMyAdmin.</p>');
        }
    }
    return $pdo;
}

//Возвращает все строки результата
function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

//Возвращает первую строку или null, если ничего не нашлось
function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

//Для INSERT, UPDATE и DELETE, возвращает число затронутых строк
function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}
