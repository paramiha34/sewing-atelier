<?php
require_once __DIR__ . '/../includes/helpers.php';
$user = current_user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}
check_csrf();

$idService = (int)($_POST['id_service'] ?? 0);
$description = trim($_POST['description'] ?? '');
$due = $_POST['due_date'] ?? '';
$ref = trim($_POST['reference_url'] ?? '');

$service = db_one('SELECT id_service, price FROM services WHERE id_service = ?', [$idService]);
$dueDate = parse_date(trim($due));
$dueTs = $dueDate ? strtotime($dueDate) : false;
$error = null;
if (!$service) {
    $error = 'Выберите услугу из списка.';
} elseif (mb_strlen($description) < 10 || mb_strlen($description) > 1000) {
    $error = 'Опишите заказ: от 10 до 1000 символов.';
} elseif (!$dueTs || $dueTs < strtotime('+3 days', strtotime('today'))) {
    $error = 'Укажите дату готовности в формате дд.мм.гггг, не раньше чем через 3 дня.';
} elseif ($ref !== '' && !filter_var($ref, FILTER_VALIDATE_URL)) {
    $error = 'Ссылка на референс указана неверно.';
}
if ($error) {
    flash('error', $error);
    redirect('../account.php?service=' . $idService . '#new-order');
}

$status = db_one("SELECT id_status FROM order_statuses WHERE status_name = 'Принят'");
db_exec(
    'INSERT INTO orders (id_user, id_service, id_status, order_date, due_date, total_price, description, reference_url)
     VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)',
    [$user['id'], $idService, $status['id_status'], date('Y-m-d', $dueTs), $service['price'], $description, $ref ?: null]
);
flash('success', 'Заказ №' . db()->lastInsertId() . ' принят. Мастер свяжется с вами в течение рабочего дня.');
redirect('../account.php');
