<?php
/* Template Name: Создание товара */

get_header();

if (!is_user_logged_in()) {
    echo '<p>' . t('Только для зарегистрированных пользователей.', 'Only for registered users.', 'Doar pentru utilizatori înregistrați.') . '</p>';
    get_footer();
    exit;
}

$current_user_id = get_current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product'])) {
    if (!isset($_POST['product_form_nonce']) || !wp_verify_nonce($_POST['product_form_nonce'], 'create_product_form')) {
        echo '<p>' . t('Ошибка безопасности.', 'Security error.', 'Eroare de securitate.') . '</p>';
    } else {
        $post_data = [
            'post_title'   => sanitize_text_field($_POST['product_title']),
            'post_content' => sanitize_textarea_field($_POST['product_content']),
            'post_status'  => sanitize_text_field($_POST['product_status']),
            'post_type'    => 'product',
            'post_author'  => $current_user_id,
        ];

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            update_post_meta($post_id, 'product_price', floatval($_POST['product_price']));
            update_post_meta($post_id, '_title_en', sanitize_text_field($_POST['title_en']));
            update_post_meta($post_id, '_description_en', sanitize_textarea_field($_POST['description_en']));
            update_post_meta($post_id, '_title_ro', sanitize_text_field($_POST['title_ro']));
            update_post_meta($post_id, '_description_ro', sanitize_textarea_field($_POST['description_ro']));

            if (!empty($_POST['selected_categories'])) {
                wp_set_post_terms($post_id, array_map('intval', $_POST['selected_categories']), 'product_cat');
            }

            if (!empty($_FILES['product_gallery']['name'][0])) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $attachment_ids = [];
                foreach ($_FILES['product_gallery']['name'] as $key => $value) {
                    if ($_FILES['product_gallery']['name'][$key]) {
                        $file = [
                            'name'     => $_FILES['product_gallery']['name'][$key],
                            'type'     => $_FILES['product_gallery']['type'][$key],
                            'tmp_name' => $_FILES['product_gallery']['tmp_name'][$key],
                            'error'    => $_FILES['product_gallery']['error'][$key],
                            'size'     => $_FILES['product_gallery']['size'][$key],
                        ];
                        $_FILES['upload_attachment'] = $file;
                        $attachment_id = media_handle_upload('upload_attachment', $post_id);
                        if (!is_wp_error($attachment_id)) {
                            $attachment_ids[] = $attachment_id;
                        }
                    }
                }
                if (!empty($attachment_ids)) {
                    set_post_thumbnail($post_id, $attachment_ids[0]);
                    update_post_meta($post_id, '_product_image_gallery', implode(',', $attachment_ids));
                }
            }

            echo '<p>' . t('Объявление создано!', 'Listing created!', 'Anunțul a fost creat!') . '</p>';
        } else {
            echo '<p>' . t('Ошибка создания.', 'Creation error.', 'Eroare la creare.') . '</p>';
        }
    }
}
?>

<div class="product__wrapper create">
    <div class="container-medium">
        <div class="product-create">
            <h3 class="product-create__title display-small"><?php echo t('Создать объявление', 'Create Listing', 'Creează Anunț'); ?></h3>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('create_product_form', 'product_form_nonce'); ?>

                <div class="form-group">
                    <h4 class="label-large"><?php echo t('Категории', 'Categories', 'Categorii'); ?></h4>
                    <div class="category-selectors" id="category-selectors" data-restored="1">
                        <?php
                        $selected_categories = isset($selected_categories) ? $selected_categories : [];
                        $sorted_term_ids = sort_categories_by_hierarchy($selected_categories);
                        ?>
                        <div id="preselected-categories" data-terms="<?php echo esc_attr(json_encode($sorted_term_ids)); ?>"></div>
                        <script>
                            const translations = {
                                selectCategory: <?php echo json_encode(t('Выберите категорию', 'Select category', 'Selectați categoria')); ?>,
                                labelLevel0: <?php echo json_encode(t('Категория', 'Category', 'Categorie')); ?>,
                                labelLevel1: <?php echo json_encode(t('Подкатегория', 'Subcategory', 'Subcategorie')); ?>,
                                labelLevel2: <?php echo json_encode(t('Под-подкатегория', 'Sub-subcategory', 'Sub-subcategorie')); ?>,
                            };
                        </script>
                    </div>
                </div>



                <div class="form-group tabs">
                    <?php $language = $GLOBALS['language']; ?>
                    <ul class="tab-buttons">
                        <li class="tab-btn body-small-semibold <?php if ($language === 'ru') echo 'active'; ?>" data-tab="tab-ru">RU</li>
                        <li class="tab-btn body-small-semibold <?php if ($language === 'en') echo 'active'; ?>" data-tab="tab-en">EN</li>
                        <li class="tab-btn body-small-semibold <?php if ($language === 'ro') echo 'active'; ?>" data-tab="tab-ro">RO</li>
                    </ul>

                    <div class="tab-content <?php if ($language === 'ru') echo 'active'; ?>" id="tab-ru">
                        <h4 class="label-large"><?php echo t('Название', 'Title', 'Titlu'); ?></h4>
                        <input type="text" class="form-input input-secondary body-medium-regular" name="product_title"
                               placeholder="<?php echo t('Введите название', 'Enter title', 'Introduceți titlul'); ?>">
                        <h4 class="label-large"><?php echo t('Описание', 'Description', 'Descriere'); ?></h4>
                        <textarea name="product_content" rows="5" class="form-textarea input-tertiary body-medium-regular"
                                  placeholder="<?php echo t('Введите описание', 'Enter description', 'Introduceți descrierea'); ?>"></textarea>
                    </div>

                    <div class="tab-content <?php if ($language === 'en') echo 'active'; ?>" id="tab-en">
                        <h4 class="label-large"><?php echo t('Название', 'Title', 'Titlu'); ?></h4>
                        <input type="text" class="form-input input-secondary body-medium-regular" name="title_en"
                               placeholder="<?php echo t('Введите название', 'Enter title', 'Introduceți titlul'); ?>">
                        <h4 class="label-large"><?php echo t('Описание', 'Description', 'Descriere'); ?></h4>
                        <textarea name="description_en" rows="5" class="form-textarea input-tertiary body-medium-regular"
                                  placeholder="<?php echo t('Введите описание', 'Enter description', 'Introduceți descrierea'); ?>"></textarea>
                    </div>

                    <div class="tab-content <?php if ($language === 'ro') echo 'active'; ?>" id="tab-ro">
                        <h4 class="label-large"><?php echo t('Название', 'Title', 'Titlu'); ?></h4>
                        <input type="text" class="form-input input-secondary body-medium-regular" name="title_ro"
                               placeholder="<?php echo t('Введите название', 'Enter title', 'Introduceți titlul'); ?>">
                        <h4 class="label-large"><?php echo t('Описание', 'Description', 'Descriere'); ?></h4>
                        <textarea name="description_ro" rows="5" class="form-textarea input-tertiary body-medium-regular"
                                  placeholder="<?php echo t('Введите описание', 'Enter description', 'Introduceți descrierea'); ?>"></textarea>
                    </div>

                    <div class="translation-button">
                        <div id="translation-message" class="form-message body-medium-regular"></div>
                        <button type="button" class="button secondary-button-small generate-translation" onclick="generateTranslations()">
                            <?php echo t('Сгенерировать переводы', 'Generate Translations', 'Generează traduceri'); ?>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo t('Цена (леи)', 'Price (lei)', 'Preț (lei)'); ?></label>
                    <input type="number" step="0.01" name="product_price" class="form-input input-secondary body-medium-regular" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo t('Изображения (до 6)', 'Images (up to 6)', 'Imagini (până la 6)'); ?></label>
                    <input type="file" name="product_gallery[]" accept="image/*" multiple class="form-file">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo t('Статус', 'Status', 'Stare'); ?></label>
                    <select name="product_status" class="form-select">
                        <option value="draft"><?php echo t('Черновик', 'Draft', 'Schiță'); ?></option>
                        <option value="publish"><?php echo t('Опубликован', 'Published', 'Publicat'); ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="submit" name="submit_product" value="<?php echo t('Создать', 'Create', 'Creează'); ?>" class="form-submit">
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');

            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });
});
</script>

<?php get_footer(); ?>
