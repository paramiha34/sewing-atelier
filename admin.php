<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/admin-schema.php';
require_admin();
$page = ['title' => 'Административная панель', 'id' => 'admin', 'blocks' => ['table', 'form', 'side-menu'], 'scripts' => ['blocks/form-validate', 'blocks/phone-mask', 'blocks/date-mask', 'pages/admin']];

$table = $_GET['table'] ?? '';
$view = $_GET['view'] ?? '';
if (!isset(ADMIN_TABLES[$table]) && !isset(ADMIN_VIEWS[$view])) {
    $table = 'orders';
}

//Варианты для выпадающего списка внешнего ключа: id => подпись из связанной таблицы
function fk_options(array $ref): array
{
    [$t, $pk, $label] = $ref;
    $rows = db_all("SELECT $pk AS id, $label AS label FROM $t ORDER BY $pk");
    return array_column($rows, 'label', 'id');
}

function cell_value(array $col, $value, array $fkCache): string
{
    if ($value === null || $value === '') {
        return '<span class="admin__null">—</span>';
    }
    switch ($col['type']) {
        case 'fk':       return e($fkCache[$value] ?? $value);
        case 'password': return '<span class="admin__null">скрыт</span>';
        case 'date':     return date_ru($value);
        case 'datetime': return e(date('d.m.Y H:i', strtotime($value)));
        case 'decimal':  return e(rtrim(rtrim(number_format((float)$value, 2, ',', ' '), '0'), ','));
        case 'textarea': return e(mb_strimwidth($value, 0, 90, '…'));
        default:         return e($value);
    }
}

function input_for(string $name, array $col, $value, array $fkCache): string
{
    $req = !empty($col['required']) ? ' required' : '';
    $id = 'f-' . $name;
    $v = e($value ?? '');
    $attrs = '';
    if (isset($col['max']) && in_array($col['type'], ['text', 'email', 'url'], true)) $attrs .= ' maxlength="' . (int)$col['max'] . '"';
    if (isset($col['pattern'])) $attrs .= ' data-pattern="' . e($col['pattern']) . '" data-pattern-message="Формат: +7 (900) 123-45-67" data-phone-mask';
    if (in_array($col['type'], ['int', 'decimal'], true) && isset($col['min'])) $attrs .= ' data-min="' . $col['min'] . '" data-max="' . $col['max'] . '"';
    switch ($col['type']) {
        case 'fk':
            $html = '<select class="form__select" id="' . $id . '" name="' . $name . '"' . $req . '><option value="">Выберите</option>';
            foreach ($fkCache as $k => $label) {
                $html .= '<option value="' . e($k) . '"' . ((string)$k === (string)$value ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            return $html . '</select>';
        case 'textarea':
            return '<textarea class="form__textarea" id="' . $id . '" name="' . $name . '"' . $req . ' maxlength="2000">' . $v . '</textarea>';
        case 'password':
            return '<input class="form__input" id="' . $id . '" name="password" type="password" autocomplete="new-password" maxlength="64"' . ($value === null ? ' required' : '') . ' data-pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" data-pattern-message="Не менее 8 символов: латиница и цифры.">';
        case 'date':
            $v = $value ? e(date('d.m.Y', strtotime($value))) : '';
            return '<input class="form__input" id="' . $id . '" name="' . $name . '" inputmode="numeric" maxlength="10" placeholder="дд.мм.гггг" autocomplete="off" data-date' . $req . ' value="' . $v . '">';
        case 'int':
        case 'decimal':
            return '<input class="form__input" id="' . $id . '" name="' . $name . '" inputmode="decimal" data-number-only' . $req . $attrs . ' value="' . $v . '">';
        case 'email':
            return '<input class="form__input" id="' . $id . '" name="' . $name . '" type="email"' . $req . $attrs . ' value="' . $v . '">';
        case 'url':
            return '<input class="form__input" id="' . $id . '" name="' . $name . '" type="url"' . $req . $attrs . ' value="' . $v . '">';
        default:
            return '<input class="form__input" id="' . $id . '" name="' . $name . '"' . $req . $attrs . ' value="' . $v . '">';
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="admin">
    <aside class="admin__aside">
        <nav class="side-menu" aria-label="Таблицы базы данных">
            <p class="side-menu__title">Таблицы</p>
<?php foreach (ADMIN_TABLES as $key => $t): ?>
            <a class="side-menu__link<?= $table === $key ? ' side-menu__link--active' : '' ?>" href="admin.php?table=<?= $key ?>"><?= e($t['title']) ?></a>
<?php endforeach; ?>
            <p class="side-menu__title">Представления</p>
<?php foreach (ADMIN_VIEWS as $key => $v): ?>
            <a class="side-menu__link<?= $view === $key ? ' side-menu__link--active' : '' ?>" href="admin.php?view=<?= $key ?>"><?= e($v['title']) ?></a>
<?php endforeach; ?>
        </nav>
    </aside>

    <div class="admin__main">
<?php if (isset(ADMIN_VIEWS[$view])):
    $cfg = ADMIN_VIEWS[$view];
    $filter = trim($_GET['f'] ?? '');
    if (!empty($cfg['filter']) && $filter !== '') {
        $rows = db_all("SELECT * FROM $view WHERE {$cfg['filter']} = ?", [$filter]);
    } else {
        $rows = db_all("SELECT * FROM $view");
    }
    $cols = $rows ? array_keys($rows[0]) : [];
?>
        <header class="admin__head">
            <div>
                <p class="admin__kind">Представление <code><?= e($view) ?></code>, только просмотр</p>
                <h1 class="admin__title"><?= e($cfg['title']) ?></h1>
            </div>
<?php if (!empty($cfg['filter'])): ?>
            <form class="admin__filter" method="get">
                <input type="hidden" name="view" value="<?= e($view) ?>">
                <label class="form__label" for="f"><?= e($cfg['filter_label']) ?></label>
                <select class="form__select" id="f" name="f" onchange="this.form.submit()">
                    <option value="">Все</option>
<?php foreach (db()->query($cfg['options'])->fetchAll(PDO::FETCH_COLUMN) as $opt): ?>
                    <option<?= $opt === $filter ? ' selected' : '' ?>><?= e($opt) ?></option>
<?php endforeach; ?>
                </select>
                <noscript><button class="button button--small" type="submit">Показать</button></noscript>
            </form>
<?php endif; ?>
        </header>
        <div class="table-wrap">
            <table class="table">
                <thead class="table__head"><tr>
<?php foreach ($cols as $c): ?>
                    <th class="table__cell table__cell--head" scope="col"><?= e(VIEW_COLUMNS[$c] ?? $c) ?></th>
<?php endforeach; ?>
                </tr></thead>
                <tbody>
<?php if (!$rows): ?>
                    <tr><td class="table__empty">Записей нет.</td></tr>
<?php endif; ?>
<?php foreach ($rows as $r): ?>
                    <tr class="table__row">
<?php foreach ($r as $c => $val): ?>
                        <td class="table__cell"><?= str_ends_with($c, '_date') ? date_ru($val) : ($c === 'total_price' || $c === 'price' ? money($val) : e($val ?? '—')) ?></td>
<?php endforeach; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="admin__count">Записей: <?= count($rows) ?></p>

<?php else:
    $cfg = ADMIN_TABLES[$table];
    $pk = $cfg['pk'];
    $fk = [];
    foreach ($cfg['columns'] as $name => $col) {
        if ($col['type'] === 'fk') $fk[$name] = fk_options($col['ref']);
    }
    $rows = db_all("SELECT * FROM $table ORDER BY $pk");
    $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
    $editRow = $editId ? db_one("SELECT * FROM $table WHERE $pk = ?", [$editId]) : null;
?>
        <header class="admin__head">
            <div>
                <p class="admin__kind">Таблица <code><?= e($table) ?></code></p>
                <h1 class="admin__title"><?= e($cfg['title']) ?></h1>
            </div>
            <a class="button button--small" href="admin.php?table=<?= $table ?>#record-form">Добавить запись</a>
        </header>

        <div class="table-wrap">
            <table class="table">
                <thead class="table__head"><tr>
                    <th class="table__cell table__cell--head" scope="col"><span class="visually-hidden">Действия</span></th>
                    <th class="table__cell table__cell--head" scope="col">ID</th>
<?php foreach ($cfg['columns'] as $col): ?>
                    <th class="table__cell table__cell--head" scope="col"><?= e($col['label']) ?></th>
<?php endforeach; ?>
                </tr></thead>
                <tbody>
<?php if (!$rows): ?>
                    <tr><td class="table__empty" colspan="<?= count($cfg['columns']) + 2 ?>">Записей нет. Добавьте первую через форму ниже.</td></tr>
<?php endif; ?>
<?php foreach ($rows as $r): ?>
                    <tr class="table__row<?= $editId === (int)$r[$pk] ? ' admin__row--editing' : '' ?>">
                        <td class="table__cell table__cell--actions">
                            <a class="admin__action" href="admin.php?table=<?= $table ?>&amp;edit=<?= (int)$r[$pk] ?>#record-form">Изменить</a>
                            <form class="admin__delete" action="handlers/admin-delete.php" method="post" data-confirm="Удалить запись №<?= (int)$r[$pk] ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="table" value="<?= $table ?>">
                                <input type="hidden" name="id" value="<?= (int)$r[$pk] ?>">
                                <button class="admin__action admin__action--danger" type="submit">Удалить</button>
                            </form>
                        </td>
                        <td class="table__cell"><?= (int)$r[$pk] ?></td>
<?php foreach ($cfg['columns'] as $name => $col): ?>
                        <td class="table__cell"><?= cell_value($col, $r[$name], $fk[$name] ?? []) ?></td>
<?php endforeach; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="admin__count">Записей: <?= count($rows) ?></p>

        <section class="admin__form-box" id="record-form">
            <h2 class="admin__subtitle"><?= $editRow ? 'Изменить запись №' . (int)$editRow[$pk] : 'Новая запись' ?></h2>
            <form class="form admin__form" action="handlers/admin-save.php" method="post" data-validate>
                <?= csrf_field() ?>
                <input type="hidden" name="table" value="<?= $table ?>">
                <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow[$pk] : '' ?>">
                <div class="admin__fields">
<?php foreach ($cfg['columns'] as $name => $col):
    if (!empty($col['auto'])) continue;
    $val = $editRow[$name] ?? null;
    if ($col['type'] === 'password') $val = $editRow ? '' : null; ?>
                    <div class="form__field<?= $col['type'] === 'textarea' ? ' admin__field--wide' : '' ?>">
                        <label class="form__label" for="f-<?= $name ?>"><?= e($col['label']) ?><?= !empty($col['required']) || ($col['type'] === 'password' && !$editRow) ? ' <span class="form__required">*</span>' : '' ?></label>
                        <?= input_for($name, $col, $val, $fk[$name] ?? []) ?>
<?php if ($col['type'] === 'password' && $editRow): ?>
                        <p class="form__hint">Оставьте пустым, чтобы не менять пароль.</p>
<?php endif; ?>
                        <p class="form__error"></p>
                    </div>
<?php endforeach; ?>
                </div>
                <div class="form__actions">
                    <button class="button" type="submit"><?= $editRow ? 'Сохранить изменения' : 'Добавить запись' ?></button>
<?php if ($editRow): ?>
                    <a class="button button--ghost" href="admin.php?table=<?= $table ?>">Отмена</a>
<?php endif; ?>
                </div>
            </form>
        </section>
<?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
