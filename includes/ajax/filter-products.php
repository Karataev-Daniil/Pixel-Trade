<?php
add_action('wp_ajax_filter_products', 'ajax_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'ajax_filter_products');

function ajax_filter_products() {
  $meta_query = [];
  $tax_query = [];

  $orderby = 'date';
  $order = 'DESC';
  $meta_key = '';

  if (!empty($_GET['product_cat'])) {
    $cat_slug = sanitize_text_field($_GET['product_cat']);
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field'    => 'slug',
      'terms'    => $cat_slug,
      'operator' => 'IN',
    ];
  }


  if (!empty($_GET['price_min']) || !empty($_GET['price_max'])) {
    $price_filter = ['key' => 'product_price', 'type' => 'NUMERIC'];

    if (!empty($_GET['price_min']) && !empty($_GET['price_max'])) {
      $price_filter['value'] = [floatval($_GET['price_min']), floatval($_GET['price_max'])];
      $price_filter['compare'] = 'BETWEEN';
    } elseif (!empty($_GET['price_min'])) {
      $price_filter['value'] = floatval($_GET['price_min']);
      $price_filter['compare'] = '>=';
    } elseif (!empty($_GET['price_max'])) {
      $price_filter['value'] = floatval($_GET['price_max']);
      $price_filter['compare'] = '<=';
    }

    $meta_query[] = $price_filter;
  }

  if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
      case 'date_asc':
        $orderby = 'date';
        $order = 'ASC';
        break;
      case 'date_desc':
        $orderby = 'date';
        $order = 'DESC';
        break;
      case 'views_desc':
        $orderby = 'meta_value_num';
        $meta_key = 'product_views';
        $order = 'DESC';
        $meta_query[] = ['key' => 'product_views', 'compare' => 'EXISTS'];
        break;
      case 'views_asc':
        $orderby = 'meta_value_num';
        $meta_key = 'product_views';
        $order = 'ASC';
        $meta_query[] = ['key' => 'product_views', 'compare' => 'EXISTS'];
        break;
    }
  }

  $query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_query'     => $meta_query,
    'tax_query'      => $tax_query,
    'orderby'        => $orderby,
    'order'          => $order,
    'meta_key'       => $meta_key,
  ]);

  if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post(); ?>
      <div class="product-card">
        <a href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()) the_post_thumbnail('medium'); ?>
          <h3><?php the_title(); ?></h3>
          <?php
          $price = get_post_meta(get_the_ID(), 'product_price', true);
          if ($price) echo '<p>' . esc_html($price) . ' ₽</p>';
          ?>
        </a>
      </div>
    <?php endwhile;
    wp_reset_postdata();
  else :
    echo '<p>Нет товаров.</p>';
  endif;

  wp_die();
}