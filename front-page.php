<?php get_header(); ?>
<div class="main-wrapper">
  <div class="container-medium">
    <div class="content-columns">
      <!-- Сайдбар -->
      <aside class="sidebar" style="width: 260px;">
        <h2 class="title-medium">Категории</h2>
        <form id="filter-form">
          <ul style="margin-top: 24px;">
            <?php
            $language = $GLOBALS['language']; // ru, en, ro
    
            $translations = [
              'pc' => [
                'ru' => 'Товары для ПК',
                'en' => 'PC products',
                'ro' => 'Produse pentru PC',
              ],
              'food' => [
                'ru' => 'Еда',
                'en' => 'Food',
                'ro' => 'Mâncare',
              ],
              'clothes' => [
                'ru' => 'Одежда',
                'en' => 'Clothes',
                'ro' => 'Haine',
              ],
            ];
    
            $categories = get_terms([
              'taxonomy' => 'product_cat',
              'hide_empty' => false,
            ]);
    
            foreach ($categories as $cat) {
              $slug = $cat->slug;
              $translated_name = $translations[$slug][$language] ?? $cat->name;
            
              echo '<li style="margin-bottom: 12px;">
                <label class="body-small-regular">
                  <input type="radio" name="product_cat" value="' . esc_attr($slug) . '"> ' . esc_html($translated_name) . '
                </label>
              </li>';
            }
            ?>
          </ul>
    
          <h3 class="title-small" style="margin-top: 32px;">Цена</h3>
          <div id="price-slider" style="margin-top: 16px; margin-bottom: 16px;"></div>
          <input type="hidden" name="price_min" id="price-min">
          <input type="hidden" name="price_max" id="price-max">
          <div style="display: flex; justify-content: space-between; font-size: 14px;">
            <span id="price-min-label"></span>
            <span id="price-max-label"></span>
          </div>
    
    
          <h3 class="title-small" style="margin-top: 32px;">Сортировка</h3>
          <select name="sort" class="body-small-regular" style="margin-top: 8px; width: 100%;">
            <option value="date_desc" class="body-small-regular">Сначала новые</option>
            <option value="date_asc" class="body-small-regular">Сначала старые</option>
            <option value="views_desc" class="body-small-regular">Популярные</option>
            <option value="views_asc" class="body-small-regular">Менее популярные</option>
          </select>
    
          <button type="submit" class="primary-button-small" style="margin-top: 24px;">Применить</button>
          <button type="button" onclick="location.reload();" class="secondary-button-small" style="margin-top: 12px;">Сбросить</button>
        </form>
      </aside>
    
      <!-- Товары -->
      <main class="product-grid" style="flex: 1;">
        <h1 class="display-medium" style="margin-bottom: 32px;">Каталог товаров</h1>
        <div id="product-results" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 32px;">
          <!-- Товары подгружаются сюда -->
        </div>
        <div class="pagination">
          <?php
          echo paginate_links([
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
          ]);
          ?>
        </div>
      </main>
    </div>
  </div>
</div>
<?php
get_footer();
?>