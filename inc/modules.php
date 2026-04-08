<?php
// Modular ACF Flexible Content: load each module's fields from /modules/{module}/fields.json
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) { return; }

    $modules_dir = get_stylesheet_directory() . '/modules';
    $layouts = [];

    if (is_dir($modules_dir)) {
        foreach (glob($modules_dir . '/*/fields.json') as $jsonFile) {
            $cfg = json_decode(file_get_contents($jsonFile), true);
            if (!$cfg || empty($cfg['name']) || empty($cfg['label'])) { continue; }
            $layout_key = !empty($cfg['key']) ? $cfg['key'] : ('layout_' . sanitize_key($cfg['name']));
            $layouts[$layout_key] = [
                'key' => $layout_key,
                'name' => $cfg['name'],
                'label' => $cfg['label'],
                'display' => isset($cfg['display']) ? $cfg['display'] : 'block',
                'sub_fields' => isset($cfg['sub_fields']) ? $cfg['sub_fields'] : [],
            ];
        }
    }

    $group_array = [
        'key' => 'group_hj_modules_pricelist',
        'title' => 'Page Modules',
        'fields' => [[
            'key' => 'field_hj_modules_fc',
            'label' => 'Modules',
            'name' => 'modules',
            'type' => 'flexible_content',
            'button_label' => 'Add Module',
            'layouts' => $layouts,
        ]],
        'location' => [
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'default' ]],
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-home.php' ]],
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php' ]],
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-stories.php' ]],
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-pricelist.php' ]],
            // Back-compat for pages still assigned to the legacy template filename.
            [[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-pricelist-dental.php' ]],
        ],
        'position' => 'acf_after_title',
        'style' => 'seamless',
        'active' => true,
        'modified' => time()
    ];

    acf_add_local_field_group($group_array);

    // Treatments: same modular flexible content field, attached to CPT "service"
    $service_group_array = [
        'key' => 'group_hj_modules_service',
        'title' => 'Treatment Modules',
        'fields' => [[
            'key' => 'field_hj_modules_fc_service',
            'label' => 'Modules',
            'name' => 'modules',
            'type' => 'flexible_content',
            'button_label' => 'Add Module',
            'layouts' => $layouts,
        ]],
        'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'service' ]]],
        'position' => 'acf_after_title',
        'style' => 'seamless',
        'active' => true,
        'modified' => time()
    ];

    acf_add_local_field_group($service_group_array);

    // Ensure local JSON sync is available and write current group to acf-json
    $json_dir = get_stylesheet_directory() . '/acf-json';
    if (!is_dir($json_dir)) { wp_mkdir_p($json_dir); }
    $json_file = $json_dir . '/group_hj_modules_pricelist.json';
    if (is_writable($json_dir)) {
        // Write pretty JSON so it appears under ACF sync
        file_put_contents($json_file, wp_json_encode($group_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service_json_file = $json_dir . '/group_hj_modules_service.json';
        file_put_contents($service_json_file, wp_json_encode($service_group_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
});

// Render helper for modules; include modules/{layout}/module.php and enqueue matching CSS
function hj_include_module_template($template_path) {
    include $template_path;
}

function hj_render_page_modules($post_id = null) {
    if (!function_exists('have_rows')) { return; }
    $post_id = $post_id ?: get_the_ID();
    if (!have_rows('modules', $post_id)) { return; }

    global $post;

    $original_post = $post ?? null;

    while (have_rows('modules', $post_id)) { the_row();
        $layout = get_row_layout();
        $layout_dash = str_replace('_', '-', $layout);
        $base = get_stylesheet_directory();

        // Enqueue CSS: try underscore name first, then dashed variant
        $css_candidates = [
            'assets/css/modules/' . $layout . '.css',
            'assets/css/modules/' . $layout_dash . '.css',
        ];
        foreach ($css_candidates as $relative_path) {
            $asset = hj_get_theme_asset($relative_path);
            if ($asset['exists']) {
                wp_enqueue_style('hj-module-' . $layout, $asset['url'], [], $asset['version']);
                break;
            }
        }

        // Enqueue JS: try underscore name first, then dashed variant
        $js_candidates = [
            'assets/js/modules/' . $layout . '.js',
            'assets/js/modules/' . $layout_dash . '.js',
        ];
        foreach ($js_candidates as $relative_path) {
            $asset = hj_get_theme_asset($relative_path);
            if ($asset['exists']) {
                wp_enqueue_script('hj-module-' . $layout, $asset['url'], [], $asset['version'], true);
                break;
            }
        }

        // Template include: try underscore folder then dashed folder
        $tpl_candidates = [
            $base . '/modules/' . $layout . '/module.php',
            $base . '/modules/' . $layout_dash . '/module.php',
        ];
        foreach ($tpl_candidates as $template) {
            if (file_exists($template)) {
                hj_include_module_template($template);

                $post = $original_post;
                if ($original_post instanceof WP_Post) {
                    setup_postdata($original_post);
                } else {
                    wp_reset_postdata();
                }

                break;
            }
        }
    }

    $post = $original_post;
    if ($original_post instanceof WP_Post) {
        setup_postdata($original_post);
    } else {
        wp_reset_postdata();
    }
}

// ACF JSON paths for load/save to enable Sync UI in backend
add_filter('acf/settings/save_json', function ($path) {
    return get_stylesheet_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
});

// Populate FluentForms select (CTA – Image + Form)
add_filter('acf/load_field/key=field_hj_cfb_fluent_form_id', function ($field) {
    $field['choices'] = [];

    // FluentForms exposes wpFluent() in most installs.
    if (!function_exists('wpFluent')) {
        return $field;
    }

    try {
        $forms = wpFluent()
            ->table('fluentform_forms')
            ->select(['id', 'title'])
            ->orderBy('id', 'desc')
            ->get();

        if (is_array($forms)) {
            foreach ($forms as $form) {
                $id = is_object($form) ? ($form->id ?? null) : ($form['id'] ?? null);
                $title = is_object($form) ? ($form->title ?? '') : ($form['title'] ?? '');
                if (!$id) { continue; }
                $field['choices'][(string) $id] = $title ? $title : ('Form #' . $id);
            }
        }
    } catch (Throwable $e) {
        // Leave choices empty if FluentForms isn't available or DB query fails.
    }

    return $field;
});

if (!function_exists('hj_get_medical_management_grid_defaults')) {
    function hj_get_medical_management_grid_defaults() {
        return [
            'title' => 'What',
            'title_accent' => 'Medical Management & Coordination',
            'title_suffix' => 'Mean To You',
            'intro' => 'Medical treatment abroad is not like treatment at home. Different systems. Different languages. Different levels of follow-up. We are here to fill the gaps, reduce risks, and protect patients at every stage.',
            'items' => [
                [
                    'icon_key' => 'doctor-01',
                    'icon_path' => 'assets/img/icons/doctor-01.svg',
                    'title' => 'Medical Matching - Not Marketing',
                    'description' => "We choose doctors and hospitals based on medical experience - not price or popularity. You're matched with professionals who know your condition well.",
                ],
                [
                    'icon_key' => 'globe',
                    'icon_path' => 'assets/img/icons/globe.svg',
                    'title' => "We Don't Just Organize - We Safeguard",
                    'description' => 'We actively manage your journey - from pre-medical planning to recovery - ensuring everything is clear, coordinated, and under control.',
                ],
                [
                    'icon_key' => 'cardiogram-01',
                    'icon_path' => 'assets/img/icons/cardiogram-01.svg',
                    'title' => 'Complete Medical Documentation',
                    'description' => 'Healing Journey goes beyond standard facilitation by actively managing the patient journey on the ground, including coordination, follow-up, and process control.',
                ],
                [
                    'icon_key' => '7',
                    'icon_path' => 'assets/img/icons/7.svg',
                    'title' => 'You Are never Alone',
                    'description' => 'A dedicated coordinator supports you throughout - in coordination with the medical team - from arrival to recovery and follow-up.',
                ],
                [
                    'icon_key' => 'customer-service-02',
                    'icon_path' => 'assets/img/icons/customer-service-02.svg',
                    'title' => 'Support Beyond Hospital Walls & Organize Your Follow-ups',
                    'description' => "We visit you during hotel recovery, monitor your condition, and organize follow-ups. We cover what hospitals don't.",
                ],
                [
                    'icon_key' => 'doctor-01',
                    'icon_path' => 'assets/img/icons/doctor-01.svg',
                    'title' => 'Complication & Cost Management',
                    'description' => 'If something goes wrong, we act - medically and financially - to protect your health and your budget.',
                ],
            ],
        ];
    }
}

if (!function_exists('hj_get_medical_management_grid_icon_choices')) {
    function hj_get_medical_management_grid_icon_choices() {
        return [
            'doctor-01' => 'Doctor 01',
            'globe' => 'Globe',
            'cardiogram-01' => 'Cardiogram 01',
            '7' => 'Support 7',
            'customer-service-02' => 'Customer Service 02',
        ];
    }
}

add_filter('acf/load_field/key=field_hj_mmg_item_icon_key', function ($field) {
    $field['choices'] = hj_get_medical_management_grid_icon_choices();
    return $field;
});

add_filter('acf/load_value/key=field_hj_mmg_title', function ($value) {
    if (trim((string) $value) !== '') {
        return $value;
    }

    $defaults = hj_get_medical_management_grid_defaults();
    return $defaults['title'] ?? $value;
}, 10, 1);

add_filter('acf/load_value/key=field_hj_mmg_title_accent', function ($value) {
    if (trim((string) $value) !== '') {
        return $value;
    }

    $defaults = hj_get_medical_management_grid_defaults();
    return $defaults['title_accent'] ?? $value;
}, 10, 1);

add_filter('acf/load_value/key=field_hj_mmg_title_suffix', function ($value) {
    if (trim((string) $value) !== '') {
        return $value;
    }

    $defaults = hj_get_medical_management_grid_defaults();
    return $defaults['title_suffix'] ?? $value;
}, 10, 1);

add_filter('acf/load_value/key=field_hj_mmg_intro', function ($value) {
    if (trim((string) $value) !== '') {
        return $value;
    }

    $defaults = hj_get_medical_management_grid_defaults();
    return $defaults['intro'] ?? $value;
}, 10, 1);

add_filter('acf/load_value/key=field_hj_mmg_items', function ($value) {
    if (!function_exists('hj_get_medical_management_grid_defaults')) {
        return $value;
    }

    $has_meaningful_value = false;

    if (is_array($value) && !empty($value)) {
        foreach ($value as $row) {
            $row_title = trim((string) ($row['title'] ?? ''));
            $row_description = trim((string) ($row['description'] ?? ''));
            $row_icon = $row['icon'] ?? null;
            $row_icon_key = trim((string) ($row['icon_key'] ?? ''));

            if ($row_title !== '' || $row_description !== '' || !empty($row_icon) || $row_icon_key !== '') {
                $has_meaningful_value = true;
                break;
            }
        }
    }

    if ($has_meaningful_value) {
        return $value;
    }

    $defaults = hj_get_medical_management_grid_defaults();
    $items = $defaults['items'] ?? [];

    if (empty($items) || !is_array($items)) {
        return $value;
    }

    return array_map(function ($item) {
        return [
            'icon_key' => $item['icon_key'] ?? '',
            'title' => $item['title'] ?? '',
            'description' => $item['description'] ?? '',
        ];
    }, $items);
}, 10, 1);
