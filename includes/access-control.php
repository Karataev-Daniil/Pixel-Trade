<?php
// access-control.php

function kayo_admin_role_requests() {
    $screen = get_current_screen();
    if (current_user_can('administrator') && $screen->id === 'users') {
        $users = get_users([
            'meta_key' => 'requested_role',
            'meta_compare' => 'EXISTS'
        ]);

        echo '<h2>Заявки на подтверждение ролей</h2>';
        echo '<ul>';
        foreach ($users as $user) {
            $requested_role = get_user_meta($user->ID, 'requested_role', true);
            echo '<li>' . esc_html($user->user_login) . ' - ' . esc_html($requested_role);
            echo ' <a href="' . admin_url('users.php?action=approve_role&user_id=' . $user->ID) . '">Одобрить</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
add_action('admin_notices', 'kayo_admin_role_requests');

function kayo_approve_user_role() {
    if (isset($_GET['action'], $_GET['user_id']) && $_GET['action'] === 'approve_role' && current_user_can('administrator')) {
        $user_id = intval($_GET['user_id']);
        $requested_role = get_user_meta($user_id, 'requested_role', true);

        if ($requested_role) {
            wp_update_user(['ID' => $user_id, 'role' => $requested_role]);
            delete_user_meta($user_id, 'requested_role');
        }

        wp_redirect(admin_url('users.php'));
        exit;
    }
}
add_action('admin_init', 'kayo_approve_user_role');