<?php
require_once __DIR__ . '/../includes/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
}
check_csrf();

$data = [
    'last_name'  => trim($_POST['last_name'] ?? ''),
    'first_name' => trim($_POST['first_name'] ?? ''),
    'phone'      => trim($_POST['phone'] ?? ''),
    'email'      => mb_strtolower(trim($_POST['email'] ?? '')),
];
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';
$next = safe_next($_POST['next'] ?? '', '');
$errors = [];

foreach (['last_name' => 'фамилию', 'first_name' => 'имя'] as $key => $word) {
    if ($data[$key] === '') {
        $errors[$key] = 'Введите ' . $word . '.';
    } elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\- ]{2,50}$/u', $data[$key])) {
        $errors[$key] = 'Только буквы и дефис, от 2 до 50 символов.';
    }
}
if ($data['phone'] === '') {
    $errors['phone'] = 'Введите телефон.';
} elseif (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $data['phone'])) {
    $errors['phone'] = 'Введите номер полностью: +7 (900) 123-45-67.';
}
if ($data['email'] === '') {
    $errors['email'] = 'Введите электронную почту.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите адрес в формате name@mail.ru.';
} elseif (db_one('SELECT id_user FROM users WHERE email = ?', [$data['email']])) {
    $errors['email'] = 'Этот адрес уже зарегистрирован. Войдите или укажите другой.';
}
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,64}$/', $password)) {
    $errors['password'] = 'Не менее 8 символов: латинские буквы и хотя бы одна цифра.';
}
if ($password !== $confirm) {
    $errors['password_confirm'] = 'Пароли не совпадают.';
}
if (empty($_POST['consent'])) {
    $errors['consent'] = 'Без согласия мы не можем хранить ваши данные.';
}

if ($errors) {
    keep_old($data);
    $_SESSION['errors'] = $errors;
    flash('error', 'Проверьте поля, отмеченные красным.');
    redirect('../register.php' . ($next ? '?next=' . urlencode($next) : ''));
}

$role = db_one("SELECT id_role FROM roles WHERE role_name = 'Пользователь'");
db_exec(
    'INSERT INTO users (id_role, last_name, first_name, phone, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)',
    [$role['id_role'], $data['last_name'], $data['first_name'], $data['phone'], $data['email'], password_hash($password, PASSWORD_DEFAULT)]
);
$id = (int)db()->lastInsertId();

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $id, 'name' => $data['first_name'], 'full' => $data['last_name'] . ' ' . $data['first_name'],
    'email' => $data['email'], 'role' => 'Пользователь',
];
clear_old();
flash('success', 'Аккаунт создан. Добро пожаловать, ' . $data['first_name'] . '!');
redirect('../' . ($next ?: 'account.php'));
