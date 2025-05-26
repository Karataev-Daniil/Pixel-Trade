<?php
/* Template Name: Мои Товары */

if (isset($_GET['delete_product'])) {
    $product_id = intval($_GET['delete_product']);
    if (current_user_can('edit_post', $product_id)) {
        wp_trash_post($product_id);
        wp_redirect(remove_query_arg(['delete_product']));
        exit;
    }
}

get_header();

if (!is_user_logged_in()) {
    echo '<div class="container my-products-wrapper"><p class="body-medium-regular">Пожалуйста, <a href="' . wp_login_url() . '" class="link-medium-underline">войдите</a>, чтобы просмотреть свои товары.</p></div>';
    get_footer();
    exit;
}

$current_user_id = get_current_user_id();
$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'author'         => $current_user_id,
    'post_status'    => ['publish', 'draft', 'pending'],
];
$products = new WP_Query($args);
?>
<div class="dashboard__wrapper">
    <div class="container-medium">
        <div class="dashboard-header">
            <h2 class="title-large">Мои товары</h2>
            <a href="?add_product=true" class="primary-button-medium button-medium">Добавить товар</a>
        </div>

        <?php if ($products->have_posts()): ?>
            <ul class="product-list">
                <?php while ($products->have_posts()): $products->the_post(); ?>
                    <li class="product-item">
                        <div class="product-card">
                            <?php
                            $lang = $GLOBALS['language'] ?? 'ru';

                            // Получаем переводы из мета
                            $title_translations = [
                                'ru' => get_the_title(),
                                'en' => get_post_meta(get_the_ID(), '_product_title_en', true),
                                'ro' => get_post_meta(get_the_ID(), '_product_title_ro', true),
                            ];
                            $content_translations = [
                                'ru' => get_the_content(),
                                'en' => get_post_meta(get_the_ID(), '_product_content_en', true),
                                'ro' => get_post_meta(get_the_ID(), '_product_content_ro', true),
                            ];
                        
                            // Безопасный вывод
                            $translated_title = esc_html($title_translations[$lang] ?? $title_translations['ru']);
                            $translated_content = esc_html($content_translations[$lang] ?? $content_translations['ru']);
                            ?>

                            <h3 class="title-medium"><?= $translated_title; ?></h3>
                        
                            <div class="thumbnail">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail'); ?>
                            </div>
                        
                            <p class="body-small"><?= $translated_content; ?></p>
                        
                            <div class="product-meta">
                                <?php
                                $price = get_post_meta(get_the_ID(), 'product_price', true);
                                if ($price) {
                                    echo '<p class="product-price">Цена: ' . esc_html($price) . ' ₽</p>';
                                }
                            
                                $terms = get_the_terms(get_the_ID(), 'product_cat');
                                if ($terms && !is_wp_error($terms)) {
                                    echo '<p>Категория: ' . esc_html($terms[0]->name) . '</p>';
                                }
                            
                                $views = get_post_meta(get_the_ID(), 'product_views', true);
                                $views = $views ? (int)$views : 0;
                                echo '<p>Просмотров: ' . $views . '</p>';
                            
                                echo '<p>Опубликовано: ' . get_the_date('d.m.Y') . '</p>';
                                ?>
                            </div>
                            
                            <div class="product-actions">
                                <a href="<?php echo esc_url(add_query_arg('edit', '1', get_permalink())); ?>" class="secondary-button-small button-small">Редактировать</a>
                                <a href="?delete_product=<?php the_ID(); ?>" onclick="return confirm('Удалить товар?')" class="accent-button-small button-small">Удалить</a>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="body-medium-regular no-products">У вас пока нет товаров.</p>
        <?php endif; ?>


        <!-- <?php
        $current_user_id = get_current_user_id();
                
        $editing = false;
        $product_id = 0;
        $title = '';
        $content = '';
        $status = 'pending';
        $thumbnail_id = '';
        $gallery_ids = [];
        $categories = [];
        $price = '';
                
        // Функция перевода через OpenAI
        function translate_text_openai($text, $target_lang) {
            if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) return '';

            if (!function_exists('wp_remote_post')) {
                error_log('wp_remote_post is not available');
                return '';
            }
        
            $prompt = "Translate the following text into $target_lang:\n\n$text";
        
            $data = [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful translator.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
            ];
        
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . OPENAI_API_KEY,
                ],
                'body' => json_encode($data),
                'timeout' => 20, // увеличить таймаут, если сервер медленный
            ]);
        
            if (is_wp_error($response)) {
                error_log('OpenAI API error: ' . $response->get_error_message());
                return '';
            }
        
            $body = json_decode(wp_remote_retrieve_body($response), true);
        
            if (!isset($body['choices'][0]['message']['content'])) {
                error_log('OpenAI API response invalid: ' . print_r($body, true));
                return '';
            }
        
            return trim($body['choices'][0]['message']['content']);
        }

        
        // Обработка отправки формы
        if (isset($_POST['submit_product']) && wp_verify_nonce($_POST['product_form_nonce'], 'save_product_form')) {
            $current_user_id = get_current_user_id();
            $title = sanitize_text_field($_POST['product_title']);
            $content = sanitize_textarea_field($_POST['product_content']);
            $product_id = intval($_POST['product_id']);
            $post_status = in_array($_POST['product_status'], ['publish', 'pending', 'draft']) ? $_POST['product_status'] : 'pending';

            $post_data = [
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => $post_status,
                'post_type'    => 'product',
                'post_author'  => $current_user_id,
            ];
        
            if ($product_id) {
                $post_data['ID'] = $product_id;
                $product_id = wp_update_post($post_data);
            } else {
                $product_id = wp_insert_post($post_data);
            }
        
            if ($product_id) {
                // Обработка перевода через OpenAI
                $title_en = translate_text_openai($title, 'English');
                $title_ro = translate_text_openai($title, 'Romanian');
                $content_en = translate_text_openai($content, 'English');
                $content_ro = translate_text_openai($content, 'Romanian');
            
                update_post_meta($product_id, '_product_title_en', $title_en);
                update_post_meta($product_id, '_product_title_ro', $title_ro);
                update_post_meta($product_id, '_product_content_en', $content_en);
                update_post_meta($product_id, '_product_content_ro', $content_ro);
            }
        
            // Обработка миниатюры
            if (!empty($_FILES['product_thumbnail']['name'])) {
                if (!function_exists('media_handle_upload')) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                }

                $thumbnail_id = media_handle_upload('product_thumbnail', $product_id);
                if (!is_wp_error($thumbnail_id)) {
                    set_post_thumbnail($product_id, $thumbnail_id);
                }
            }
        
            // Обработка галереи
            if (!empty($_FILES['product_gallery']['name'][0])) {
                $gallery_ids = [];
                foreach ($_FILES['product_gallery']['name'] as $key => $value) {
                    if ($_FILES['product_gallery']['name'][$key]) {
                        $file = [
                            'name'     => $_FILES['product_gallery']['name'][$key],
                            'type'     => $_FILES['product_gallery']['type'][$key],
                            'tmp_name' => $_FILES['product_gallery']['tmp_name'][$key],
                            'error'    => $_FILES['product_gallery']['error'][$key],
                            'size'     => $_FILES['product_gallery']['size'][$key],
                        ];
                        $_FILES['single_gallery_image'] = $file;
                        $attach_id = media_handle_upload('single_gallery_image', $product_id);
                        if (!is_wp_error($attach_id)) {
                            $gallery_ids[] = $attach_id;
                        }
                    }
                }
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
            }
        
            // Обработка категорий
            if (!empty($_POST['product_categories']) && is_array($_POST['product_categories'])) {
                $category_ids = array_map('intval', $_POST['product_categories']);
                wp_set_object_terms($product_id, $category_ids, 'product_cat');
            }
        
            // Обработка цены
            if (isset($_POST['product_price'])) {
                $price = floatval($_POST['product_price']);
                update_post_meta($product_id, 'product_price', $price);
            }
        
            wp_redirect(get_permalink());
            exit;
        }
        
        // Подгрузка данных, если редактируем
        if (isset($_GET['edit_product'])) {
            $product_id = intval($_GET['edit_product']);
            $post = get_post($product_id);
            if ($post && $post->post_author == $current_user_id) {
                $editing = true;
                $title = esc_attr($post->post_title);
                $content = esc_textarea($post->post_content);
                $status = $post->post_status;
                $thumbnail_id = get_post_thumbnail_id($product_id);
                $gallery_ids = explode(',', get_post_meta($product_id, '_product_image_gallery', true));
                $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
                $price = get_post_meta($product_id, 'product_price', true);
            }
        }
        
        // Вывод формы
        if ($editing || isset($_GET['add_product'])):
            $all_categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ]);
        ?>
            <div class="product-form-wrapper">
                <h3 class="title-medium"><?php echo $editing ? 'Редактировать товар' : 'Добавить товар'; ?></h3>
                <form method="post" enctype="multipart/form-data" class="product-form">
                    <?php wp_nonce_field('save_product_form', 'product_form_nonce'); ?>
        
                    <div class="form-group">
                        <label for="product_title" class="label-large">Название товара</label>
                        <input type="text" id="product_title" name="product_title" value="<?php echo $title; ?>" required class="body-medium-regular">
                    </div>
        
                    <div class="form-group">
                        <label for="product_content" class="label-large">Описание</label>
                        <textarea id="product_content" name="product_content" rows="5" required class="body-medium-regular"><?php echo $content; ?></textarea>
                    </div>
        
                    <div class="form-group">
                        <label class="label-large">Категории</label>
                        <?php foreach ($all_categories as $cat): ?>
                            <label>
                                <input type="checkbox" name="product_categories[]" value="<?php echo $cat->term_id; ?>" <?php checked(in_array($cat->term_id, $categories)); ?>>
                                <?php echo esc_html($cat->name); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                        
                    <div class="form-group">
                        <label class="label-large">Статус</label>
                        <select name="product_status" class="body-medium-regular">
                            <option value="publish" <?php selected($status, 'publish'); ?>>Опубликовано</option>
                            <option value="pending" <?php selected($status, 'pending'); ?>>На модерации</option>
                            <option value="draft" <?php selected($status, 'draft'); ?>>Черновик</option>
                        </select>
                    </div>
                        
                    <div class="form-group">
                        <label class="label-large">Миниатюра</label>
                        <input type="file" name="product_thumbnail" accept="image/*" class="body-medium-regular">
                        <?php if ($thumbnail_id): echo wp_get_attachment_image($thumbnail_id, 'thumbnail'); endif; ?>
                    </div>
                        
                    <div class="form-group">
                        <label class="label-large">Галерея</label>
                        <input type="file" name="product_gallery[]" accept="image/*" multiple class="body-medium-regular">
                        <?php
                        foreach ($gallery_ids as $id) {
                            if ($id) echo wp_get_attachment_image($id, 'thumbnail');
                        }
                        ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_price" class="label-large">Цена (₽)</label>
                        <input type="number" step="0.01" id="product_price" name="product_price" value="<?php echo esc_attr($price); ?>" required class="body-medium-regular">
                    </div>
                    
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-actions">
                        <input type="submit" name="submit_product" class="primary-button-large button-large" value="<?php echo $editing ? 'Обновить' : 'Создать'; ?>">
                    </div>
                </form>
            </div>
        <?php endif; ?> -->
    </div>    
</div>

<?php
wp_reset_postdata();
get_footer();
?>
