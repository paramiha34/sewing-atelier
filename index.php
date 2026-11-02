<?php
$page = ['id' => 'home', 'blocks' => ['card', 'tape', 'review', 'rating', 'ruler'], 'scripts' => ['blocks/ruler']];
require __DIR__ . '/includes/header.php';

$types = db_all(
    'SELECT st.id_service_type, st.type_name, MIN(s.price) AS min_price, COUNT(s.id_service) AS cnt
     FROM service_types st LEFT JOIN services s ON s.id_service_type = st.id_service_type
     GROUP BY st.id_service_type, st.type_name ORDER BY st.id_service_type'
);
$collabs = db_all('SELECT * FROM collaborations WHERE end_date >= CURDATE() ORDER BY start_date LIMIT 3');
$reviews = db_all(
    'SELECT r.rating, r.review_text, r.review_date, u.first_name
     FROM reviews r JOIN users u ON u.id_user = r.id_user
     ORDER BY r.review_date DESC LIMIT 3'
);
$typeText = [
    1 => 'Платья, брюки, пальто и костюмы по вашим меркам и из выбранной ткани.',
    2 => 'Меняем молнии и подкладку, штопаем, восстанавливаем любимые вещи.',
    3 => 'Ушиваем, укорачиваем и посадим готовую вещь точно по фигуре.',
    4 => 'Собираем образ персонажа по референсам: одежда, плащи, мягкие доспехи, реквизит.',
];
$orderLink = current_user() ? 'account.php#new-order' : 'login.php?next=' . urlencode('account.php#new-order');
?>
<section class="hero">
    <div class="hero__inner container">
        <div class="hero__content">
            <h1 class="hero__title">Шьём по вашим меркам. И по меркам вашего героя.</h1>
            <p class="hero__lead">Ателье «Лекало» шьёт, чинит и подгоняет повседневную одежду, а ещё собирает костюмы персонажей книг, фильмов, манги и игр к фестивалю и фотосессии.</p>
            <div class="hero__actions">
                <a class="button" href="<?= e($orderLink) ?>">Оформить заказ</a>
                <a class="button button--ghost" href="works.php">Посмотреть работы</a>
            </div>
        </div>
        <figure class="hero__figure" aria-hidden="true">
            <img class="hero__image" src="assets/img/ui/hero-dress-form.svg" alt="" width="420" height="520">
        </figure>
    </div>
</section>
<div class="tape tape--sticky" aria-hidden="true">
    <div class="tape__numbers"><?php for ($i = 0; $i <= 60; $i++): ?><span><?= $i * 5 ?></span><?php endfor; ?></div>
</div>

<section class="section">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Что мы делаем</h2>
            <a class="home__more" href="services.php">Все услуги и цены</a>
        </div>
        <ul class="home__services">
<?php foreach ($types as $t): ?>
            <li class="card<?= (int)$t['id_service_type'] === 4 ? ' card--accent' : '' ?>">
                <h3 class="card__title"><?= e($t['type_name']) ?></h3>
                <p class="card__text"><?= e($typeText[$t['id_service_type']] ?? '') ?></p>
                <div class="card__meta">
                    <span class="card__price">от <?= money($t['min_price']) ?></span>
                    <a class="card__link" href="services.php?type=<?= (int)$t['id_service_type'] ?>"><?= (int)$t['cnt'] ?> <?= plural((int)$t['cnt'], 'услуга', 'услуги', 'услуг') ?></a>
                </div>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</section>

<?php if ($collabs): ?>
<section class="section home__collabs">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Коллаборации и сезонные акции</h2>
            <p class="section__lead">Тематические коллекции к праздникам и по мотивам любимых произведений.</p>
        </div>
        <ul class="collabs">
<?php foreach ($collabs as $c): ?>
            <li class="collab">
                <a class="collab__link" href="services.php?collab=<?= (int)$c['id_collaboration'] ?>">
                    <img class="collab__image" src="assets/img/collabs/<?= e($c['image']) ?>" alt="" width="480" height="300" loading="lazy">
                    <span class="collab__body">
                        <span class="collab__title"><?= e($c['title']) ?></span>
                        <span class="collab__text"><?= e($c['description']) ?></span>
                        <span class="collab__dates"><?= date_ru($c['start_date']) ?> – <?= date_ru($c['end_date']) ?></span>
                        <span class="collab__more">Подробнее и цены</span>
                    </span>
                </a>
<?php if ($c['image_source']): ?>
                <a class="collab__source" href="<?= e($c['image_source']) ?>" target="_blank" rel="noopener" data-no-pjax>Источник изображения</a>
<?php endif; ?>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section__head">
            <h2 class="section__title">Что говорят клиенты</h2>
            <a class="home__more" href="reviews.php">Все отзывы</a>
        </div>
        <ul class="home__reviews">
<?php foreach ($reviews as $r): ?>
            <li class="review">
                <span class="rating" aria-label="Оценка <?= (int)$r['rating'] ?> из 5"><?= str_repeat('★', (int)$r['rating']) ?><span class="rating__empty"><?= str_repeat('★', 5 - (int)$r['rating']) ?></span></span>
                <p class="review__text"><?= e($r['review_text']) ?></p>
                <p class="review__author"><span class="review__name"><?= e($r['first_name']) ?></span><span><?= date_ru($r['review_date']) ?></span></p>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container home__steps">
        <h2 class="section__title">Как проходит заказ</h2>
        <ol class="steps">
            <li class="steps__item"><h3 class="steps__title">Заявка</h3><p class="steps__text">Опишите вещь или пришлите ссылку на референс в личном кабинете.</p></li>
            <li class="steps__item"><h3 class="steps__title">Мерки</h3><p class="steps__text">Снимите мерки по нашей инструкции или приходите — снимем сами.</p></li>
            <li class="steps__item"><h3 class="steps__title">Примерка</h3><p class="steps__text">Подгоняем изделие по фигуре. Статус заказа виден в кабинете.</p></li>
            <li class="steps__item"><h3 class="steps__title">Выдача</h3><p class="steps__text">Забираете готовую вещь в ателье на Портновской.</p></li>
        </ol>
    </div>
</section>
<div class="ruler" data-ruler aria-hidden="true">
    <div class="ruler__side"><div class="ruler__numbers"></div></div>
    <div class="ruler__line ruler__line--x"></div>
    <div class="ruler__line ruler__line--y"></div>
    <span class="ruler__label ruler__label--x"></span>
    <span class="ruler__label ruler__label--y"></span>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
