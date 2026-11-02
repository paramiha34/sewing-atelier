<?php
$page = ['title' => 'Примеры работ', 'id' => 'works', 'blocks' => ['filter', 'modal'], 'scripts' => ['blocks/filter', 'blocks/modal']];
require __DIR__ . '/includes/header.php';

$types = db_all('SELECT * FROM service_types ORDER BY id_service_type');
$active = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$works = db_all(
    'SELECT w.*, st.type_name FROM works w
     JOIN service_types st ON st.id_service_type = w.id_service_type
     ORDER BY w.created_at DESC'
);
?>
<div class="container">
    <header class="page-head">
        <h1 class="page-head__title">Примеры работ</h1>
        <p class="page-head__lead">Вещи, которые мы сшили, починили и подогнали за последние месяцы. Нажмите на работу, чтобы прочитать о ней.</p>
    </header>

    <nav class="filter" aria-label="Тип работы" data-filter="#works-grid">
        <a class="filter__button<?= $active === 0 ? ' filter__button--active' : '' ?>" href="works.php" data-filter-value="all">Все</a>
<?php foreach ($types as $t): ?>
        <a class="filter__button<?= $active === (int)$t['id_service_type'] ? ' filter__button--active' : '' ?>" href="?type=<?= (int)$t['id_service_type'] ?>" data-filter-value="<?= (int)$t['id_service_type'] ?>"><?= e($t['type_name']) ?></a>
<?php endforeach; ?>
    </nav>

    <ul class="works" id="works-grid">
<?php foreach ($works as $w):
    $img = 'assets/img/works/' . $w['image']; ?>
        <li class="works__item" data-filter-item="<?= (int)$w['id_service_type'] ?>"<?= $active && $active !== (int)$w['id_service_type'] ? ' hidden' : '' ?>>
            <button class="work" type="button" data-modal-open data-image="<?= e($img) ?>" data-title="<?= e($w['title']) ?>" data-text="<?= e($w['description']) ?>" data-kind="<?= e($w['type_name']) ?>, <?= date_ru($w['created_at']) ?>" data-link="<?= e($w['character_url'] ?? '') ?>">
                <img class="work__image" src="<?= e($img) ?>" alt="<?= e($w['title']) ?>" width="400" height="500" loading="lazy">
                <span class="work__caption">
                    <span class="work__title"><?= e($w['title']) ?></span>
                    <span class="work__type"><?= e($w['type_name']) ?></span>
                </span>
            </button>
        </li>
<?php endforeach; ?>
    </ul>
</div>

<dialog class="modal" data-modal aria-labelledby="modal-title">
    <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">×</button>
    <div class="modal__body">
        <img class="modal__image" src="" alt="">
        <div class="modal__content">
            <p class="modal__type"></p>
            <h2 class="modal__title" id="modal-title"></h2>
            <p class="modal__text"></p>
            <a class="modal__extra" href="#" target="_blank" rel="noopener" hidden>Персонаж на Shikimori</a>
            <a class="button" href="services.php">Узнать цену</a>
        </div>
    </div>
</dialog>
<?php require __DIR__ . '/includes/footer.php'; ?>
