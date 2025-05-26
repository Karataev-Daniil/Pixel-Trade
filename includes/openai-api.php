<?php
function translate_with_openai($text, $target_lang) {
    if (!defined('OPENAI_API_KEY')) {
        return 'API key not defined';
    }

    $api_key = OPENAI_API_KEY;

    $lang_map = [
        'en' => 'English',
        'ro' => 'Romanian',
        'ru' => 'Russian',
    ];

    $lang_name = $lang_map[$target_lang] ?? $target_lang;

    $messages = [
        [
            'role' => 'user',
            'content' => "Translate into {$lang_name}: " . trim($text)
        ],
    ];

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0,
        ]),
    ]);

    if (is_wp_error($response)) {
        return 'Request error: ' . $response->get_error_message();
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return trim($body['choices'][0]['message']['content'] ?? 'Empty response');
}

function handle_generate_translations() {
    check_ajax_referer('generate_translations_nonce');
    
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    
    $title_en = translate_with_openai($title, 'en');
    $title_ro = translate_with_openai($title, 'ro');
    $desc_en = translate_with_openai($description, 'en');
    $desc_ro = translate_with_openai($description, 'ro');
    
    if (isset($_POST['product_id'])) {
        $product_id = (int) $_POST['product_id'];
        update_post_meta($product_id, '_title_en', $title_en);
        update_post_meta($product_id, '_title_ro', $title_ro);
        update_post_meta($product_id, '_description_en', $desc_en);
        update_post_meta($product_id, '_description_ro', $desc_ro);
    }
    
    wp_send_json_success([
        'title_en' => $title_en,
        'title_ro' => $title_ro,
        'description_en' => $desc_en,
        'description_ro' => $desc_ro,
    ]);
}
add_action('wp_ajax_generate_translations', 'handle_generate_translations');