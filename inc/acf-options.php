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
        'capability' => 'edit_posts',
        'icon_url'   => 'dashicons-admin-generic',
        'position'   => 60,
        'redirect'   => false,
    ]);
}
