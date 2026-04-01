<?php
if (!defined('ABSPATH')) exit;

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $group_array = [
        'key' => 'group_hj_testimonials',
        'title' => 'Testimonial Details',
        'fields' => [
            [
                'key' => 'field_hj_testimonial_rating',
                'label' => 'Rating',
                'name' => 'rating',
                'type' => 'number',
                'default_value' => 5,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'required' => 1,
            ],
            [
                'key' => 'field_hj_testimonial_role',
                'label' => 'Role',
                'name' => 'role',
                'type' => 'text',
                'default_value' => 'Patient',
                'placeholder' => 'Patient',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_testimonial_text',
                'label' => 'Review Text',
                'name' => 'text',
                'type' => 'textarea',
                'rows' => 6,
                'required' => 1,
                'placeholder' => 'I had extra leg room seats on plane plus ice pack every hr...',
            ],
        ],
        'location' => [
            [[ 'param' => 'post_type', 'operator' => '==', 'value' => 'testimonial' ]],
        ],
        'position' => 'acf_after_title',
        'style' => 'seamless',
        'active' => true,
        'modified' => time(),
    ];

    acf_add_local_field_group($group_array);

    $json_dir = get_stylesheet_directory() . '/acf-json';
    if (!is_dir($json_dir)) {
        wp_mkdir_p($json_dir);
    }
    if (is_writable($json_dir)) {
        $json_file = $json_dir . '/group_hj_testimonials.json';
        file_put_contents($json_file, wp_json_encode($group_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
});

if (!function_exists('hj_get_testimonial_slide_data')) {
    function hj_get_testimonial_slide_data($testimonial_post) {
        $testimonial_post = get_post($testimonial_post);
        if (!$testimonial_post instanceof WP_Post || $testimonial_post->post_type !== 'testimonial') {
            return null;
        }

        $testimonial_id = (int) $testimonial_post->ID;
        $text = function_exists('get_field') ? trim((string) get_field('text', $testimonial_id)) : '';
        $name = trim((string) get_the_title($testimonial_id));

        if ($name === '' && $text === '') {
            return null;
        }

        return [
            'rating' => function_exists('get_field') ? (int) get_field('rating', $testimonial_id) : 5,
            'name' => $name,
            'role' => function_exists('get_field') ? trim((string) get_field('role', $testimonial_id)) : '',
            'text' => $text,
            'photo' => get_post_thumbnail_id($testimonial_id)
                ? wp_get_attachment_image_src(get_post_thumbnail_id($testimonial_id), 'thumbnail')
                : null,
            'photo_id' => get_post_thumbnail_id($testimonial_id),
            'photo_alt' => get_post_meta(get_post_thumbnail_id($testimonial_id), '_wp_attachment_image_alt', true),
        ];
    }
}

if (!function_exists('hj_get_testimonials_for_slider')) {
    function hj_get_testimonials_for_slider($selected_ids = []) {
        $selected_ids = array_values(array_filter(array_map('intval', (array) $selected_ids)));

        $query_args = [
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        if (!empty($selected_ids)) {
            $query_args['post__in'] = $selected_ids;
            $query_args['orderby'] = 'post__in';
        } else {
            $query_args['orderby'] = [
                'menu_order' => 'ASC',
                'title' => 'ASC',
            ];
            $query_args['order'] = 'ASC';
        }

        $posts = get_posts($query_args);
        $items = [];

        foreach ($posts as $post) {
            $item = hj_get_testimonial_slide_data($post);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }
}