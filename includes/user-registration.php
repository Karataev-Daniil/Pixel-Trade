<?php
// user-registration.php

function kayo_custom_registration_form() {
    ?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <label for="username">Имя пользователя</label>
        <input type="text" name="username" required>

        <label for="email">Email</label>
        <input type="email" name="email" required>

        <label for="password">Пароль</label>
        <input type="password" name="password" required>

        <input type="submit" name="kayo_register" value="Зарегистрироваться как продавец">
    </form>
    <?php
}
add_shortcode('kayo_registration_form', 'kayo_custom_registration_form');

function kayo_handle_registration() {
    if (isset($_POST['kayo_register'])) {
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);

        $errors = new WP_Error();

        if (username_exists($username) || email_exists($email)) {
            $errors->add('user_exists', 'Имя пользователя или email уже заняты.');
        }

        if (empty($errors->errors)) {
            $user_id = wp_create_user($username, $password, $email);

            wp_update_user(['ID' => $user_id, 'role' => 'regular_user']);
            update_user_meta($user_id, 'pending_role', 'seller');

            echo '<p>Регистрация успешна! После одобрения администратора вы сможете публиковать товары.</p>';
        } else {
            foreach ($errors->get_error_messages() as $error) {
                echo '<p>' . $error . '</p>';
            }
        }
    }
}
add_action('init', 'kayo_handle_registration');