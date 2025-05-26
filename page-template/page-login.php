<?php
/* Template Name: Вход */
get_header();
?>

<div class="auth-wrapper">
    <div class="container-xxsmall">
        <div class="login-form">
            <h2 class="title-large">Вход</h2>

            <?php if (isset($_GET['login']) && $_GET['login'] === 'failed') : ?>
                <p class="body-small-semibold" style="color: red;">Неверное имя пользователя или пароль.</p>
            <?php endif; ?>

            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="form-ui">
                <label for="username" class="label-large">Имя пользователя или email</label>
                <input type="text" name="username" required class="input-ui">

                <label for="password" class="label-large">Пароль</label>
                <input type="password" name="password" required class="input-ui">

                <input type="submit" name="kayo_login" value="Войти" class="primary-button-medium button-medium">
            </form>

            <p class="body-small-regular">
                Нет аккаунта?
                <a href="<?php echo site_url('/register'); ?>" class="link-medium-underline">Зарегистрируйтесь</a>
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
