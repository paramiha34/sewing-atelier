<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Любой текст из базы или формы выводим только через эту функцию, чтобы не было XSS
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

//Сообщение, которое покажется один раз на следующей странице
function flash(string $type, string $text): void
{
    $_SESSION['flash'][] = ['type' => $type, 'text' => $text];
}

function take_flashes(): array
{
    $list = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $list;
}

//Запоминаем введённое в форму, чтобы после ошибки не набирать всё заново
function keep_old(array $data): void
{
    $_SESSION['old'] = $data;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

//Токен для форм, которые меняют данные: без него запрос с чужого сайта не пройдёт
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Сессия устарела. Обновите страницу и повторите действие.');
    }
}

function money($value): string
{
    return number_format((float)$value, 0, ',', ' ') . ' ₽';
}

function date_ru(?string $date): string
{
    if (!$date) {
        return '—';
    }
    return date('d.m.Y', strtotime($date));
}

//Дата из формы в формате дд.мм.гггг (или гггг-мм-дд) в вид для базы. Возвращает null, если даты нет
function parse_date(string $value): ?string
{
    foreach (['!d.m.Y', '!Y-m-d'] as $format) {
        $d = DateTime::createFromFormat($format, $value);
        if ($d && $d->format(ltrim($format, '!')) === $value) {
            return $d->format('Y-m-d');
        }
    }
    return null;
}

//Склонение после числа: 1 день, 2 дня, 5 дней
function plural(int $n, string $one, string $few, string $many): string
{
    $n10 = $n % 10;
    $n100 = $n % 100;
    if ($n10 === 1 && $n100 !== 11) {
        return $one;
    }
    if ($n10 >= 2 && $n10 <= 4 && ($n100 < 12 || $n100 > 14)) {
        return $few;
    }
    return $many;
}

/* ---------- Сессия пользователя ---------- */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'Администратор';
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('info', 'Войдите, чтобы открыть личный кабинет.');
        $here = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        redirect('login.php?next=' . urlencode($here));
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!is_admin()) {
        http_response_code(403);
        flash('error', 'Раздел доступен только администратору.');
        redirect('account.php');
    }
    return $user;
}

//Возвращаемся после входа только на свои страницы, а не по любой ссылке из адреса
function safe_next(?string $next, string $fallback): string
{
    if ($next && preg_match('~^[a-z\-]+\.php(\?[^\s<>"]*)?(#[a-z\-]+)?$~i', $next)) {
        return $next;
    }
    return $fallback;
}
