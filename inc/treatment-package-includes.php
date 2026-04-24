<?php

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $default_package_content = 'Many patients choose knee surgery abroad for better affordability — but the price is only one small part of the treatment journey. Long-term surgical results depend on clinical expertise, implant quality, surgical precision and structured rehabilitation follow-up. We manage the entire process to keep your treatment consistent, medically supervised and focused on long-term mobility, stability, and joint function.';

    $group_array = [
        'key' => 'group_hj_treatment_package_includes',
        'title' => 'Treatment Package Includes',
        'fields' => [
            [
                'key' => 'field_hj_tpi_heading',
                'label' => 'Heading',
                'name' => 'package_includes_heading',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_content',
                'label' => 'Content',
                'name' => 'package_includes_content',
                'type' => 'textarea',
                'rows' => 5,
                'default_value' => $default_package_content,
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_included_title',
                'label' => 'Included Title',
                'name' => 'package_includes_included_title',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_included_items',
                'label' => 'Included Items',
                'name' => 'package_includes_included_items',
                'type' => 'repeater',
                'button_label' => 'Add Item',
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_hj_tpi_included_item_text',
                        'label' => 'Text',
                        'name' => 'text',
                        'type' => 'text',
                        'required' => 1,
                    ],
                ],
            ],
            [
                'key' => 'field_hj_tpi_price',
                'label' => 'Price',
                'name' => 'package_includes_price',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_price_symbol',
                'label' => 'Price Symbol',
                'name' => 'package_includes_price_symbol',
                'type' => 'text',
                'default_value' => '£',
                'required' => 0,
                'wrapper' => [
                    'width' => '25',
                ],
            ],
            [
                'key' => 'field_hj_tpi_price_note',
                'label' => 'Price Note',
                'name' => 'package_includes_price_note',
                'type' => 'text',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_button',
                'label' => 'Price Button',
                'name' => 'package_includes_price_button',
                'type' => 'link',
                'return_format' => 'array',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_tpi_rating',
                'label' => 'Google Reviews Summary',
                'name' => 'package_includes_rating',
                'type' => 'group',
                'sub_fields' => [
                    [
                        'key' => 'field_hj_tpi_rating_dynamic_message',
                        'label' => 'Dynamic Rating',
                        'name' => '',
                        'type' => 'message',
                        'message' => 'This block pulls the Google rating summary automatically from Theme Settings > Google Reviews.',
                        'new_lines' => 'wpautop',
                        'esc_html' => 0,
                    ],
                ],
            ],
        ],
        'location' => [
            [[ 'param' => 'post_type', 'operator' => '==', 'value' => 'service' ]],
        ],
        'position' => 'normal',
        'style' => 'default',
        'active' => true,
    ];

    acf_add_local_field_group($group_array);
});

function hj_tpi_has_target_data($post_id) {
    $values = [
        get_field('package_includes_heading', $post_id),
        get_field('package_includes_content', $post_id),
        get_field('package_includes_included_title', $post_id),
        get_field('package_includes_included_items', $post_id),
        get_field('package_includes_price', $post_id),
        get_field('package_includes_price_symbol', $post_id),
        get_field('package_includes_price_note', $post_id),
        get_field('package_includes_price_button', $post_id),
    ];

    foreach ($values as $value) {
        if (is_array($value) && !empty($value)) {
            return true;
        }

        if (!is_array($value) && trim((string) $value) !== '') {
            return true;
        }
    }

    return false;
}

function hj_tpi_get_meta_value(array $all_meta, $key, $default = '') {
    if (!array_key_exists($key, $all_meta) || !isset($all_meta[$key][0])) {
        return $default;
    }

    return maybe_unserialize($all_meta[$key][0]);
}

function hj_tpi_get_legacy_source_data($post_id) {
    $all_meta = get_post_meta($post_id);
    $modules_count = (int) hj_tpi_get_meta_value($all_meta, 'modules', 0);
    if ($modules_count < 1) {
        return null;
    }

    for ($module_index = 0; $module_index < $modules_count; $module_index++) {
        $module_prefix = 'modules_' . $module_index;
        if (hj_tpi_get_meta_value($all_meta, $module_prefix . '_acf_fc_layout', '') !== 'video_slider_sections') {
            continue;
        }

        $sections_count = (int) hj_tpi_get_meta_value($all_meta, $module_prefix . '_sections', 0);
        if ($sections_count < 1) {
            continue;
        }

        for ($section_index = 0; $section_index < $sections_count; $section_index++) {
            $section_prefix = $module_prefix . '_sections_' . $section_index;
            if (hj_tpi_get_meta_value($all_meta, $section_prefix . '_section_type', '') !== 'price') {
                continue;
            }

            $price_button = hj_tpi_get_meta_value($all_meta, $section_prefix . '_price_button', []);
            $price_button = is_array($price_button) ? $price_button : [];

            $data = [
                'heading' => trim((string) hj_tpi_get_meta_value($all_meta, $section_prefix . '_heading', '')),
                'content' => trim((string) hj_tpi_get_meta_value($all_meta, $section_prefix . '_content', '')),
                'included_title' => trim((string) hj_tpi_get_meta_value($all_meta, $section_prefix . '_included_title', '')),
                'included_items' => [],
                'price' => trim((string) hj_tpi_get_meta_value($all_meta, $section_prefix . '_price', '')),
                'price_note' => trim((string) hj_tpi_get_meta_value($all_meta, $section_prefix . '_price_note', '')),
                'price_button' => $price_button,
            ];

            $included_items_count = (int) hj_tpi_get_meta_value($all_meta, $section_prefix . '_included_items', 0);
            for ($item_index = 0; $item_index < $included_items_count; $item_index++) {
                $text = hj_tpi_get_meta_value($all_meta, $section_prefix . '_included_items_' . $item_index . '_text', '');
                $text = trim((string) $text);
                if ($text === '') {
                    continue;
                }

                $data['included_items'][] = [
                    'text' => $text,
                ];
            }

            $has_data = $data['heading'] !== ''
                || $data['content'] !== ''
                || $data['included_title'] !== ''
                || !empty($data['included_items'])
                || $data['price'] !== ''
                || $data['price_note'] !== ''
                || !empty($data['price_button']);

            if ($has_data) {
                return $data;
            }
        }
    }

    return null;
}

add_action('admin_init', function () {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (!function_exists('get_field') || !function_exists('update_field')) {
        return;
    }

    $migration_key = 'hj_tpi_migrated_20260323_v3';
    if (get_option($migration_key) === '1') {
        return;
    }

    $service_ids = get_posts([
        'post_type' => 'service',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'no_found_rows' => true,
        'suppress_filters' => true,
    ]);

    foreach ($service_ids as $post_id) {
        $post_id = (int) $post_id;
        if (!$post_id || hj_tpi_has_target_data($post_id)) {
            continue;
        }

        $source = hj_tpi_get_legacy_source_data($post_id);
        if (!$source) {
            continue;
        }

        update_field('field_hj_tpi_heading', $source['heading'], $post_id);
    update_field('field_hj_tpi_content', $source['content'], $post_id);
        update_field('field_hj_tpi_included_title', $source['included_title'], $post_id);
        update_field('field_hj_tpi_included_items', $source['included_items'], $post_id);
        update_field('field_hj_tpi_price', $source['price'], $post_id);
        update_field('field_hj_tpi_price_note', $source['price_note'], $post_id);
        update_field('field_hj_tpi_button', $source['price_button'], $post_id);
    }

    update_option($migration_key, '1');
});