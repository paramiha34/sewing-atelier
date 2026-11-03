<?php
//Общая шапка сайта. Страница перед подключением задаёт массив $page:
//title - заголовок вкладки, id - имя файла стилей в assets/css/pages,
//blocks - нужные БЭМ-блоки из assets/css/blocks, scripts - скрипты из assets/js.
//Чтобы убрать функцию со страницы, достаточно убрать её блок из этого массива.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/logo.php';

$page = array_merge(['title' => '', 'id' => '', 'blocks' => [], 'scripts' => []], $page ?? []);
$commonBlocks = ['header', 'nav', 'theme-switcher', 'button', 'notice', 'footer', 'progress', 'pumpkin', 'candy-rain'];
$blocks = array_unique(array_merge($commonBlocks, $page['blocks']));
$scripts = array_unique(array_merge(['app', 'blocks/theme-switcher', 'blocks/nav', 'blocks/pjax', 'blocks/candy-rain'], $page['scripts']));
$current = basename($_SERVER['PHP_SELF']);
$user = current_user();
$isHome = $page['id'] === 'home';

$menu = [
    'index.php'        => 'Главная',
    'services.php'     => 'Услуги и цены',
    'works.php'        => 'Примеры работ',
    'measurements.php' => 'Как снять мерки',
    'reviews.php'      => 'Отзывы',
    'contacts.php'     => 'Контакты',
];
?><!DOCTYPE html>
<html lang="ru" data-theme="paper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page['title'] ? $page['title'] . ' — ' . SITE_NAME : SITE_NAME . ' — ' . SITE_TAGLINE) ?></title>
    <meta name="description" content="Ателье «Лекало»: пошив и ремонт одежды, подгонка по фигуре и костюмы для косплея.">
    <link rel="icon" href="assets/img/ui/favicon.svg" type="image/svg+xml">
    <script>
        //Тему и линейку применяем до отрисовки, иначе страница мигнёт светлой темой
        try {
            var savedTheme = localStorage.getItem('atelier-theme');
            if (savedTheme) document.documentElement.setAttribute('data-theme', savedTheme);
            if (localStorage.getItem('atelier-ruler') === 'off') document.documentElement.setAttribute('data-ruler', 'off');
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="assets/css/base/fonts.css">
    <link rel="stylesheet" href="assets/css/base/themes.css">
    <link rel="stylesheet" href="assets/css/base/reset.css">
    <link rel="stylesheet" href="assets/css/base/typography.css">
    <link rel="stylesheet" href="assets/css/base/layout.css">
<?php foreach ($blocks as $b): ?>
    <link rel="stylesheet" href="assets/css/blocks/<?= e($b) ?>.css">
<?php endforeach; ?>
<?php if ($page['id']): ?>
    <link rel="stylesheet" href="assets/css/pages/<?= e($page['id']) ?>.css">
<?php endif; ?>
<?php foreach ($scripts as $s): ?>
    <script src="assets/js/<?= e($s) ?>.js" defer></script>
<?php endforeach; ?>
</head>
<body class="page page--<?= e($page['id'] ?: 'default') ?>">
<a class="page__skip" href="#main">Перейти к содержимому</a>
<div class="progress" aria-hidden="true"></div>
<header class="header">
    <div class="header__inner container">
        <a class="header__logo logo" href="index.php" aria-label="Лекало — на главную">
            <?= logo_svg('logo') ?>
        </a>

        <button class="header__burger" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="header__burger-line"></span>
            <span class="visually-hidden">Открыть меню</span>
        </button>

        <nav class="header__nav nav" id="site-nav" aria-label="Основное меню">
            <ul class="nav__list">
<?php foreach ($menu as $href => $label): ?>
                <li class="nav__item">
                    <a class="nav__link<?= $current === $href ? ' nav__link--active' : '' ?>" href="<?= $href ?>"<?= $current === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
                </li>
<?php endforeach; ?>
            </ul>
            <div class="nav__actions">
<?php if ($isHome): ?>
                <button class="ruler-toggle" type="button" aria-pressed="true" title="Линейка и перекрестие за курсором" data-ruler-toggle>
                    <svg class="ruler-toggle__icon" viewBox="0 0 20 20" aria-hidden="true"><path d="M2 10h16M10 2v16" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                    <span class="ruler-toggle__label">Линейка</span>
                </button>
<?php endif; ?>
                <div class="theme-switcher" data-theme-switcher>
                    <button class="theme-switcher__toggle" type="button" aria-haspopup="true" aria-expanded="false" data-theme-toggle>
                        <span class="theme-switcher__swatch" aria-hidden="true"></span>
                        <span class="theme-switcher__label">Тема</span>
                    </button>
                    <ul class="theme-switcher__menu" role="menu" hidden>
                        <li><button class="theme-switcher__option" type="button" role="menuitemradio" data-theme-value="paper"><span class="theme-switcher__dot theme-switcher__dot--paper"></span>Бумага</button></li>
                        <li><button class="theme-switcher__option" type="button" role="menuitemradio" data-theme-value="alpine"><span class="theme-switcher__dot theme-switcher__dot--alpine"></span>Альпийский закат</button></li>
                        <li><button class="theme-switcher__option" type="button" role="menuitemradio" data-theme-value="dark"><span class="theme-switcher__dot theme-switcher__dot--dark"></span>Тёмная</button></li>
                        <li><button class="theme-switcher__option" type="button" role="menuitemradio" data-theme-value="halloween"><span class="theme-switcher__dot theme-switcher__dot--halloween"></span>Хэллоуин</button></li>
                    </ul>
                </div>
<?php if ($user): ?>
                <a class="button button--ghost button--small" href="<?= is_admin() ? 'admin.php' : 'account.php' ?>"><?= is_admin() ? 'Админ-панель' : 'Личный кабинет' ?></a>
                <a class="nav__link nav__link--quiet" href="logout.php" data-no-pjax>Выйти</a>
<?php else: ?>
                <a class="button button--small" href="login.php">Войти</a>
<?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<button class="pumpkin" type="button" title="Угоститься конфетами" data-candy-rain>
    <img class="pumpkin__image" src="assets/img/ui/pumpkin.svg" alt="" width="46" height="47">
    <span class="visually-hidden">Высыпать конфеты</span>
</button>

<main class="page__main" id="main" tabindex="-1">
<?php foreach (take_flashes() as $f): ?>
    <div class="container">
        <p class="notice notice--<?= e($f['type']) ?>" role="status"><?= e($f['text']) ?></p>
    </div>
<?php endforeach; ?>
