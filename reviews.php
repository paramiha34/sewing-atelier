<?php
$page = ['title' => 'Отзывы', 'id' => 'reviews', 'blocks' => ['review', 'rating', 'form'], 'scripts' => ['blocks/form-validate']];
require __DIR__ . '/includes/header.php';

$reviews = db_all(
    'SELECT r.*, u.first_name, u.last_name FROM reviews r
     JOIN users u ON u.id_user = r.id_user ORDER BY r.review_date DESC, r.id_review DESC'
);
$avg = db_one('SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt FROM reviews');
$user = current_user();
?>
<div class="container">
    <header class="page-head reviews__head">
        <div>
            <h1 class="page-head__title">Отзывы</h1>
            <p class="page-head__lead">Что клиенты пишут после выдачи заказа. Мы публикуем все отзывы, в том числе критические.</p>
        </div>
<?php if ($avg['cnt']): ?>
        <p class="reviews__score"><span class="reviews__score-value"><?= e(str_replace('.', ',', $avg['avg_rating'])) ?></span><span class="reviews__score-label">средняя оценка по <?= (int)$avg['cnt'] ?> <?= plural((int)$avg['cnt'], 'отзыву', 'отзывам', 'отзывам') ?></span></p>
<?php endif; ?>
    </header>

    <div class="reviews">
        <ul class="reviews__list">
<?php if (!$reviews): ?>
            <li class="reviews__empty">Отзывов пока нет. Станьте первым, кто поделится впечатлением.</li>
<?php endif; ?>
<?php foreach ($reviews as $r): ?>
            <li class="review">
                <span class="rating" aria-label="Оценка <?= (int)$r['rating'] ?> из 5"><?= str_repeat('★', (int)$r['rating']) ?><span class="rating__empty"><?= str_repeat('★', 5 - (int)$r['rating']) ?></span></span>
                <p class="review__text"><?= nl2br(e($r['review_text'])) ?></p>
                <p class="review__author"><span class="review__name"><?= e($r['first_name'] . ' ' . mb_substr($r['last_name'], 0, 1) . '.') ?></span><span><?= date_ru($r['review_date']) ?></span></p>
            </li>
<?php endforeach; ?>
        </ul>

        <aside class="reviews__aside">
<?php if ($user && !is_admin()): ?>
            <form class="form reviews__form" action="handlers/review-create.php" method="post" data-validate>
                <?= csrf_field() ?>
                <h2 class="reviews__form-title">Оставить отзыв</h2>
                <div class="form__field">
                    <span class="form__label">Оценка <span class="form__required">*</span></span>
                    <fieldset class="rating-input">
                        <legend class="visually-hidden">Оценка от 1 до 5</legend>
<?php for ($i = 5; $i >= 1; $i--): ?>
                        <input class="rating-input__radio" type="radio" name="rating" id="rating-<?= $i ?>" value="<?= $i ?>"<?= $i === 5 ? ' checked' : '' ?>>
                        <label class="rating-input__star" for="rating-<?= $i ?>" title="<?= $i ?> из 5">★</label>
<?php endfor; ?>
                    </fieldset>
                </div>
                <div class="form__field">
                    <label class="form__label" for="review-text">Отзыв <span class="form__required">*</span></label>
                    <textarea class="form__textarea" id="review-text" name="review_text" required minlength="10" maxlength="1000" data-counter="review-counter" data-pattern="^[\s\S]{10,}$" data-pattern-message="Напишите хотя бы 10 символов."></textarea>
                    <p class="form__counter" id="review-counter"></p>
                    <p class="form__error"></p>
                </div>
                <button class="button" type="submit">Опубликовать отзыв</button>
            </form>
<?php elseif (!$user): ?>
            <div class="reviews__cta">
                <h2 class="reviews__form-title">Были у нас?</h2>
                <p>Войдите в личный кабинет, чтобы оставить отзыв о заказе.</p>
                <a class="button" href="login.php?next=reviews.php">Войти и оставить отзыв</a>
            </div>
<?php endif; ?>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
