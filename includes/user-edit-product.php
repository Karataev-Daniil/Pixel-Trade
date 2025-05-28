<?php
function handle_product_edit_form_submission() {
    if (
        !isset($_POST['submit_product']) ||
        !isset($_POST['product_form_nonce']) ||
        !wp_verify_nonce($_POST['product_form_nonce'], 'save_product_form') ||
        !current_user_can('edit_posts')
    ) {
        return;
    }

    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id || get_post_type($product_id) !== 'product') {
        return;
    }


    if (get_current_user_id() !== (int)get_post_field('post_author', $product_id)) {
        wp_die('У вас нет прав для редактирования этого товара.');
    }

    $post_title    = sanitize_text_field($_POST['product_title'] ?? '');
    $post_content  = sanitize_textarea_field($_POST['product_content'] ?? '');
    $post_status   = sanitize_text_field($_POST['product_status'] ?? 'draft');
    $product_price = sanitize_text_field($_POST['product_price'] ?? '');

    wp_update_post([
        'ID'           => $product_id,
        'post_title'   => $post_title,
        'post_content' => $post_content,
        'post_status'  => $post_status,
    ]);

    update_post_meta($product_id, 'product_price', $product_price);

    if (!empty($_POST['product_categories']) && is_array($_POST['product_categories'])) {
        $category_ids = array_map('intval', $_POST['product_categories']);
        wp_set_post_terms($product_id, $category_ids, 'product_cat');
    } else {
        wp_set_post_terms($product_id, [], 'product_cat');
    }

    if (!function_exists('media_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }

    if (!empty($_FILES['product_thumbnail']['name'])) {
        $thumb_id = media_handle_upload('product_thumbnail', $product_id);
        if (!is_wp_error($thumb_id)) {
            set_post_thumbnail($product_id, $thumb_id);
        }
    }

    $existing_ids = explode(',', get_post_meta($product_id, '_product_image_gallery', true) ?: '');

    if (!empty($_POST['remove_gallery_ids']) && is_array($_POST['remove_gallery_ids'])) {
        $remove_ids = array_map('intval', $_POST['remove_gallery_ids']);
        $existing_ids = array_diff($existing_ids, $remove_ids);
    }

    $newly_uploaded_ids = [];
    $current_count = count($existing_ids);

    if (!empty($_FILES['product_gallery']['name'][0])) {
        foreach ($_FILES['product_gallery']['name'] as $i => $name) {
            if ($current_count >= 6) break;

            if ($_FILES['product_gallery']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name'     => $_FILES['product_gallery']['name'][$i],
                    'type'     => $_FILES['product_gallery']['type'][$i],
                    'tmp_name' => $_FILES['product_gallery']['tmp_name'][$i],
                    'error'    => $_FILES['product_gallery']['error'][$i],
                    'size'     => $_FILES['product_gallery']['size'][$i],
                ];
                $_FILES['single_upload'] = $file;

                $attachment_id = media_handle_upload('single_upload', $product_id);
                if (!is_wp_error($attachment_id)) {
                    $newly_uploaded_ids[] = $attachment_id;
                    $existing_ids[] = $attachment_id;
                    $current_count++;
                }
            }
        }
    }

    if (!empty($_POST['gallery_order'])) {
        $ordered = explode(',', $_POST['gallery_order']);
        $final_order = [];

        foreach ($ordered as $id) {
            if (strpos($id, 'new-') === 0) {
                $new_id = array_shift($newly_uploaded_ids);
                if ($new_id) {
                    $final_order[] = $new_id;
                }
            } elseif (in_array((int)$id, $existing_ids)) {
                $final_order[] = (int)$id;
            }
        }

        $existing_ids = $final_order;
    }

    $existing_ids = array_unique(array_filter($existing_ids));

    // Обработка выбранной миниатюры
if (!empty($_POST['main_thumbnail_id'])) {
    $main_thumb_id = sanitize_text_field($_POST['main_thumbnail_id']);

    // Если это новый ID (например, "new-..."), нужно заменить его на реальный attachment ID из загруженных
    if (strpos($main_thumb_id, 'new-') === 0 && !empty($newly_uploaded_ids)) {
        // Порядок совпадает с gallery_order, поэтому можно сопоставить по очереди
        $main_thumb_id = reset($newly_uploaded_ids);
    }

    $main_thumb_id = (int) $main_thumb_id;

    if (in_array($main_thumb_id, $existing_ids)) {
        set_post_thumbnail($product_id, $main_thumb_id);
    }
}


    if (!empty($existing_ids)) {
        if (!has_post_thumbnail($product_id) && !empty($existing_ids)) {
            set_post_thumbnail($product_id, $existing_ids[0]);
        } // Установить первую как thumbnail
        update_post_meta($product_id, '_product_image_gallery', implode(',', $existing_ids));
    } else {
        delete_post_meta($product_id, '_product_image_gallery');
        delete_post_thumbnail($product_id);
    }

    update_post_meta($product_id, '_title_en', sanitize_text_field($_POST['title_en'] ?? ''));
    update_post_meta($product_id, '_title_ro', sanitize_text_field($_POST['title_ro'] ?? ''));
    update_post_meta($product_id, '_description_en', sanitize_textarea_field($_POST['description_en'] ?? ''));
    update_post_meta($product_id, '_description_ro', sanitize_textarea_field($_POST['description_ro'] ?? ''));

    wp_redirect(get_permalink($product_id));
    exit;
}
add_action('init', 'handle_product_edit_form_submission');