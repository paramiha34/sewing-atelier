<?php
require_once __DIR__ . '/../includes/helpers.php';
$user = current_user();
if (!$user || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php?next=reviews.php');
}
check_csrf();

$rating = (int)($_POST['rating'] ?? 0);
$text = trim($_POST['review_text'] ?? '');
if ($rating < 1 || $rating > 5) {
    flash('error', 'Выберите оценку от 1 до 5.');
    redirect('../reviews.php');
}
if (mb_strlen($text) < 10 || mb_strlen($text) > 1000) {
    flash('error', 'Отзыв должен содержать от 10 до 1000 символов.');
    redirect('../reviews.php');
}
db_exec('INSERT INTO reviews (id_user, rating, review_text, review_date) VALUES (?, ?, ?, CURDATE())', [$user['id'], $rating, $text]);
flash('success', 'Спасибо! Отзыв опубликован.');
redirect('../reviews.php');
