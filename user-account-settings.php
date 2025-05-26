<?php
/**
 * Template Name: Настройки аккаунта
 */

get_header();

if (!is_user_logged_in()) {
    echo '<div class="container"><p>Пожалуйста, <a href="' . wp_login_url() . '">войдите</a>, чтобы управлять аккаунтом.</p></div>';
    get_footer();
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_settings_nonce']) && wp_verify_nonce($_POST['user_settings_nonce'], 'save_user_settings')) {
    if (isset($_POST['display_name'])) {
        wp_update_user([
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['display_name']),
        ]);
    }

    if (isset($_POST['user_email']) && is_email($_POST['user_email'])) {
        wp_update_user([
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['user_email']),
        ]);
    }

    if (isset($_POST['region'])) {
        update_user_meta($user_id, 'region', sanitize_text_field($_POST['region']));
    }

    echo '<div class="notice success" style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom: 20px;">Профиль обновлён.</div>';
}

// Получаем обновленные данные
$region = get_user_meta($user_id, 'region', true);
?>

<div class="container" style="max-width: 600px; margin: 40px auto;">
    <h2>Настройки аккаунта</h2>

    <form method="post">
        <?php wp_nonce_field('save_user_settings', 'user_settings_nonce'); ?>

        <p>
            <label for="display_name"><strong>Имя:</strong></label><br>
            <input type="text" name="display_name" id="display_name" value="<?php echo esc_attr($current_user->display_name); ?>" class="widefat" required>
        </p>

        <p>
            <label for="user_email"><strong>Email:</strong></label><br>
            <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" class="widefat" required>
        </p>

        <p>
            <label for="region"><strong>Регион:</strong></label><br>
            <input type="text" name="region" id="region" value="<?php echo esc_attr($region); ?>" class="widefat">
        </p>

        <p>
            <button type="submit" class="button button-primary">💾 Сохранить изменения</button>
        </p>
    </form>
</div>

<?php get_footer(); ?>
