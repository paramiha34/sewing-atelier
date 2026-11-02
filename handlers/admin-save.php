<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/admin-schema.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin.php');
}
check_csrf();

$table = $_POST['table'] ?? '';
if (!isset(ADMIN_TABLES[$table])) {
    http_response_code(400);
    exit('Неизвестная таблица.');
}
$cfg = ADMIN_TABLES[$table];
$pk = $cfg['pk'];
$id = (int)($_POST['id'] ?? 0);
$back = '../admin.php?table=' . $table . ($id ? '&edit=' . $id : '') . '#record-form';

$set = [];
$errors = [];
foreach ($cfg['columns'] as $name => $col) {
    if (!empty($col['auto'])) {
        continue;
    }
    if ($col['type'] === 'password') {
        $pass = $_POST['password'] ?? '';
        if ($pass === '' && $id) {
            //при изменении записи пустое поле пароля значит «оставить прежний»
            continue;
        }
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,64}$/', $pass)) {
            $errors[] = 'Пароль: не менее 8 символов, латинские буквы и цифры.';
            continue;
        }
        $set[$name] = password_hash($pass, PASSWORD_DEFAULT);
        continue;
    }
    $raw = trim((string)($_POST[$name] ?? ''));
    if ($raw === '') {
        if (!empty($col['required'])) {
            $errors[] = 'Поле «' . $col['label'] . '» обязательно.';
        }
        $set[$name] = null;
        continue;
    }
    switch ($col['type']) {
        case 'int':
        case 'decimal':
        case 'fk':
            $num = str_replace([',', ' '], ['.', ''], $raw);
            if (!is_numeric($num) || ($col['type'] !== 'decimal' && (string)(int)$num !== $num)) {
                $errors[] = 'Поле «' . $col['label'] . '» должно быть числом.';
                break;
            }
            if (isset($col['min']) && ($num < $col['min'] || $num > $col['max'])) {
                $errors[] = 'Поле «' . $col['label'] . '»: от ' . $col['min'] . ' до ' . $col['max'] . '.';
                break;
            }
            if ($col['type'] === 'fk') {
                [$rt, $rpk] = $col['ref'];
                if (!db_one("SELECT 1 FROM $rt WHERE $rpk = ?", [(int)$num])) {
                    $errors[] = 'Поле «' . $col['label'] . '»: такой записи нет.';
                    break;
                }
            }
            $set[$name] = $num;
            break;
        case 'date':
            $date = parse_date($raw);
            if (!$date) {
                $errors[] = 'Поле «' . $col['label'] . '»: введите дату в формате дд.мм.гггг.';
                break;
            }
            $set[$name] = $date;
            break;
        case 'email':
            if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Поле «' . $col['label'] . '»: неверный адрес почты.';
                break;
            }
            $set[$name] = mb_strtolower($raw);
            break;
        case 'url':
            if (!filter_var($raw, FILTER_VALIDATE_URL)) {
                $errors[] = 'Поле «' . $col['label'] . '»: неверная ссылка.';
                break;
            }
            $set[$name] = $raw;
            break;
        default:
            if (isset($col['max']) && mb_strlen($raw) > $col['max']) {
                $errors[] = 'Поле «' . $col['label'] . '»: не длиннее ' . $col['max'] . ' символов.';
                break;
            }
            if (isset($col['pattern']) && !preg_match('/' . $col['pattern'] . '/u', $raw)) {
                $errors[] = 'Поле «' . $col['label'] . '»: неверный формат.';
                break;
            }
            $set[$name] = $raw;
    }
}

if ($errors) {
    flash('error', implode(' ', $errors));
    redirect($back);
}

try {
    if ($id) {
        $assign = implode(', ', array_map(fn($c) => "$c = ?", array_keys($set)));
        db_exec("UPDATE $table SET $assign WHERE $pk = ?", [...array_values($set), $id]);
        flash('success', 'Запись №' . $id . ' изменена.');
    } else {
        $cols = implode(', ', array_keys($set));
        $marks = implode(', ', array_fill(0, count($set), '?'));
        db_exec("INSERT INTO $table ($cols) VALUES ($marks)", array_values($set));
        flash('success', 'Запись №' . db()->lastInsertId() . ' добавлена.');
    }
} catch (PDOException $ex) {
    $msg = $ex->errorInfo[1] ?? 0;
    flash('error', $msg == 1062 ? 'Такое значение уже есть в таблице: поле должно быть уникальным.' : 'Не удалось сохранить запись: проверьте связанные данные.');
    redirect($back);
}
redirect('../admin.php?table=' . $table);
