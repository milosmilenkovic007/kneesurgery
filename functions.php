<?php
/**
 * Enqueue parent and child theme styles.
 */
add_action('wp_enqueue_scripts', function () {
    // Fonts
    wp_enqueue_style(
        'hj-source-serif-4',
        'https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap',
        [],
        null
    );
    // Parent theme style
    wp_enqueue_style(
        'hello-elementor-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('hello-elementor')->get('Version')
    );

    // Child theme style
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_uri(),
        ['hello-elementor-style'],
        wp_get_theme()->get('Version')
    );
});

// Admin setup: disable Gutenberg for the pricelist template
require_once get_stylesheet_directory() . '/inc/pricelist-setup.php';
// ACF Options Pages
require_once get_stylesheet_directory() . '/inc/acf-options.php';
// Modules
require_once get_stylesheet_directory() . '/inc/modules.php';
// PDF generator
require_once get_stylesheet_directory() . '/inc/pdf-pricelist.php';
// Word export
require_once get_stylesheet_directory() . '/inc/export-doc.php';
// Candidate uploads
require_once get_stylesheet_directory() . '/inc/candidate-upload.php';
// Pricelist package defaults
require_once get_stylesheet_directory() . '/inc/pricelist-package-defaults.php';

// Globally remove the legacy "Custom Fields" meta box across all post types
add_action('add_meta_boxes', function () {
    $types = get_post_types([], 'names');
    foreach ($types as $pt) {
        remove_meta_box('postcustom', $pt, 'normal');
    }
}, 100);

// Admin: auto-load posts in Articles Grid relationship picker
add_action('acf/input/admin_enqueue_scripts', function(){
    wp_enqueue_script(
        'hj-acf-autoload-relationship',
        get_stylesheet_directory_uri() . '/assets/admin/acf-relationship-autoload.js',
        ['jquery'],
        wp_get_theme()->get('Version'),
        true
    );
});

// Try to load Composer autoloader for libraries (e.g., dompdf)
if (file_exists(get_stylesheet_directory() . '/vendor/autoload.php')) {
    require_once get_stylesheet_directory() . '/vendor/autoload.php';
}
