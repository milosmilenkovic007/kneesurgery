<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('hj_bfs_register_post_type')) {
    function hj_bfs_register_post_type()
    {
        register_post_type('hj_booking_submission', [
            'labels' => [
                'name' => __('Submissions', 'hello-elementor-child'),
                'singular_name' => __('Submission', 'hello-elementor-child'),
                'menu_name' => __('Booking Form', 'hello-elementor-child'),
                'all_items' => __('Submissions', 'hello-elementor-child'),
                'edit_item' => __('View Submission', 'hello-elementor-child'),
                'view_item' => __('View Submission', 'hello-elementor-child'),
                'search_items' => __('Search Submissions', 'hello-elementor-child'),
                'not_found' => __('No submissions found.', 'hello-elementor-child'),
                'not_found_in_trash' => __('No submissions found in Trash.', 'hello-elementor-child'),
            ],
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
            'menu_position' => 26,
            'menu_icon' => 'dashicons-email-alt2',
            'supports' => ['title'],
            'map_meta_cap' => true,
            'capabilities' => [
                'create_posts' => 'do_not_allow',
            ],
        ]);
    }
}
add_action('init', 'hj_bfs_register_post_type');

if (!function_exists('hj_bfs_create_submission')) {
    function hj_bfs_create_submission(array $data)
    {
        $full_name = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $country_code = trim((string) ($data['country_code'] ?? ''));
        $treatment_id = absint($data['treatment_id'] ?? 0);
        $treatment_title = trim((string) ($data['treatment_title'] ?? ''));
        $source_url = esc_url_raw((string) ($data['source_url'] ?? ''));

        $submission_title = sprintf(
            __('%1$s - %2$s - %3$s', 'hello-elementor-child'),
            $full_name !== '' ? $full_name : __('Booking enquiry', 'hello-elementor-child'),
            $treatment_title !== '' ? $treatment_title : __('Treatment package', 'hello-elementor-child'),
            wp_date('Y-m-d H:i')
        );

        $submission_id = wp_insert_post([
            'post_type' => 'hj_booking_submission',
            'post_status' => 'publish',
            'post_title' => $submission_title,
        ], true);

        if (is_wp_error($submission_id)) {
            return $submission_id;
        }

        update_post_meta($submission_id, '_hj_bfs_full_name', $full_name);
        update_post_meta($submission_id, '_hj_bfs_email', $email);
        update_post_meta($submission_id, '_hj_bfs_phone', $phone);
        update_post_meta($submission_id, '_hj_bfs_country_code', $country_code);
        update_post_meta($submission_id, '_hj_bfs_treatment_id', $treatment_id);
        update_post_meta($submission_id, '_hj_bfs_treatment_title', $treatment_title);
        update_post_meta($submission_id, '_hj_bfs_source_url', $source_url);

        return $submission_id;
    }
}

if (!function_exists('hj_bfs_get_submission_meta')) {
    function hj_bfs_get_submission_meta($post_id, $key)
    {
        return (string) get_post_meta($post_id, $key, true);
    }
}

add_filter('manage_hj_booking_submission_posts_columns', function ($columns) {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => __('Submission', 'hello-elementor-child'),
        'hj_bfs_treatment' => __('Treatment', 'hello-elementor-child'),
        'hj_bfs_email' => __('Email', 'hello-elementor-child'),
        'hj_bfs_phone' => __('Mobile', 'hello-elementor-child'),
        'date' => __('Submitted', 'hello-elementor-child'),
    ];
});

add_action('manage_hj_booking_submission_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'hj_bfs_treatment':
            echo esc_html(hj_bfs_get_submission_meta($post_id, '_hj_bfs_treatment_title'));
            break;
        case 'hj_bfs_email':
            $email = hj_bfs_get_submission_meta($post_id, '_hj_bfs_email');
            if ($email !== '') {
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            }
            break;
        case 'hj_bfs_phone':
            echo esc_html(hj_bfs_get_submission_meta($post_id, '_hj_bfs_phone'));
            break;
    }
}, 10, 2);

add_filter('post_row_actions', function ($actions, $post) {
    if (!$post instanceof WP_Post || $post->post_type !== 'hj_booking_submission') {
        return $actions;
    }

    unset($actions['inline hide-if-no-js']);
    unset($actions['view']);

    return $actions;
}, 10, 2);

add_action('add_meta_boxes_hj_booking_submission', function () {
    add_meta_box(
        'hj-bfs-details',
        __('Submission Details', 'hello-elementor-child'),
        function ($post) {
            $fields = [
                __('Full name', 'hello-elementor-child') => hj_bfs_get_submission_meta($post->ID, '_hj_bfs_full_name'),
                __('Email', 'hello-elementor-child') => hj_bfs_get_submission_meta($post->ID, '_hj_bfs_email'),
                __('Mobile', 'hello-elementor-child') => hj_bfs_get_submission_meta($post->ID, '_hj_bfs_phone'),
                __('Country', 'hello-elementor-child') => strtoupper(hj_bfs_get_submission_meta($post->ID, '_hj_bfs_country_code')),
                __('Treatment', 'hello-elementor-child') => hj_bfs_get_submission_meta($post->ID, '_hj_bfs_treatment_title'),
                __('Source URL', 'hello-elementor-child') => hj_bfs_get_submission_meta($post->ID, '_hj_bfs_source_url'),
                __('Submitted', 'hello-elementor-child') => get_the_date('Y-m-d H:i:s', $post),
            ];

            echo '<table class="widefat striped" style="border:0">';
            foreach ($fields as $label => $value) {
                echo '<tr>';
                echo '<td style="width:180px"><strong>' . esc_html($label) . '</strong></td>';
                echo '<td>';
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    echo '<a href="' . esc_url($value) . '" target="_blank" rel="noopener">' . esc_html($value) . '</a>';
                } elseif (is_email($value)) {
                    echo '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                } else {
                    echo esc_html($value);
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        },
        'hj_booking_submission',
        'normal',
        'high'
    );
});

add_action('admin_menu', function () {
    remove_submenu_page('edit.php?post_type=hj_booking_submission', 'post-new.php?post_type=hj_booking_submission');
}, 99);

add_action('admin_head', function () {
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'hj_booking_submission') {
        return;
    }

    echo '<style>.post-type-hj_booking_submission .page-title-action,.post-type-hj_booking_submission #minor-publishing-actions,.post-type-hj_booking_submission #misc-publishing-actions .misc-pub-post-status,.post-type-hj_booking_submission #misc-publishing-actions .misc-pub-visibility{display:none!important;}</style>';
});