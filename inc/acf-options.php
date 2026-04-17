<?php
/**
 * ACF Theme Options Pages
 */

// Register ACF Options Page for Theme Settings
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => 'Theme Settings',
        'menu_title' => 'Theme Settings',
        'menu_slug'  => 'theme-settings',
        'capability' => 'manage_options',
        'icon_url'   => 'dashicons-admin-generic',
        'position'   => 60,
        'redirect'   => false,
    ]);
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_theme-settings') {
        return;
    }

    if (!function_exists('hj_get_theme_asset')) {
        return;
    }

    $theme_settings_style = hj_get_theme_asset('assets/admin/theme-settings.css');
    if (!$theme_settings_style['exists']) {
        return;
    }

    wp_enqueue_style(
        'hj-theme-settings-admin',
        $theme_settings_style['url'],
        [],
        $theme_settings_style['version']
    );
}, 20);
