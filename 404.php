<?php get_header(); ?>

<main id="main" class="site-main" style="text-align: center; padding: 100px 20px;">
    <h1 style="font-size: 96px; margin-bottom: 20px;">😲 О как!</h1>
    <p style="font-size: 24px; margin-bottom: 30px;">Кажется, вы попали не туда...<br>Страница не найдена.</p>
    
    <a href="<?php echo esc_url(home_url('/')); ?>" 
       style="display: inline-block; padding: 12px 24px; font-size: 18px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 6px;">
       Вернуться на главную
    </a>

    <div style="margin-top: 60px;">
        <img src="<?php echo get_template_directory_uri(); ?>/images/o-kak-meme.png" 
             alt="О как!" 
             style="max-width: 100%; height: auto;">
    </div>
</main>

<?php get_footer(); ?>
