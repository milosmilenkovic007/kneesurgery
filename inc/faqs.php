<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    $labels = [
        'name'                  => __('FAQs', 'hello-elementor-child'),
        'singular_name'         => __('FAQ', 'hello-elementor-child'),
        'menu_name'             => __('FAQs', 'hello-elementor-child'),
        'name_admin_bar'        => __('FAQ', 'hello-elementor-child'),
        'add_new'               => __('Add New', 'hello-elementor-child'),
        'add_new_item'          => __('Add New FAQ', 'hello-elementor-child'),
        'new_item'              => __('New FAQ', 'hello-elementor-child'),
        'edit_item'             => __('Edit FAQ', 'hello-elementor-child'),
        'view_item'             => __('View FAQ', 'hello-elementor-child'),
        'all_items'             => __('All FAQs', 'hello-elementor-child'),
        'search_items'          => __('Search FAQs', 'hello-elementor-child'),
        'not_found'             => __('No FAQs found.', 'hello-elementor-child'),
        'not_found_in_trash'    => __('No FAQs found in Trash.', 'hello-elementor-child'),
    ];

    $args = [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-editor-help',
        'has_archive' => false,
        'rewrite' => false,
        'supports' => ['title', 'revisions', 'page-attributes'],
        'hierarchical' => false,
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
    ];

    register_post_type('faq', $args);
}, 0);

add_action('init', function () {
    $labels = [
        'name'              => __('FAQ Categories', 'hello-elementor-child'),
        'singular_name'     => __('FAQ Category', 'hello-elementor-child'),
        'search_items'      => __('Search FAQ Categories', 'hello-elementor-child'),
        'all_items'         => __('All FAQ Categories', 'hello-elementor-child'),
        'parent_item'       => __('Parent FAQ Category', 'hello-elementor-child'),
        'parent_item_colon' => __('Parent FAQ Category:', 'hello-elementor-child'),
        'edit_item'         => __('Edit FAQ Category', 'hello-elementor-child'),
        'update_item'       => __('Update FAQ Category', 'hello-elementor-child'),
        'add_new_item'      => __('Add New FAQ Category', 'hello-elementor-child'),
        'new_item_name'     => __('New FAQ Category Name', 'hello-elementor-child'),
        'menu_name'         => __('Categories', 'hello-elementor-child'),
    ];

    $args = [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => false,
    ];

    register_taxonomy('faq_category', ['faq'], $args);
}, 0);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $group_array = [
        'key' => 'group_hj_faq',
        'title' => 'FAQ Details',
        'fields' => [
            [
                'key' => 'field_hj_faq_answer',
                'label' => 'Answer',
                'name' => 'faq_answer',
                'type' => 'textarea',
                'rows' => 6,
                'required' => 1,
                'placeholder' => 'Write the answer here...',
            ],
        ],
        'location' => [
            [[ 'param' => 'post_type', 'operator' => '==', 'value' => 'faq' ]],
        ],
        'position' => 'acf_after_title',
        'style' => 'seamless',
        'active' => true,
    ];

    acf_add_local_field_group($group_array);
});

if (!function_exists('hj_get_faq_answer')) {
    function hj_get_faq_answer($faq_post) {
        $faq_post = get_post($faq_post);
        if (!$faq_post instanceof WP_Post || $faq_post->post_type !== 'faq') {
            return '';
        }

        $answer = function_exists('get_field') ? get_field('faq_answer', $faq_post->ID) : '';
        return trim((string) $answer);
    }
}

if (!function_exists('hj_get_faq_items')) {
    function hj_get_faq_items($args = []) {
        $selected_ids = function_exists('hj_normalize_faq_post_ids')
            ? hj_normalize_faq_post_ids($args['post__in'] ?? [])
            : array_values(array_filter(array_map('intval', (array) ($args['post__in'] ?? []))));
        $term_ids = function_exists('hj_normalize_faq_term_ids')
            ? hj_normalize_faq_term_ids($args['term_ids'] ?? [])
            : array_values(array_filter(array_map('intval', (array) ($args['term_ids'] ?? []))));
        $term_slugs = array_values(array_filter(array_map('sanitize_title', (array) ($args['term_slugs'] ?? []))));
        $posts_per_page = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : -1;

        $query_args = [
            'post_type' => 'faq',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => [
                'menu_order' => 'ASC',
                'title' => 'ASC',
            ],
            'order' => 'ASC',
        ];

        if (!empty($selected_ids)) {
            $query_args['post__in'] = $selected_ids;
            $query_args['orderby'] = 'post__in';
        }

        $tax_query = [];
        if (!empty($term_ids)) {
            $tax_query[] = [
                'taxonomy' => 'faq_category',
                'field' => 'term_id',
                'terms' => $term_ids,
            ];
        }
        if (!empty($term_slugs)) {
            $tax_query[] = [
                'taxonomy' => 'faq_category',
                'field' => 'slug',
                'terms' => $term_slugs,
            ];
        }
        if (!empty($tax_query)) {
            $query_args['tax_query'] = count($tax_query) > 1
                ? array_merge(['relation' => 'AND'], $tax_query)
                : $tax_query;
        }

        $posts = get_posts($query_args);
        $items = [];

        foreach ($posts as $post) {
            $question = trim((string) get_the_title($post));
            $answer = hj_get_faq_answer($post);

            if ($question === '' || $answer === '') {
                continue;
            }

            $terms = get_the_terms($post, 'faq_category');
            $items[] = [
                'id' => (int) $post->ID,
                'question' => $question,
                'answer' => $answer,
                'categories' => is_array($terms) ? array_values($terms) : [],
            ];
        }

        return $items;
    }
}

if (!function_exists('hj_get_faq_category_map')) {
    function hj_get_faq_category_map($args = []) {
        $terms = get_terms(array_merge([
            'taxonomy' => 'faq_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ], $args));

        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $mapped = [];
        foreach ($terms as $term) {
            $mapped[] = [
                'id' => (int) $term->term_id,
                'name' => (string) $term->name,
                'slug' => (string) $term->slug,
            ];
        }

        return $mapped;
    }
}

if (!function_exists('hj_get_faq_section_anchor_id')) {
    function hj_get_faq_section_anchor_id($section, $fallback_index = 0) {
        if (!is_array($section)) {
            $section = [];
        }

        $slug = sanitize_title((string) ($section['slug'] ?? ''));

        if ($slug === '') {
            $term_id = (int) ($section['term_id'] ?? 0);
            if ($term_id > 0) {
                $term = get_term($term_id, 'faq_category');
                if ($term instanceof WP_Term && !is_wp_error($term)) {
                    $slug = sanitize_title($term->slug ?: $term->name);
                }
            }
        }

        if ($slug === '') {
            $slug = sanitize_title((string) ($section['title'] ?? ''));
        }

        if ($slug === '') {
            $slug = 'faq-section';
            if ($fallback_index > 0) {
                $slug .= '-' . $fallback_index;
            }
        }

        return $slug;
    }
}

if (!function_exists('hj_normalize_faq_post_ids')) {
    function hj_normalize_faq_post_ids($items) {
        $items = is_array($items) ? $items : [$items];
        $ids = [];

        foreach ($items as $item) {
            if ($item instanceof WP_Post) {
                $ids[] = (int) $item->ID;
                continue;
            }

            if (is_array($item)) {
                $item_id = (int) ($item['ID'] ?? $item['id'] ?? 0);
                if ($item_id > 0) {
                    $ids[] = $item_id;
                }
                continue;
            }

            $item_id = (int) $item;
            if ($item_id > 0) {
                $ids[] = $item_id;
            }
        }

        return array_values(array_unique($ids));
    }
}

if (!function_exists('hj_normalize_faq_term_ids')) {
    function hj_normalize_faq_term_ids($items) {
        $items = is_array($items) ? $items : [$items];
        $ids = [];

        foreach ($items as $item) {
            if ($item instanceof WP_Term) {
                $ids[] = (int) $item->term_id;
                continue;
            }

            if (is_array($item)) {
                $item_id = (int) ($item['term_id'] ?? $item['id'] ?? $item['ID'] ?? 0);
                if ($item_id > 0) {
                    $ids[] = $item_id;
                }
                continue;
            }

            $item_id = (int) $item;
            if ($item_id > 0) {
                $ids[] = $item_id;
            }
        }

        return array_values(array_unique($ids));
    }
}

if (!function_exists('hj_limit_faq_items')) {
    function hj_limit_faq_items($items, $limit) {
        $items = is_array($items) ? array_values($items) : [];
        $limit = (int) $limit;

        if ($limit <= 0) {
            return $items;
        }

        return array_slice($items, 0, $limit);
    }
}

if (!function_exists('hj_normalize_legacy_faq_items')) {
    function hj_normalize_legacy_faq_items($items) {
        $items = is_array($items) ? $items : [];
        $normalized = [];

        foreach ($items as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $normalized[] = [
                'id' => 0,
                'question' => $question,
                'answer' => $answer,
                'categories' => [],
            ];
        }

        return $normalized;
    }
}

if (!function_exists('hj_clean_legacy_faq_text')) {
    function hj_clean_legacy_faq_text($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return trim(wp_specialchars_decode($value, ENT_QUOTES));
    }
}

if (!function_exists('hj_normalize_faq_category_term_name')) {
    function hj_normalize_faq_category_term_name($term_id, $name) {
        global $wpdb;

        $term_id = (int) $term_id;
        $name = hj_clean_legacy_faq_text($name);

        if ($term_id <= 0 || $name === '') {
            return;
        }

        $term = get_term($term_id, 'faq_category');
        if (!$term || is_wp_error($term) || (string) $term->name === $name) {
            return;
        }

        $wpdb->update(
            $wpdb->terms,
            ['name' => $name],
            ['term_id' => $term_id],
            ['%s'],
            ['%d']
        );

        clean_term_cache($term_id, 'faq_category');
    }
}

if (!function_exists('hj_resolve_faq_module_items')) {
    function hj_resolve_faq_module_items($args = []) {
        $legacy_items = hj_normalize_legacy_faq_items($args['legacy_items'] ?? []);
        $source = sanitize_key((string) ($args['source'] ?? ''));
        $selected_ids = hj_normalize_faq_post_ids($args['selected_ids'] ?? []);
        $term_ids = hj_normalize_faq_term_ids($args['term_ids'] ?? []);
        $limit = isset($args['limit']) ? (int) $args['limit'] : -1;
        $limit = $limit > 0 ? $limit : -1;
        $uses_legacy_by_default = false;

        if ($source === '') {
            $source = !empty($legacy_items) ? 'legacy' : 'all';
            $uses_legacy_by_default = $source === 'legacy';
        }

        if ($source === 'legacy') {
            return hj_limit_faq_items($legacy_items, $limit);
        }

        $query_args = [
            'posts_per_page' => $limit,
        ];

        if ($source === 'manual') {
            if (empty($selected_ids)) {
                return [];
            }

            $query_args['post__in'] = $selected_ids;
        } elseif ($source === 'category') {
            if (empty($term_ids)) {
                return [];
            }

            $query_args['term_ids'] = $term_ids;
        }

        $items = hj_get_faq_items($query_args);

        if (empty($items) && !empty($legacy_items) && ($source === 'all' || $uses_legacy_by_default)) {
            return hj_limit_faq_items($legacy_items, $limit);
        }

        return hj_limit_faq_items($items, $limit);
    }
}

if (!function_exists('hj_get_faq_sections')) {
    function hj_get_faq_sections($args = []) {
        $term_ids = array_values(array_filter(array_map('intval', (array) ($args['term_ids'] ?? []))));
        $term_slugs = array_values(array_filter(array_map('sanitize_title', (array) ($args['term_slugs'] ?? []))));
        $posts_per_page = isset($args['posts_per_page']) ? (int) $args['posts_per_page'] : -1;
        $include_uncategorized = !empty($args['include_uncategorized']);

        $term_query_args = [
            'taxonomy' => 'faq_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        if (!empty($term_ids)) {
            $term_query_args['include'] = $term_ids;
        }
        if (!empty($term_slugs)) {
            $term_query_args['slug'] = $term_slugs;
        }

        $terms = get_terms($term_query_args);
        if (is_wp_error($terms) || !is_array($terms)) {
            return [];
        }

        $sections = [];

        foreach ($terms as $term) {
            $items = hj_get_faq_items([
                'term_ids' => [(int) $term->term_id],
                'posts_per_page' => $posts_per_page,
            ]);

            if (empty($items)) {
                continue;
            }

            $sections[] = [
                'term_id' => (int) $term->term_id,
                'slug' => (string) $term->slug,
                'title' => (string) $term->name,
                'items' => $items,
            ];
        }

        if ($include_uncategorized) {
            $uncategorized_posts = get_posts([
                'post_type' => 'faq',
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'orderby' => [
                    'menu_order' => 'ASC',
                    'title' => 'ASC',
                ],
                'order' => 'ASC',
                'tax_query' => [[
                    'taxonomy' => 'faq_category',
                    'operator' => 'NOT EXISTS',
                ]],
            ]);

            $items = [];
            foreach ($uncategorized_posts as $post) {
                $question = trim((string) get_the_title($post));
                $answer = hj_get_faq_answer($post);

                if ($question === '' || $answer === '') {
                    continue;
                }

                $items[] = [
                    'id' => (int) $post->ID,
                    'question' => $question,
                    'answer' => $answer,
                    'categories' => [],
                ];
            }

            if (!empty($items)) {
                $sections[] = [
                    'term_id' => 0,
                    'slug' => 'general',
                    'title' => __('General', 'hello-elementor-child'),
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }
}

if (!function_exists('hj_get_legacy_faq_page_sections')) {
    function hj_get_legacy_faq_page_sections($page_id) {
        $page_id = (int) $page_id;
        if ($page_id <= 0) {
            return [];
        }

        $sections = function_exists('get_field') ? get_field('faq_sections', $page_id) : null;
        if (is_array($sections) && !empty($sections)) {
            return $sections;
        }

        $section_count = (int) get_post_meta($page_id, 'faq_sections', true);
        if ($section_count <= 0) {
            return [];
        }

        $sections = [];

        for ($section_index = 0; $section_index < $section_count; $section_index++) {
            $section_title = hj_clean_legacy_faq_text(get_post_meta($page_id, "faq_sections_{$section_index}_title", true));
            $item_count = (int) get_post_meta($page_id, "faq_sections_{$section_index}_items", true);
            $items = [];

            for ($item_index = 0; $item_index < $item_count; $item_index++) {
                $question = hj_clean_legacy_faq_text(get_post_meta($page_id, "faq_sections_{$section_index}_items_{$item_index}_question", true));
                $answer = hj_clean_legacy_faq_text(get_post_meta($page_id, "faq_sections_{$section_index}_items_{$item_index}_answer", true));

                if ($question === '' || $answer === '') {
                    continue;
                }

                $items[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }

            if ($section_title === '' || empty($items)) {
                continue;
            }

            $sections[] = [
                'title' => $section_title,
                'items' => $items,
            ];
        }

        return $sections;
    }
}

if (!function_exists('hj_find_legacy_faq_page_id')) {
    function hj_find_legacy_faq_page_id() {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-faq.php',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return !empty($pages) ? (int) $pages[0] : 0;
    }
}

if (!function_exists('hj_get_main_faq_page_url')) {
    function hj_get_main_faq_page_url() {
        $page_id = hj_find_legacy_faq_page_id();

        if ($page_id > 0) {
            $url = get_permalink($page_id);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        $page = get_page_by_path('faq', OBJECT, 'page');
        if ($page instanceof WP_Post) {
            $url = get_permalink($page);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return home_url('/faq/');
    }
}

if (!function_exists('hj_get_faq_section_url')) {
    function hj_get_faq_section_url($term_ids = []) {
        $base_url = hj_get_main_faq_page_url();
        if (!is_string($base_url) || $base_url === '') {
            return '';
        }

        $term_ids = hj_normalize_faq_term_ids($term_ids);
        if (empty($term_ids)) {
            return $base_url;
        }

        $term = get_term($term_ids[0], 'faq_category');
        if (!$term instanceof WP_Term || is_wp_error($term)) {
            return $base_url;
        }

        $anchor = hj_get_faq_section_anchor_id([
            'term_id' => (int) $term->term_id,
            'slug' => (string) $term->slug,
            'title' => (string) $term->name,
        ]);

        $base_url = preg_replace('/#.*$/', '', $base_url);
        return $anchor !== '' ? $base_url . '#' . $anchor : $base_url;
    }
}

if (!function_exists('hj_get_faq_module_read_more_url')) {
    function hj_get_faq_module_read_more_url($args = []) {
        $custom_url = trim((string) ($args['custom_url'] ?? ''));
        if ($custom_url !== '') {
            return $custom_url;
        }

        $source = sanitize_key((string) ($args['source'] ?? ''));
        $term_ids = hj_normalize_faq_term_ids($args['term_ids'] ?? []);

        if ($source === 'category' && !empty($term_ids)) {
            return hj_get_faq_section_url($term_ids);
        }

        return hj_get_main_faq_page_url();
    }
}

if (!function_exists('hj_migrate_legacy_faq_page_to_cpt')) {
    function hj_migrate_legacy_faq_page_to_cpt($page_id = 0) {
        if (!function_exists('get_field')) {
            return new WP_Error('hj_faq_missing_acf', __('ACF is required for FAQ migration.', 'hello-elementor-child'));
        }

        $page_id = $page_id ? (int) $page_id : hj_find_legacy_faq_page_id();
        if ($page_id <= 0) {
            return new WP_Error('hj_faq_missing_page', __('Legacy FAQ page was not found.', 'hello-elementor-child'));
        }

        $sections = hj_get_legacy_faq_page_sections($page_id);
        if (!is_array($sections) || empty($sections)) {
            return new WP_Error('hj_faq_empty_source', __('Legacy FAQ page has no FAQ sections to migrate.', 'hello-elementor-child'));
        }

        $created = 0;
        $skipped = 0;
        $created_terms = 0;

        foreach ($sections as $section_index => $section) {
            $section_title = hj_clean_legacy_faq_text($section['title'] ?? '');
            $items = is_array($section['items'] ?? null) ? $section['items'] : [];

            if ($section_title === '' || empty($items)) {
                continue;
            }

            $term_info = term_exists($section_title, 'faq_category');
            if (!$term_info) {
                $term_info = wp_insert_term($section_title, 'faq_category');
                if (!is_wp_error($term_info)) {
                    $created_terms++;
                }
            }

            $term_id = is_wp_error($term_info)
                ? 0
                : (int) (is_array($term_info) ? ($term_info['term_id'] ?? 0) : $term_info);

            if ($term_id > 0) {
                hj_normalize_faq_category_term_name($term_id, $section_title);
            }

            foreach ($items as $item_index => $item) {
                $question = hj_clean_legacy_faq_text($item['question'] ?? '');
                $answer = hj_clean_legacy_faq_text($item['answer'] ?? '');

                if ($question === '' || $answer === '') {
                    $skipped++;
                    continue;
                }

                $legacy_key = sprintf('%d:%d:%d', $page_id, (int) $section_index, (int) $item_index);
                $existing = get_posts([
                    'post_type' => 'faq',
                    'post_status' => ['publish', 'draft', 'private'],
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_key' => '_hj_legacy_faq_source',
                    'meta_value' => $legacy_key,
                ]);

                if (!empty($existing)) {
                    $skipped++;
                    continue;
                }

                $faq_id = wp_insert_post([
                    'post_type' => 'faq',
                    'post_status' => 'publish',
                    'post_title' => $question,
                    'menu_order' => ((int) $section_index * 100) + (int) $item_index,
                ], true);

                if (is_wp_error($faq_id) || !$faq_id) {
                    $skipped++;
                    continue;
                }

                if (function_exists('update_field')) {
                    update_field('field_hj_faq_answer', $answer, $faq_id);
                } else {
                    update_post_meta($faq_id, 'faq_answer', $answer);
                }

                update_post_meta($faq_id, '_hj_legacy_faq_source', $legacy_key);
                update_post_meta($faq_id, '_hj_legacy_faq_page_id', $page_id);

                if ($term_id > 0) {
                    wp_set_object_terms($faq_id, [$term_id], 'faq_category', false);
                }

                $created++;
            }
        }

        return [
            'page_id' => $page_id,
            'created_terms' => $created_terms,
            'created' => $created,
            'skipped' => $skipped,
        ];
    }
}

add_action('admin_init', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    $key = 'hj_rewrite_flushed_faq_20260417';
    if (get_option($key) === '1') {
        return;
    }

    flush_rewrite_rules(false);
    update_option($key, '1');
});
