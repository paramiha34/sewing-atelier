<?php
$page = ['title' => 'Контакты', 'id' => 'contacts'];
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <header class="page-head">
        <h1 class="page-head__title">Контакты</h1>
        <p class="page-head__lead">Приходите на примерку или за готовым заказом. Перед визитом лучше позвонить — мастер может быть на выезде.</p>
    </header>

    <div class="contacts">
        <dl class="contacts__list">
            <div class="contacts__item">
                <dt class="contacts__term">Адрес</dt>
                <dd class="contacts__value">Москва, ул. Портновская, 12, первый этаж, вход со двора</dd>
            </div>
            <div class="contacts__item">
                <dt class="contacts__term">Часы работы</dt>
                <dd class="contacts__value">Пн–Пт 10:00–20:00<br>Сб 11:00–17:00<br>Вс — выходной</dd>
            </div>
            <div class="contacts__item">
                <dt class="contacts__term">Телефон</dt>
                <dd class="contacts__value"><a href="tel:+74950000000">+7 (495) 000-00-00</a></dd>
            </div>
            <div class="contacts__item">
                <dt class="contacts__term">Почта</dt>
                <dd class="contacts__value"><a href="mailto:hello@lekalo-atelier.ru">hello@lekalo-atelier.ru</a></dd>
            </div>
            <div class="contacts__item">
                <dt class="contacts__term">Как добраться</dt>
                <dd class="contacts__value">7 минут пешком от метро. Во дворе есть парковка для клиентов.</dd>
            </div>
        </dl>
        <figure class="contacts__map">
            <img class="contacts__map-image" src="assets/img/ui/map.svg" alt="Схема проезда: ателье во дворе дома 12 на улице Портновской, в 7 минутах от метро" width="640" height="460">
            <figcaption class="contacts__map-caption">Схема проезда</figcaption>
        </figure>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
