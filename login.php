<?php
$page = ['title' => 'Вход', 'id' => 'auth', 'blocks' => ['form'], 'scripts' => ['blocks/form-validate']];
require_once __DIR__ . '/includes/helpers.php';
if (current_user()) {
    redirect(is_admin() ? 'admin.php' : 'account.php');
}
$next = safe_next($_GET['next'] ?? '', '');
require __DIR__ . '/includes/header.php';
?>
<div class="container auth">
    <div class="auth__card">
        <h1 class="auth__title">Вход</h1>
        <p class="auth__lead">Войдите, чтобы оформить заказ и посмотреть его статус.</p>
        <form class="form" action="handlers/login.php" method="post" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <div class="form__field">
                <label class="form__label" for="email">Электронная почта <span class="form__required">*</span></label>
                <input class="form__input" id="email" name="email" type="email" autocomplete="email" required maxlength="100" value="<?= old('email') ?>">
                <p class="form__error"></p>
            </div>
            <div class="form__field">
                <label class="form__label" for="password">Пароль <span class="form__required">*</span></label>
                <input class="form__input" id="password" name="password" type="password" autocomplete="current-password" required maxlength="64">
                <p class="form__error"></p>
            </div>
            <button class="button button--block" type="submit">Войти</button>
        </form>
        <p class="auth__switch">Нет аккаунта? <a href="register.php<?= $next ? '?next=' . urlencode($next) : '' ?>">Зарегистрироваться</a></p>
    </div>
</div>
<?php clear_old(); require __DIR__ . '/includes/footer.php'; ?>
