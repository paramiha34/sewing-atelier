<?php
$page = ['title' => 'Услуги и цены', 'id' => 'services', 'blocks' => ['filter', 'table', 'card'], 'scripts' => ['blocks/filter', 'pages/character-search']];
require __DIR__ . '/includes/header.php';

$types = db_all('SELECT * FROM service_types ORDER BY id_service_type');
$active = isset($_GET['type']) ? (int)$_GET['type'] : 0;
//переход с главной по карточке коллаборации: показываем её и услуги того же типа
$collab = isset($_GET['collab']) ? db_one('SELECT c.*, st.type_name FROM collaborations c JOIN service_types st ON st.id_service_type = c.id_service_type WHERE c.id_collaboration = ?', [(int)$_GET['collab']]) : null;
if ($collab) {
    $active = (int)$collab['id_service_type'];
}
//действующие и ближайшие акции показываем и здесь, чтобы не возвращаться за ними на главную
$collabs = db_all('SELECT * FROM collaborations WHERE end_date >= CURDATE() ORDER BY start_date');
$services = db_all(
    'SELECT s.*, st.type_name FROM services s
     JOIN service_types st ON st.id_service_type = s.id_service_type
     ORDER BY s.id_service_type, s.price'
);
$cosplayService = db_one("SELECT id_service FROM services WHERE service_name LIKE 'Костюм персонажа (базовый)%' LIMIT 1");
$orderUrl = 'account.php?service=' . (int)($cosplayService['id_service'] ?? 0);
if (!current_user()) {
    $orderUrl = 'login.php?next=' . urlencode($orderUrl);
}
?>
<div class="container">
    <header class="page-head">
        <h1 class="page-head__title">Услуги и цены</h1>
        <p class="page-head__lead">Цена указана за работу. Ткань и фурнитуру можно принести свои или подобрать у нас. Срок считается с последней примерки.</p>
    </header>

<?php if ($collabs): ?>
    <section class="promo" aria-labelledby="promo-title">
        <h2 class="promo__title" id="promo-title">Акции и коллаборации</h2>
        <ul class="promo__list">
<?php foreach ($collabs as $c):
    $selected = $collab && (int)$collab['id_collaboration'] === (int)$c['id_collaboration']; ?>
            <li class="promo__item<?= $selected ? ' promo__item--active' : '' ?>">
                <a class="promo__link" href="services.php?collab=<?= (int)$c['id_collaboration'] ?>"<?= $selected ? ' aria-current="true"' : '' ?>>
                    <img class="promo__image" src="assets/img/collabs/<?= e($c['image']) ?>" alt="" width="160" height="100">
                    <span class="promo__body">
                        <span class="promo__name"><?= e($c['title']) ?></span>
                        <span class="promo__text"><?= e($c['description']) ?></span>
                        <span class="promo__dates"><?= date_ru($c['start_date']) ?> – <?= date_ru($c['end_date']) ?></span>
                    </span>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
<?php if ($collab): ?>
        <p class="promo__note">Показаны услуги раздела «<?= e($collab['type_name']) ?>», к которым относится акция «<?= e($collab['title']) ?>». <a href="services.php">Показать все услуги</a></p>
<?php if ($collab['image_source']): ?>
        <p class="promo__source">Изображение к акции: <a href="<?= e($collab['image_source']) ?>" target="_blank" rel="noopener" data-no-pjax>источник</a></p>
<?php endif; ?>
<?php endif; ?>
    </section>
<?php endif; ?>

    <nav class="filter" aria-label="Тип услуги" data-filter="#services-table">
        <a class="filter__button<?= $active === 0 ? ' filter__button--active' : '' ?>" href="services.php" data-filter-value="all">Все</a>
<?php foreach ($types as $t): ?>
        <a class="filter__button<?= $active === (int)$t['id_service_type'] ? ' filter__button--active' : '' ?>" href="?type=<?= (int)$t['id_service_type'] ?>" data-filter-value="<?= (int)$t['id_service_type'] ?>"><?= e($t['type_name']) ?></a>
<?php endforeach; ?>
    </nav>

    <div class="table-wrap">
        <table class="table" id="services-table">
            <thead class="table__head">
                <tr>
                    <th class="table__cell table__cell--head" scope="col">Услуга</th>
                    <th class="table__cell table__cell--head services__desc-col" scope="col">Что входит</th>
                    <th class="table__cell table__cell--head table__cell--num" scope="col">Срок</th>
                    <th class="table__cell table__cell--head table__cell--num" scope="col">Цена</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($services as $s): ?>
                <tr class="table__row" data-filter-item="<?= (int)$s['id_service_type'] ?>"<?= $active && $active !== (int)$s['id_service_type'] ? ' hidden' : '' ?>>
                    <td class="table__cell"><strong><?= e($s['service_name']) ?></strong><br><span class="services__type"><?= e($s['type_name']) ?></span></td>
                    <td class="table__cell services__desc-col"><?= e($s['description']) ?></td>
                    <td class="table__cell table__cell--num"><?= (int)$s['duration_days'] ?> <?= plural((int)$s['duration_days'], 'день', 'дня', 'дней') ?></td>
                    <td class="table__cell table__cell--num"><?= money($s['price']) ?></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <section class="character-search" aria-labelledby="character-title">
        <div class="character-search__intro">
            <h2 class="character-search__title" id="character-title">Костюм персонажа</h2>
            <p class="character-search__lead">Найдите героя аниме, манги или ранобэ по имени — покажем его изображение и произведение. Нажмите «Заказать костюм», и мы заполним заявку за вас.</p>
        </div>
        <form class="character-search__form" novalidate data-character-search data-order-url="<?= e($orderUrl) ?>" role="search">
            <label class="visually-hidden" for="character-q">Имя персонажа</label>
            <input class="character-search__input form__input" id="character-q" name="q" type="search" placeholder="Например, Фрирен или Levi" autocomplete="off" required minlength="3" maxlength="60">
            <button class="button" type="submit">Найти персонажа</button>
        </form>
        <p class="character-search__status" data-character-status aria-live="polite">Данные о персонажах берутся из открытой базы Shikimori.</p>
        <ul class="character-search__results" data-character-results></ul>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
