<?php
require_once __DIR__ . '/../includes/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}
check_csrf();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$next = safe_next($_POST['next'] ?? '', '');
keep_old(['email' => $email]);
$back = '../login.php' . ($next ? '?next=' . urlencode($next) : '');

if ($email === '' || $password === '') {
    flash('error', 'Заполните электронную почту и пароль.');
    redirect($back);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Адрес электронной почты введён неверно.');
    redirect($back);
}

$user = db_one(
    'SELECT u.id_user, u.first_name, u.last_name, u.email, u.password_hash, r.role_name
     FROM users u JOIN roles r ON r.id_role = u.id_role WHERE u.email = ?',
    [$email]
);
if (!$user || !password_verify($password, $user['password_hash'])) {
    flash('error', 'Неверная почта или пароль.');
    redirect($back);
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id'    => (int)$user['id_user'],
    'name'  => $user['first_name'],
    'full'  => $user['last_name'] . ' ' . $user['first_name'],
    'email' => $user['email'],
    'role'  => $user['role_name'],
];
clear_old();
flash('success', 'Здравствуйте, ' . $user['first_name'] . '!');
if ($user['role_name'] === 'Администратор') {
    redirect('../admin.php');
}
redirect('../' . ($next ?: 'account.php'));
