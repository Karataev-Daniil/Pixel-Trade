<?php
// admin-approval.php

// Подтверждение ролей пользователей 
function kayo_pending_roles_admin_notice() {
    $args = [
        'meta_key' => 'pending_role',
        'meta_compare' => 'EXISTS'
    ];
    $pending_users = get_users($args);

    if (!empty($pending_users)) {
        echo '<div class="notice notice-warning"><p>Есть пользователи, ожидающие подтверждения роли. <a href="' . admin_url('users.php') . '">Посмотреть</a></p></div>';
    }
}
add_action('admin_notices', 'kayo_pending_roles_admin_notice');

function kayo_add_approve_button($actions, $user_object) {
    if (current_user_can('administrator') && get_user_meta($user_object->ID, 'pending_role', true)) {
        $approve_url = wp_nonce_url(
            add_query_arg([
                'action' => 'approve_user_role',
                'user_id' => $user_object->ID
            ], admin_url('users.php')),
            'approve_user_role_' . $user_object->ID
        );

        $actions['approve_role'] = '<a href="' . esc_url($approve_url) . '">Подтвердить роль</a>';
    }
    return $actions;
}
add_filter('user_row_actions', 'kayo_add_approve_button', 10, 2);

function kayo_handle_approve_user_role() {
    if (
        isset($_GET['action'], $_GET['user_id']) &&
        $_GET['action'] === 'approve_user_role' &&
        current_user_can('administrator') &&
        check_admin_referer('approve_user_role_' . intval($_GET['user_id']))
    ) {
        $user_id = intval($_GET['user_id']);
        $pending_role = get_user_meta($user_id, 'pending_role', true);

        if ($pending_role) {
            wp_update_user([
                'ID' => $user_id,
                'role' => $pending_role,
            ]);
            delete_user_meta($user_id, 'pending_role');
        }

        wp_redirect(admin_url('users.php?role_approved=1'));
        exit;
    }
}
add_action('admin_init', 'kayo_handle_approve_user_role');

add_action('admin_notices', 'kayo_show_user_role_approved_notice');
function kayo_show_user_role_approved_notice() {
    if (isset($_GET['role_approved']) && $_GET['role_approved'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>Роль пользователя успешно подтверждена.</p></div>';
    }
}

// Модерация товаров (одобрение админом)
add_filter('wp_insert_post_data', 'force_pending_for_vendors', 99, 2);
function force_pending_for_vendors($data, $postarr) {
    if ($data['post_type'] === 'product' && !current_user_can('publish_posts')) {
        $data['post_status'] = 'pending';
    }
    return $data;
}

add_filter('post_row_actions', 'add_approve_action_link', 10, 2);
function add_approve_action_link($actions, $post) {
    if ($post->post_type === 'product' && $post->post_status === 'pending' && current_user_can('publish_post', $post->ID)) {
        $approve_url = wp_nonce_url(
            admin_url('edit.php?post_type=product&approve_product=' . $post->ID),
            'approve_product_' . $post->ID
        );
        $actions['approve'] = '<a href="' . esc_url($approve_url) . '">Одобрить</a>';
    }
    return $actions;
}

add_action('admin_init', 'handle_product_approval');
function handle_product_approval() {
    if (isset($_GET['approve_product'])) {
        $product_id = intval($_GET['approve_product']);
        if (current_user_can('publish_post', $product_id) && check_admin_referer('approve_product_' . $product_id)) {
            wp_update_post([
                'ID' => $product_id,
                'post_status' => 'publish',
            ]);
            wp_redirect(admin_url('edit.php?post_type=product&approved=1'));
            exit;
        }
    }
}

add_action('admin_notices', 'show_approval_notice');
function show_approval_notice() {
    if (isset($_GET['approved']) && $_GET['approved'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>Товар успешно одобрен и опубликован.</p></div>';
    }
}
