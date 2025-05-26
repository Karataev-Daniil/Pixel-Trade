<?php
function t($ru, $en, $ro) {
    global $language;
    return $language == 'en' ? $en : ($language == 'ro' ? $ro : $ru);
}

function set_language_cookie() {
    if (isset($_GET['lang'])) {
        $language = $_GET['lang'];
        setcookie('language', $language, time() + (30 * 24 * 60 * 60), '/');
    } elseif (isset($_COOKIE['language'])) {
        $language = $_COOKIE['language']; 
    } else {
        $language = 'ru';
    }

    $GLOBALS['language'] = $language;
}
add_action('init', 'set_language_cookie');

function set_theme_cookie() {
    if (isset($_GET['theme'])) {
        $theme = $_GET['theme'];
        setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), '/');
    } elseif (isset($_COOKIE['theme'])) {
        $theme = $_COOKIE['theme']; 
    } else {
        $theme = 'light';
    }

    $GLOBALS['theme'] = $theme;
}
add_action('init', 'set_theme_cookie');