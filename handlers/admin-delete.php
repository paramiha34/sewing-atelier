<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin-schema.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin.php');
}
check_csrf();

$table = $_POST['table'] ?? '';
$id = (int)($_POST['id'] ?? 0);
if (!isset(ADMIN_TABLES[$table]) || $id <= 0) {
    http_response_code(400);
    exit('Неверный запрос.');
}
$pk = ADMIN_TABLES[$table]['pk'];
if ($table === 'users' && $id === current_user()['id']) {
    flash('error', 'Нельзя удалить собственную учётную запись администратора.');
    redirect('../admin.php?table=users');
}
try {
    db_exec("DELETE FROM $table WHERE $pk = ?", [$id]);
    flash('success', 'Запись №' . $id . ' удалена.');
} catch (PDOException $ex) {
    flash('error', 'Запись №' . $id . ' нельзя удалить: на неё ссылаются другие таблицы. Сначала удалите или измените связанные записи.');
}
redirect('../admin.php?table=' . $table);
