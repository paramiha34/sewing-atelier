<?php
require_once __DIR__ . '/includes/helpers.php';
$user = require_login();
if (is_admin()) {
    redirect('admin.php');
}
$page = ['title' => 'Личный кабинет', 'id' => 'account', 'blocks' => ['form', 'table', 'side-menu'], 'scripts' => ['blocks/form-validate', 'blocks/date-mask']];
$tab = ($_GET['tab'] ?? 'orders') === 'measurements' ? 'measurements' : 'orders';

$orders = db_all(
    'SELECT o.*, s.service_name, os.status_name FROM orders o
     JOIN services s ON s.id_service = o.id_service
     JOIN order_statuses os ON os.id_status = o.id_status
     WHERE o.id_user = ? ORDER BY o.order_date DESC, o.id_order DESC',
    [$user['id']]
);
$services = db_all(
    'SELECT s.id_service, s.service_name, s.price, st.type_name FROM services s
     JOIN service_types st ON st.id_service_type = s.id_service_type ORDER BY st.id_service_type, s.service_name'
);
$m = db_one('SELECT * FROM measurements WHERE id_user = ?', [$user['id']]) ?? [];
$preService = (int)($_GET['service'] ?? 0);
$character = trim($_GET['character'] ?? '');
$preDescription = $character !== '' ? 'Костюм персонажа: ' . mb_substr($character, 0, 80) . '. ' : '';
//ссылку на персонажа подставляем, только если она ведёт на Shikimori
$preRef = preg_match('~^https://shikimori\.(io|one)/characters/[\w\-]+$~', $_GET['ref'] ?? '') ? $_GET['ref'] : '';
$minDate = date('Y-m-d', strtotime('+3 days'));
$statusClass = ['Выполнен' => 'status--done', 'Отменён' => 'status--cancel', 'В работе' => 'status--active', 'Примерка' => 'status--active'];

require_once __DIR__ . '/includes/measures.php';
require __DIR__ . '/includes/header.php';
?>
<div class="container account">
    <aside class="account__aside">
        <p class="account__hello">Здравствуйте,<br><strong><?= e($user['full']) ?></strong></p>
        <nav class="side-menu" aria-label="Разделы кабинета">
            <a class="side-menu__link<?= $tab === 'orders' ? ' side-menu__link--active' : '' ?>" href="account.php">Мои заказы</a>
            <a class="side-menu__link<?= $tab === 'measurements' ? ' side-menu__link--active' : '' ?>" href="account.php?tab=measurements">Мерки</a>
            <a class="side-menu__link" href="logout.php">Выйти</a>
        </nav>
    </aside>

    <div class="account__main">
<?php if ($tab === 'orders'): ?>
        <h1 class="account__title">Мои заказы</h1>
        <div class="table-wrap">
            <table class="table">
                <thead class="table__head">
                    <tr>
                        <th class="table__cell table__cell--head" scope="col">№</th>
                        <th class="table__cell table__cell--head" scope="col">Услуга</th>
                        <th class="table__cell table__cell--head" scope="col">Оформлен</th>
                        <th class="table__cell table__cell--head" scope="col">Срок</th>
                        <th class="table__cell table__cell--head table__cell--num" scope="col">Стоимость</th>
                        <th class="table__cell table__cell--head" scope="col">Статус</th>
                    </tr>
                </thead>
                <tbody>
<?php if (!$orders): ?>
                    <tr><td class="table__empty" colspan="6">Заказов пока нет. Оформите первый — форма ниже.</td></tr>
<?php endif; ?>
<?php foreach ($orders as $o): ?>
                    <tr class="table__row">
                        <td class="table__cell"><?= (int)$o['id_order'] ?></td>
                        <td class="table__cell"><?= e($o['service_name']) ?><?php if ($o['description']): ?><br><span class="account__desc"><?= e($o['description']) ?></span><?php endif; ?></td>
                        <td class="table__cell"><?= date_ru($o['order_date']) ?></td>
                        <td class="table__cell"><?= date_ru($o['due_date']) ?></td>
                        <td class="table__cell table__cell--num"><?= money($o['total_price']) ?></td>
                        <td class="table__cell"><span class="status <?= $statusClass[$o['status_name']] ?? '' ?>"><?= e($o['status_name']) ?></span></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <section class="account__section" id="new-order">
            <h2 class="account__subtitle">Новый заказ</h2>
            <form class="form account__form" action="handlers/order-create.php" method="post" data-validate>
                <?= csrf_field() ?>
                <div class="form__field">
                    <label class="form__label" for="id_service">Услуга <span class="form__required">*</span></label>
                    <select class="form__select" id="id_service" name="id_service" required>
                        <option value="">Выберите услугу</option>
<?php $group = null; foreach ($services as $s):
    if ($group !== $s['type_name']) { if ($group !== null) echo "</optgroup>"; $group = $s['type_name']; echo '<optgroup label="' . e($group) . '">'; } ?>
                        <option value="<?= (int)$s['id_service'] ?>"<?= $preService === (int)$s['id_service'] ? ' selected' : '' ?>><?= e($s['service_name']) ?> — <?= money($s['price']) ?></option>
<?php endforeach; if ($group !== null) echo '</optgroup>'; ?>
                    </select>
                    <p class="form__error"></p>
                </div>
                <div class="form__field">
                    <label class="form__label" for="description">Что нужно сделать <span class="form__required">*</span></label>
                    <textarea class="form__textarea" id="description" name="description" required minlength="10" maxlength="1000" data-counter="order-counter" data-pattern="^[\s\S]{10,}$" data-pattern-message="Опишите заказ хотя бы в 10 символах."><?= e($preDescription) ?></textarea>
                    <p class="form__counter" id="order-counter"></p>
                    <p class="form__error"></p>
                </div>
                <div class="form__row">
                    <div class="form__field">
                        <label class="form__label" for="due_date">Желаемая дата готовности <span class="form__required">*</span></label>
                        <input class="form__input" id="due_date" name="due_date" inputmode="numeric" maxlength="10" placeholder="дд.мм.гггг" autocomplete="off" required data-date data-min-days="3">
                        <p class="form__hint">Не раньше <?= date_ru($minDate) ?>.</p>
                        <p class="form__error"></p>
                    </div>
                    <div class="form__field">
                        <label class="form__label" for="reference_url">Ссылка на референс</label>
                        <input class="form__input" id="reference_url" name="reference_url" type="url" maxlength="255" placeholder="https://" value="<?= e($preRef) ?>" data-pattern="^https?://\S+$" data-pattern-message="Ссылка должна начинаться с http:// или https://.">
                        <p class="form__error"></p>
                    </div>
                </div>
                <div class="form__actions">
                    <button class="button" type="submit">Отправить заказ</button>
                    <p class="form__hint">Мастер свяжется с вами и уточнит стоимость.</p>
                </div>
            </form>
        </section>
<?php else: ?>
        <h1 class="account__title">Мерки</h1>
        <p class="account__lead">Значения в сантиметрах. Заполните то, что знаете, остальное можно добавить позже. <a href="measurements.php">Как правильно снять мерки</a></p>
<?php if (!empty($m['updated_at'])): ?>
        <p class="account__updated">Последнее изменение: <?= date('d.m.Y H:i', strtotime($m['updated_at'])) ?></p>
<?php endif; ?>
        <form class="form account__form" action="handlers/measurements-save.php" method="post" data-validate>
            <?= csrf_field() ?>
            <div class="account__grid">
<?php foreach (MEASURES as $name => [$label, $min, $max, $how]): ?>
                <div class="form__field">
                    <label class="form__label" for="<?= $name ?>"><?= e($label) ?><?= $name === 'shoe_size' ? '' : ', см' ?></label>
                    <input class="form__input" id="<?= $name ?>" name="<?= $name ?>" inputmode="decimal" maxlength="5" data-number-only data-min="<?= $min ?>" data-max="<?= $max ?>" value="<?= isset($m[$name]) ? e(rtrim(rtrim((string)$m[$name], '0'), '.')) : '' ?>">
<?php if ($name === 'underbust'): ?>
                    <p class="form__hint">Только для девушек, иначе оставьте пустым.</p>
<?php endif; ?>
                    <p class="form__error"></p>
                </div>
<?php endforeach; ?>
            </div>
            <div class="form__actions">
                <button class="button" type="submit"><?= $m ? 'Сохранить изменения' : 'Сохранить мерки' ?></button>
            </div>
        </form>
<?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
