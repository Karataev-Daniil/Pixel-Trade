<?php
/* Template Name: Регистрация */
get_header();
?>

<div class="auth-wrapper">
    <div class="container-xxsmall">
        <div class="register-form">
            <h2 class="title-large">Регистрация</h2>

            <?php if (isset($_GET['register']) && $_GET['register'] === 'exists') : ?>
                <p class="body-small-semibold" style="color: red;">Пользователь с таким email уже существует.</p>
            <?php endif; ?>

            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post" class="form-ui">
                <label for="reg_username" class="label-large">Имя пользователя</label>
                <input type="text" name="reg_username" required class="input-ui">

                <label for="reg_email" class="label-large">Email</label>
                <input type="email" name="reg_email" required class="input-ui">

                <label for="reg_password" class="label-large">Пароль</label>
                <input type="password" name="reg_password" required class="input-ui">

                <input type="submit" name="kayo_register" value="Зарегистрироваться" class="primary-button-medium button-medium">
            </form>

            <p class="body-small-regular">
                Уже есть аккаунт?
                <a href="<?php echo site_url('/login'); ?>" class="link-medium-underline">Войти</a>
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
