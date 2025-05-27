<?php
get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $current_user_id = get_current_user_id();
        $post_author_id = get_the_author_meta('ID');
        $is_editing = isset($_GET['edit']) && $_GET['edit'] == 1 && $current_user_id === $post_author_id;

        $product_id = get_the_ID();

            if ($is_editing && $product_id && get_post_type($product_id) === 'product') :
            
                $title = esc_attr(get_the_title($product_id));
                $content = esc_textarea(get_post_field('post_content', $product_id));
                $status = get_post_status($product_id);
                $thumbnail_id = get_post_thumbnail_id($product_id);
                $gallery_ids = explode(',', get_post_meta($product_id, '_product_image_gallery', true));
                $price = get_post_meta($product_id, 'product_price', true);
                $all_categories = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                ]);
                $selected_categories = wp_get_post_terms($product_id, 'product_cat');
            
                $deepest_term = null;
                $max_depth = 0;
            
                foreach ($selected_categories as $term) {
                    $depth = 0;
                    $parent = $term;
                    while ($parent->parent != 0) {
                        $parent = get_term($parent->parent, 'product_cat');
                        $depth++;
                    }
                    if ($depth > $max_depth) {
                        $max_depth = $depth;
                        $deepest_term = $term;
                    }
                }
            ?>     
            <div class="product__wrapper edit">
                <div class="container-medium">
                    <div class="product-edit">
                        <h3 class="product-edit__title display-small"><?php echo t('Редактировать объявление', 'Edit Listing', 'Editează Anunț'); ?></h3>
                        <form method="post" enctype="multipart/form-data">
                            <?php wp_nonce_field('save_product_form', 'product_form_nonce'); ?>

                            <!-- Категории -->
                            <div class="form-group">
                                <div id="category-selectors" data-restored="1">
                                    <?php
                                    $sorted_term_ids = sort_categories_by_hierarchy($selected_categories);
                                    ?>
                                    <div id="preselected-categories" data-terms="<?php echo esc_attr(json_encode($sorted_term_ids)); ?>"></div>
                       
                                </div>
                            </div>

                            <div class="form-group">          
                                <div class="title-translations">
                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Название (ru)', 'Title (ru)', 'Titlu (ru)'); ?></h4>
                                        <div class="input-block">
                                            <input type="text" name="product_title" value="<?php echo $title; ?>" class="form-input input-secondary body-medium-regular" required>
                                            <!-- <label class="form-label label-large"><?php echo t('Название (ru)', 'Title (ru)', 'Titlu (ru)'); ?></label> -->
                                        </div>      
                                    </div>
                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Название (en)', 'Title (en)', 'Titlu (en)'); ?></h4>
                                        <div class="input-block">
                                            <input type="text" name="title_en" value="<?php echo esc_attr(get_post_meta($product_id, '_title_en', true)); ?>" placeholder="<?php echo t('Название на английском', 'English Title', 'Titlu în engleză'); ?>" class="form-input input-secondary body-medium-regular">
                                            <!-- <label class="form-label label-large"><?php echo t('Название (en)', 'Title (en)', 'Titlu (en)'); ?></label> -->
                                        </div>
                                    </div>
                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Название (ro)', 'Title (ro)', 'Titlu (ro)'); ?></h4>
                                        <div class="input-block">
                                            <input type="text" name="title_ro" value="<?php echo esc_attr(get_post_meta($product_id, '_title_ro', true)); ?>" placeholder="<?php echo t('Название на румынском', 'Romanian Title', 'Titlu în română'); ?>" class="form-input input-secondary body-medium-regular">
                                            <!-- <label class="form-label label-large"><?php echo t('Название (ro)', 'Title (ro)', 'Titlu (ro)'); ?></label> -->
                                        </div>
                                    </div>
                                </div>


                                <div class="description-translations">
                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Описание (ru)', 'Description (ru)', 'Descriere (ru)'); ?></h4>
                                        <div class="input-block">
                                            <textarea name="product_content" rows="5" class="form-textarea input-tertiary body-medium-regular" required><?php echo $content; ?></textarea>
                                            <!-- <label class="form-label label-large"><?php echo t('Описание (ru)', 'Description (ru)', 'Descriere (ru)'); ?></label> -->
                                        </div> 
                                    </div> 

                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Описание (en)', 'Description (en)', 'Descriere (en)'); ?></h4>
                                        <div class="input-block">
                                            <textarea name="description_en" rows="5" class="form-textarea input-tertiary body-medium-regular" placeholder="<?php echo t('Описание на английском', 'English Description', 'Descriere în engleză'); ?>"><?php echo esc_textarea(get_post_meta($product_id, '_description_en', true)); ?></textarea>
                                            <!-- <label class="form-label label-large"><?php echo t('Описание (en)', 'Description (en)', 'Descriere (en)'); ?></label> -->
                                        </div> 
                                    </div>

                                    <div class="translation-block">
                                        <h4 class="label-large"><?php echo t('Описание (ro)', 'Description (ro)', 'Descriere (ro)'); ?></h4>
                                        <div class="input-block">
                                            <textarea name="description_ro" rows="5" class="form-textarea input-tertiary body-medium-regular" placeholder="<?php echo t('Описание на румынском', 'Romanian Description', 'Descriere în română'); ?>"><?php echo esc_textarea(get_post_meta($product_id, '_description_ro', true)); ?></textarea>
                                            <!-- <label class="form-label label-large"><?php echo t('Описание (ro)', 'Description (ro)', 'Descriere (ro)'); ?></label> -->
                                        </div> 
                                    </div> 
                                </div>

                                <div class="translation-button">
                                    <div id="translation-message" class="form-message body-medium-regular"></div>
                                    <button type="button" class="button secondary-button-small generate-translation" onclick="generateTranslations()"><?php echo t('Сгенерировать переводы', 'Generate Translations', 'Generează traduceri'); ?></button>
                                </div>
                            </div>
                                            
                            <!-- Статус -->
                            <div class="form-group">
                                <label class="form-label label-large"><?php echo t('Статус', 'Status', 'Stare'); ?></label>
                                <select name="product_status" class="form-select body-medium-regular">
                                    <option value="draft" <?php selected($status, 'draft'); ?>><?php echo t('Черновик', 'Draft', 'Schiță'); ?></option>
                                    <option value="publish" <?php selected($status, 'publish'); ?>><?php echo t('Опубликован', 'Published', 'Publicat'); ?></option>
                                </select>
                            </div>
                                            
                            <!-- Галерея -->
                            <div class="form-group">
                                <label class="form-label label-large"><?php echo t('Изображения (до 6 шт., первое — миниатюра)', 'Images (up to 6, first is thumbnail)', 'Imagini (până la 6, prima este miniatura)'); ?></label>
                                <input type="file" name="product_gallery[]" accept="image/*" multiple class="form-file body-medium-regular" id="product_gallery_input">
                                <div id="gallery_preview" class="gallery-preview">
                                    <?php foreach (array_filter($gallery_ids) as $id): ?>
                                        <div class="gallery-item" data-id="<?php echo esc_attr($id); ?>">
                                            <?php echo wp_get_attachment_image($id, 'thumbnail'); ?>
                                            <input type="hidden" name="existing_gallery_ids[]" value="<?php echo esc_attr($id); ?>">
                                            <span class="gallery-remove link-small-default" title="<?php echo t('Удалить', 'Remove', 'Șterge'); ?>">×</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                                    
                            <!-- Цена -->
                            <div class="form-group">
                                <label class="form-label label-large"><?php echo t('Цена (леи)', 'Price (lei)', 'Preț (lei)'); ?></label>
                                <input type="number" step="0.01" name="product_price" value="<?php echo esc_attr($price); ?>" class="form-input body-medium-regular" required>
                            </div>
                                    
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    
                            <div class="form-group">
                                <input type="submit" name="submit_product" value="<?php echo t('Обновить', 'Update', 'Actualizează'); ?>" class="form-submit primary-button-large button-large">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php
        else:
            $lang = $GLOBALS['language'] ?? 'ru';

            $title_translations = [
                'ru' => get_the_title(),
                'en' => get_post_meta(get_the_ID(), '_title_en', true),
                'ro' => get_post_meta(get_the_ID(), '_title_ro', true),
            ];

            $content_translations = [
                'ru' => get_the_content(),
                'en' => get_post_meta(get_the_ID(), '_description_en', true),
                'ro' => get_post_meta(get_the_ID(), '_description_ro', true),
            ];

            $price = get_post_meta(get_the_ID(), 'product_price', true);

            $gallery_meta = get_post_meta(get_the_ID(), '_product_image_gallery', true);
            $gallery_ids = !empty($gallery_meta) ? explode(',', $gallery_meta) : [];
            ?>

            <div class="product__wrapper">
                <div class="container-medium">
                    <nav class="breadcrumbs body-small-regular" aria-label="<?= t('Хлебные крошки', 'Breadcrumb', 'Firimituri'); ?>">
                        <a class="link-small-underline" href="<?php echo home_url(); ?>"><?= t('Главная', 'Home', 'Pagina principală'); ?></a> &raquo;

                        <?php
                        $terms = get_the_terms(get_the_ID(), 'product_cat');

                        if ($terms && !is_wp_error($terms)) {
                            $term = $terms[0];
                        
                            $breadcrumbs = [];
                            while ($term) {
                                $slug = $term->slug;
                                $translated_name = $translations[$slug][$lang] ?? $term->name;
                                $breadcrumbs[] = '<a class="link-small-underline" href="' . get_term_link($term) . '">' . esc_html($translated_name) . '</a>';
                                $term = $term->parent ? get_term($term->parent, 'product_cat') : false;
                            }
                        
                            echo implode(' &raquo; ', array_reverse($breadcrumbs)) . ' &raquo; ';
                        }
                        ?>


                        <span class="link-small-default"><?php echo esc_html($title_translations[$lang] ?? get_the_title()); ?></span>
                    </nav>
                    
                    <h1 class="product-card__title display-small">
                        <?php echo esc_html($title_translations[$lang] ?? get_the_title()); ?>
                    </h1>
                    <div class="product-card">
                        <main>
                            <article class="product-content">
                                <section class="product-gallery-carousel" aria-label="<?= t('Галерея изображений товара', 'Product image gallery', 'Galerie de imagini ale produsului'); ?>">
                                    <div class="main-slider">
                                        <?php foreach ($gallery_ids as $index => $id): 
                                            if ($id): ?>
                                                <figure>
                                                    <?php
                                                    echo wp_get_attachment_image(
                                                        $id,
                                                        'large',
                                                        false,
                                                        ['alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: t('Изображение товара', 'Product image', 'Imagine produs')]
                                                    );
                                                    ?>
                                                </figure>
                                            <?php endif;
                                        endforeach; ?>
                                    </div>
                                </section>
                                    
                                <section class="content body-small-regular" aria-label="<?= t('Описание товара', 'Product Description', 'Descriere produs'); ?>">
                                    <?php echo wpautop($content_translations[$lang] ?? get_the_content()); ?>
                                </section>
                                    
                                <section class="price title-medium" aria-label="<?= t('Цена', 'Price', 'Preț'); ?>">
                                    <p><strong><?= t('Цена:', 'Price:', 'Preț:'); ?></strong> <?php echo format_price_mdl_with_conversions($price); ?></p>
                                </section>
                            </article>
                        </main>
                                    
                        <aside class="product-sidebar">
                            <?php
                            $author_id = get_the_author_meta('ID');
                            $author_avatar = get_avatar($author_id, 64);
                            $author_registered = get_the_author_meta('user_registered');
                            $author_url = get_author_posts_url($author_id);
                            $author_region = get_user_meta($author_id, 'region', true);
                            $product_type = get_post_meta(get_the_ID(), 'product_type', true);
                            $price = get_post_meta(get_the_ID(), 'product_price', true);
                                    
                            $lang = $GLOBALS['language'];
                            ?>

                            <section class="author" aria-label="<?= t('Информация об авторе', 'Author Info', 'Informații despre autor'); ?>">
                                <div class="author-avatar"><?php echo $author_avatar; ?></div>
                                <div class="author-profile">
                                    <a class="link-button" href="<?php echo esc_url($author_url); ?>">
                                        <strong><?= t('Автор:', 'Author:', 'Autor:'); ?></strong> <?php the_author(); ?>
                                    </a>
                                    <span class="body-small-regular">
                                        <?= t('На сайте с', 'On the site since', 'Pe site din'); ?> <?php echo date_i18n('d.m.Y', strtotime($author_registered)); ?>
                                    </span>
                                </div>
                            </section>
                                    
                            <section class="details" aria-label="<?= t('Детали товара', 'Product Details', 'Detalii produs'); ?>">
                                <div class="item body-small-regular"><?= t('Дата публикации', 'Published on', 'Data publicării'); ?>: <?php echo get_the_date('d.m.Y'); ?></div>
                                <div class="item body-small-regular"><?= t('Просмотры', 'Views', 'Vizualizări'); ?>: <?php echo (int)get_post_meta(get_the_ID(), 'product_views', true); ?></div>
                                <?php if ($product_type): ?>
                                    <div class="item body-small-regular"><?= t('Тип', 'Type', 'Tip'); ?>: <?php echo esc_html($product_type); ?></div>
                                <?php endif; ?>
                            </section>
                                
                            <section class="price title-medium" aria-label="<?= t('Цена', 'Price', 'Preț'); ?>">
                                <?php echo format_price_mdl_with_conversions($price); ?>
                            </section>
                                
                            <?php if ($author_region): ?>
                                <section class="author-region" aria-label="<?= t('Регион автора', 'Author Region', 'Regiunea autorului'); ?>">
                                    <div class="item body-small-regular">
                                        <strong><?= t('Регион:', 'Region:', 'Regiune:'); ?></strong> <?php echo esc_html($author_region); ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                            
                            <?php if ($current_user_id === $post_author_id): ?>
                                <section class="actions" aria-label="<?= t('Управление товаром', 'Manage Product', 'Gestionați produsul'); ?>">
                                    <a href="<?php echo esc_url(add_query_arg('edit', 1)); ?>" class="button primary-button-small">
                                        <?= t('Редактировать', 'Edit', 'Editați'); ?>
                                    </a>
                            
                                    <form method="post" onsubmit="return confirm('<?= t('Вы уверены, что хотите удалить этот товар?', 'Are you sure you want to delete this product?', 'Sunteți sigur că doriți să ștergeți acest produs?'); ?>');">
                                        <?php wp_nonce_field('delete_product_action', 'delete_product_nonce'); ?>
                                        <input type="hidden" name="delete_product_id" value="<?php echo get_the_ID(); ?>">
                                        <button type="submit" class="button secondary-button-small"><?= t('Удалить', 'Delete', 'Ștergeți'); ?></button>
                                    </form>
                                </section>
                            <?php endif; ?>
                        </aside>
                    </div>
                </div>
            </div>
        <?php
        endif;
    endwhile;
else :
    echo '<p>Товар не найден.</p>';
endif;

get_footer();
?>
