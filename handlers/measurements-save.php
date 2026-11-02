<?php
require_once __DIR__ . '/../includes/helpers.php';
$user = current_user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}
check_csrf();

require_once __DIR__ . '/../includes/measures.php';
$values = [];
foreach (MEASURES as $name => [$label, $min, $max]) {
    $raw = str_replace(',', '.', trim($_POST[$name] ?? ''));
    if ($raw === '') {
        $values[$name] = null;
        continue;
    }
    if (!is_numeric($raw) || $raw < $min || $raw > $max) {
        flash('error', 'Проверьте мерки: значения должны быть числами в допустимых пределах.');
        redirect('../account.php?tab=measurements');
    }
    $values[$name] = round((float)$raw, 1);
}
if (count(array_filter($values, fn($v) => $v !== null)) === 0) {
    flash('error', 'Заполните хотя бы одну мерку.');
    redirect('../account.php?tab=measurements');
}

$exists = db_one('SELECT id_measurement FROM measurements WHERE id_user = ?', [$user['id']]);
$columns = array_keys($values);
$params = array_values($values);
$params[] = $user['id'];
if ($exists) {
    $assign = implode(', ', array_map(fn($c) => "$c = ?", $columns));
    db_exec("UPDATE measurements SET $assign WHERE id_user = ?", $params);
} else {
    db_exec('INSERT INTO measurements (' . implode(', ', $columns) . ', id_user) VALUES (' . implode(', ', array_fill(0, count($columns) + 1, '?')) . ')', $params);
}
flash('success', 'Мерки сохранены.');
redirect('../account.php?tab=measurements');
