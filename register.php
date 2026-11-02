<?php
$page = ['title' => 'Регистрация', 'id' => 'auth', 'blocks' => ['form'], 'scripts' => ['blocks/form-validate', 'blocks/phone-mask']];
require_once __DIR__ . '/includes/helpers.php';
if (current_user()) {
    redirect('account.php');
}
$next = safe_next($_GET['next'] ?? '', '');
require __DIR__ . '/includes/header.php';
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
function field_error(array $errors, string $name): string
{
    return isset($errors[$name]) ? e($errors[$name]) : '';
}
?>
<div class="container auth">
    <div class="auth__card auth__card--wide">
        <h1 class="auth__title">Регистрация</h1>
        <p class="auth__lead">Аккаунт нужен, чтобы оформлять заказы, хранить мерки и оставлять отзывы.</p>
        <form class="form" action="handlers/register.php" method="post" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <div class="form__row">
                <div class="form__field<?= isset($errors['last_name']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="last_name">Фамилия <span class="form__required">*</span></label>
                    <input class="form__input" id="last_name" name="last_name" autocomplete="family-name" required maxlength="50" data-letters-only data-pattern="^[A-Za-zА-Яа-яЁё\- ]{2,50}$" data-pattern-message="Только буквы и дефис, от 2 символов." value="<?= old('last_name') ?>">
                    <p class="form__error"><?= field_error($errors, 'last_name') ?></p>
                </div>
                <div class="form__field<?= isset($errors['first_name']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="first_name">Имя <span class="form__required">*</span></label>
                    <input class="form__input" id="first_name" name="first_name" autocomplete="given-name" required maxlength="50" data-letters-only data-pattern="^[A-Za-zА-Яа-яЁё\- ]{2,50}$" data-pattern-message="Только буквы и дефис, от 2 символов." value="<?= old('first_name') ?>">
                    <p class="form__error"><?= field_error($errors, 'first_name') ?></p>
                </div>
            </div>
            <div class="form__row">
                <div class="form__field<?= isset($errors['phone']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="phone">Телефон <span class="form__required">*</span></label>
                    <input class="form__input" id="phone" name="phone" type="tel" autocomplete="tel" required placeholder="+7 (___) ___-__-__" data-phone-mask data-pattern="^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$" data-pattern-message="Введите номер полностью: +7 (900) 123-45-67." value="<?= old('phone') ?>">
                    <p class="form__error"><?= field_error($errors, 'phone') ?></p>
                </div>
                <div class="form__field<?= isset($errors['email']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="email">Электронная почта <span class="form__required">*</span></label>
                    <input class="form__input" id="email" name="email" type="email" autocomplete="email" required maxlength="100" value="<?= old('email') ?>">
                    <p class="form__error"><?= field_error($errors, 'email') ?></p>
                </div>
            </div>
            <div class="form__row">
                <div class="form__field<?= isset($errors['password']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="password">Пароль <span class="form__required">*</span></label>
                    <input class="form__input" id="password" name="password" type="password" autocomplete="new-password" required maxlength="64" data-pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" data-pattern-message="Не менее 8 символов: латинские буквы и хотя бы одна цифра.">
                    <p class="form__hint">Не менее 8 символов, латинские буквы и цифры.</p>
                    <p class="form__error"><?= field_error($errors, 'password') ?></p>
                </div>
                <div class="form__field<?= isset($errors['password_confirm']) ? ' form__field--invalid' : '' ?>">
                    <label class="form__label" for="password_confirm">Повторите пароль <span class="form__required">*</span></label>
                    <input class="form__input" id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required maxlength="64" data-match="password">
                    <p class="form__error"><?= field_error($errors, 'password_confirm') ?></p>
                </div>
            </div>
            <div class="form__field<?= isset($errors['consent']) ? ' form__field--invalid' : '' ?>">
                <label class="form__check"><input type="checkbox" name="consent" value="1" required> Согласен на обработку персональных данных для оформления заказов</label>
                <p class="form__error"><?= field_error($errors, 'consent') ?></p>
            </div>
            <button class="button button--block" type="submit">Зарегистрироваться</button>
        </form>
        <p class="auth__switch">Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</div>
<?php clear_old(); require __DIR__ . '/includes/footer.php'; ?>
