<?php
require_once get_template_directory() . '/includes/global/settings.php';
require_once get_template_directory() . '/includes/helpers.php';

require_once get_template_directory() . '/includes/enqueue-assets.php';

require_once get_template_directory() . '/includes/custom-post-types.php';
require_once get_template_directory() . '/includes/user-roles.php';

require_once get_template_directory() . '/includes/user-registration.php';
require_once get_template_directory() . '/includes/user-login.php';
require_once get_template_directory() . '/includes/user-edit-product.php';

require_once get_template_directory() . '/includes/ajax/filter-products.php';

require_once get_template_directory() . '/includes/admin-approval.php';

require_once get_template_directory() . '/includes/openai-api.php';


add_action('wp_ajax_get_subcategories', 'get_subcategories_ajax');
add_action('wp_ajax_nopriv_get_subcategories', 'get_subcategories_ajax');

function get_subcategories_ajax() {
    $parent_id = isset($_GET['parent']) ? intval($_GET['parent']) : 0;

    $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => $parent_id,
    ]);

    $result = [];
    foreach ($terms as $term) {
        $result[] = [
            'term_id' => $term->term_id,
            'name'    => [
                'ru' => $term->name, // по умолчанию, имя терма на русском
                'en' => get_term_meta($term->term_id, 'translation_en', true) ?: $term->name,
                'ro' => get_term_meta($term->term_id, 'translation_ro', true) ?: $term->name,
            ],
        ];
    }

    wp_send_json($result);
}


function sort_categories_by_hierarchy($categories) {
    if (empty($categories)) return [];

    $categories_by_id = [];
    foreach ($categories as $term) {
        $categories_by_id[$term->term_id] = $term;
    }

    $sorted = [];

    // Найдём самого верхнего родителя
    $leaf = null;
    foreach ($categories as $term) {
        if (!array_filter($categories, fn($t) => $t->parent === $term->term_id)) {
            $leaf = $term;
            break;
        }
    }

    // Восстановим путь от листа к корню
    while ($leaf) {
        $sorted[] = $leaf->term_id;
        $leaf = isset($categories_by_id[$leaf->parent]) ? $categories_by_id[$leaf->parent] : null;
    }

    return array_reverse($sorted); // от родителя к потомку
}

// В functions.php
function create_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'private_messages';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        sender_id BIGINT(20) UNSIGNED NOT NULL,
        receiver_id BIGINT(20) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_messages_table'); // или замени __FILE__ на путь к твоей теме

add_action('wp_ajax_send_private_message', function() {
    global $wpdb;
    $sender_id = get_current_user_id();
    $receiver_id = intval($_POST['receiver_id']);
    $message = sanitize_text_field($_POST['message']);

    $wpdb->insert("{$wpdb->prefix}private_messages", [
        'sender_id' => $sender_id,
        'receiver_id' => $receiver_id,
        'message' => $message,
    ]);

    wp_die();
});

add_action('wp_ajax_load_private_messages', function() {
    global $wpdb;
    $current_user = get_current_user_id();
    $receiver_id = intval($_GET['receiver_id']);

    $messages = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}private_messages
        WHERE (sender_id = %d AND receiver_id = %d)
           OR (sender_id = %d AND receiver_id = %d)
        ORDER BY created_at ASC",
        $current_user, $receiver_id, $receiver_id, $current_user
    ));

    foreach ($messages as $msg) {
        $from = ($msg->sender_id == $current_user) ? 'Вы' : 'Он/она';
        echo "<p><strong>$from:</strong> " . esc_html($msg->message) . "</p>";
    }

    wp_die();
});

?>
