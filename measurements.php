<?php
$page = ['title' => 'Как снять мерки', 'id' => 'measurements', 'blocks' => ['table']];
require __DIR__ . '/includes/header.php';

require_once __DIR__ . '/includes/measures.php';
?>
<div class="container">
    <header class="page-head">
        <h1 class="page-head__title">Как снять мерки</h1>
        <p class="page-head__lead">Все мерки со схемы ниже. Для обычной одежды обычно хватает роста, обхватов груди, талии и бёдер и длины руки, остальные нужны для костюмов: шляп, перчаток, сапог и носков. Понадобятся сантиметровая лента и помощник. Мерку под грудью снимают только девушки, остальным её можно пропустить.</p>
    </header>

    <div class="measure">
        <figure class="measure__figure">
            <img class="measure__image" src="assets/img/ui/body-measures.png" alt="Схема женской и мужской фигуры с линиями мерок: обхват головы, шеи, груди, талии, бёдер, ширина плеч, длина руки и ноги, рост" width="1280" height="902">
            <figcaption class="measure__caption">Схема: MagentaGreen, <a href="https://commons.wikimedia.org/wiki/File:Body_measures_SVG.svg" target="_blank" rel="noopener">Wikimedia Commons</a>, лицензия CC BY-SA 3.0, подписи переведены на русский.</figcaption>
        </figure>

        <ol class="measure__steps">
<?php $i = 0; foreach (MEASURES as [$label, $min, $max, $how]): $i++; ?>
            <li class="measure__step">
                <span class="measure__num" aria-hidden="true"><?= $i ?></span>
                <div>
                    <h2 class="measure__title"><?= e($label) ?></h2>
                    <p class="measure__text"><?= e($how) ?></p>
                </div>
            </li>
<?php endforeach; ?>
        </ol>
    </div>

    <section class="measure__rules">
        <h2 class="measure__rules-title">Чтобы мерки были точными</h2>
        <ul class="measure__list">
            <li>Снимайте мерки поверх тонкого белья, а не поверх одежды.</li>
            <li>Лента прилегает к телу, но не натягивается и не врезается.</li>
            <li>Записывайте значения в сантиметрах с точностью до половины сантиметра.</li>
            <li>Для костюма персонажа дополнительно пришлите фото в полный рост — так проще подобрать пропорции деталей.</li>
        </ul>
<?php if (current_user() && !is_admin()): ?>
        <a class="button" href="account.php?tab=measurements">Записать мерки в личный кабинет</a>
<?php elseif (!current_user()): ?>
        <p class="measure__note">Зарегистрируйтесь, и мерки сохранятся в личном кабинете — повторно снимать их не придётся. <a href="register.php">Создать аккаунт</a></p>
<?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
