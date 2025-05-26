<?php
function custom_enqueue_assets() {
    $theme_dir  = get_stylesheet_directory_uri();
    $theme_path = get_stylesheet_directory();

    // Общие стили (подключаются на всех страницах)
    $common_styles = [
        'style'        => '/style.css',
        'fonts'        => '/assets/css/fonts.css',
        'reset'        => '/assets/css/reset.css',
        'buttons-ui'   => '/assets/css/ui-kit/buttons.css',
        'pallete-colors' => '/assets/css/ui-kit/pallete-collors.css',
        'inputs-ui' => '/assets/css/ui-kit/inputs.css',
        'typography'   => '/assets/css/ui-kit/typography.css',
        'menu'         => '/assets/css/menu.css',
        'footer-menu'  => '/assets/css/footer-menu.css',
    ];

    foreach ( $common_styles as $handle => $path ) {
        wp_enqueue_style(
            $handle,
            $theme_dir . $path,
            [],
            filemtime( $theme_path . $path )
        );
    }

    if ( is_page_template( 'page-user-products.php' ) ) {
        wp_enqueue_style(
            'page-user-products-style',
            $theme_dir . '/assets/css/template/page-user-products.css',
            [],
            filemtime( $theme_path . '/assets/css/template/page-user-products.css' )
        );
    }

    wp_enqueue_style(
        'test-ui-page-style',
        $theme_dir . '/assets/css/template/test-ui-page.css',
        [],
        filemtime( $theme_path . '/assets/css/template/test-ui-page.css' )
    );

    if ( is_front_page() ) {
        wp_enqueue_style(
            'front-page-style',
            $theme_dir . '/assets/css/template/front-page.css',
            [],
            filemtime( $theme_path . '/assets/css/template/front-page.css' )
        );
    }

    if ( is_singular( 'product' ) ) {
        wp_enqueue_style(
            'single-product-style',
            $theme_dir . '/assets/css/template/single-product.css',
            [],
            filemtime( $theme_path . '/assets/css/template/single-product.css' )
        );
    }

    // Скрипты
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script(
        'scripts',
        $theme_dir . '/assets/js/scripts.js',
        [ 'jquery' ],
        filemtime( $theme_path . '/assets/js/scripts.js' ),
        true
    );

    if (is_front_page()) {
        wp_enqueue_script(
            'ajax-filter-script',
            $theme_dir . '/assets/js/ajax-filter.js',
            ['jquery'],
            filemtime($theme_path . '/assets/js/ajax-filter.js'),
            true
        );

        wp_localize_script('ajax-filter-script', 'ajaxFilterData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    wp_enqueue_style(
        'slick-css',
        $theme_dir . '/includes/slick/slick.css',
        array(),
        filemtime($theme_path . '/includes/slick/slick.css')
    );

    wp_enqueue_script(
        'slick-js',
        $theme_dir . '/includes/slick/slick.min.js',
        array('jquery'), // важно: зависимость от jQuery
        filemtime($theme_path . '/includes/slick/slick.min.js'),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'custom_enqueue_assets' );
