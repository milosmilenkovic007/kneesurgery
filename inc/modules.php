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
function hj_render_page_modules($post_id = null) {
    if (!function_exists('have_rows')) { return; }
    $post_id = $post_id ?: get_the_ID();
    if (!have_rows('modules', $post_id)) { return; }

    while (have_rows('modules', $post_id)) { the_row();
        $layout = get_row_layout();
        $layout_dash = str_replace('_', '-', $layout);
        $base = get_stylesheet_directory();

        // Enqueue CSS: try underscore name first, then dashed variant
        $css_candidates = [
            [ $base . '/assets/css/modules/' . $layout . '.css', get_stylesheet_directory_uri() . '/assets/css/modules/' . $layout . '.css' ],
            [ $base . '/assets/css/modules/' . $layout_dash . '.css', get_stylesheet_directory_uri() . '/assets/css/modules/' . $layout_dash . '.css' ],
        ];
        foreach ($css_candidates as [$path, $url]) {
            if (file_exists($path)) { wp_enqueue_style('hj-module-' . $layout, $url, [], wp_get_theme()->get('Version')); break; }
        }

        // Template include: try underscore folder then dashed folder
        $tpl_candidates = [
            $base . '/modules/' . $layout . '/module.php',
            $base . '/modules/' . $layout_dash . '/module.php',
        ];
        foreach ($tpl_candidates as $template) {
            if (file_exists($template)) { include $template; break; }
        }
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
