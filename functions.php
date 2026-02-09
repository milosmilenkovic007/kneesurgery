<?php
/**
 * Hello Elementor Child – core bootstrap
 */

if (!defined('ABSPATH')) exit;

// -----------------------------------------------------------------------------
//  Google Tag Manager (GTM) – <head> i posle <body>
// -----------------------------------------------------------------------------
// <head> skripta (što više u <head>, prioritet 0)
add_action('wp_head', function () {
?>
<!-- Google Tag Manager -->
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NQF5LP95');
</script>
<!-- End Google Tag Manager -->
<?php
}, 0);

// <body> noscript (odmah nakon <body>, kroz wp_body_open)
add_action('wp_body_open', function () {
?>
<!-- Google Tag Manager (noscript) -->
<noscript>
  <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NQF5LP95"
          height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
<?php
});

// -----------------------------------------------------------------------------
//  Theme setup
// -----------------------------------------------------------------------------
add_action('after_setup_theme', function () {
  // i18n
  load_child_theme_textdomain('hello-elementor-child', get_stylesheet_directory() . '/languages');

  // supports
  add_theme_support('post-thumbnails');
  add_theme_support('title-tag');
  add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','script','style']);

  // custom sizes (used by the Ortho single template)
  add_image_size('ortho-hero', 1440, 900, true);
  add_image_size('ortho-card', 720, 480, true);
});

// -----------------------------------------------------------------------------
//  Media: allow SVG uploads
// -----------------------------------------------------------------------------
add_filter('upload_mimes', function ($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  if ($ext === 'svg') {
    $data['ext'] = 'svg';
    $data['type'] = 'image/svg+xml';
    $data['proper_filename'] = $filename;
  }
  return $data;
}, 10, 4);

// -----------------------------------------------------------------------------
//  Enqueue parent & child assets
// -----------------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
  $child = wp_get_theme(get_stylesheet());
  $child_ver = $child->get('Version') ?: '1.0.0';

  // Parent Hello Elementor style
  wp_enqueue_style(
    'hello-elementor-parent',
    get_template_directory_uri() . '/style.css',
    [],
    wp_get_theme(get_template())->get('Version')
  );

  // Child style.css
  wp_enqueue_style(
    'hello-elementor-child',
    get_stylesheet_uri(),
    ['hello-elementor-parent'],
    $child_ver
  );

  // Load Ortho single assets only on our custom template
  if (is_singular('post') && get_page_template_slug(get_queried_object_id()) === 'single-ortho.php') {
    wp_enqueue_style('ortho-single', get_stylesheet_directory_uri() . '/assets/css/single-ortho.css', ['hello-elementor-child'], $child_ver);
    wp_enqueue_script('ortho-single', get_stylesheet_directory_uri() . '/assets/js/single-ortho.js', [], $child_ver, true);
  }
}, 20);

// -----------------------------------------------------------------------------
//  Sidebar (for sticky right column in Ortho layout)
// -----------------------------------------------------------------------------
add_action('widgets_init', function () {
  register_sidebar([
    'name'          => __('Ortho Sidebar', 'hello-elementor-child'),
    'id'            => 'ortho-sidebar',
    'description'   => __('Widgets here appear in the sticky sidebar on the Ortho single template.', 'hello-elementor-child'),
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ]);
});

// -----------------------------------------------------------------------------
//  Safety: ensure template header is respected even with Elementor Theme Builder
// -----------------------------------------------------------------------------
add_filter('theme_page_templates', function ($templates) {
  // make sure WP lists our template in the selector if needed
  $templates['single-ortho.php'] = __('Ortho Single (Blog)', 'hello-elementor-child');
  return $templates;
});

/* ---------- Register Doctors CTA widget ---------- */
require_once get_stylesheet_directory() . '/inc/widgets/class-ortho-doctors-cta.php';
add_action('widgets_init', function () {
  register_widget('Ortho_Doctors_CTA_Widget');
});

/* ---------- Front styles for the widget ---------- */
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'ortho-widget-doctors-cta',
    get_stylesheet_directory_uri() . '/assets/css/widget-doctors-cta.css',
    ['hello-elementor-child'],
    '1.0.0'
  );
}, 30);

/* ---------- Admin media uploader for widget ---------- */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'widgets.php' && $hook !== 'customize.php') return;
  wp_enqueue_media();
  wp_enqueue_script(
    'ortho-widget-media',
    get_stylesheet_directory_uri() . '/assets/js/widget-media.js',
    ['jquery'],
    '1.0.0',
    true
  );
});

// -----------------------------------------------------------------------------
//  Includes (ACF options + flexible modules)
// -----------------------------------------------------------------------------
require_once get_stylesheet_directory() . '/inc/acf-options.php';
require_once get_stylesheet_directory() . '/inc/modules.php';

// -----------------------------------------------------------------------------
//  CPT: Services
// -----------------------------------------------------------------------------
add_action('init', function () {
  $labels = [
    'name'                  => __('Services', 'hello-elementor-child'),
    'singular_name'         => __('Service', 'hello-elementor-child'),
    'menu_name'             => __('Services', 'hello-elementor-child'),
    'name_admin_bar'        => __('Service', 'hello-elementor-child'),
    'add_new'               => __('Add New', 'hello-elementor-child'),
    'add_new_item'          => __('Add New Service', 'hello-elementor-child'),
    'new_item'              => __('New Service', 'hello-elementor-child'),
    'edit_item'             => __('Edit Service', 'hello-elementor-child'),
    'view_item'             => __('View Service', 'hello-elementor-child'),
    'all_items'             => __('All Services', 'hello-elementor-child'),
    'search_items'          => __('Search Services', 'hello-elementor-child'),
    'not_found'             => __('No services found.', 'hello-elementor-child'),
    'not_found_in_trash'    => __('No services found in Trash.', 'hello-elementor-child'),
    'featured_image'        => __('Service Image', 'hello-elementor-child'),
    'set_featured_image'    => __('Set service image', 'hello-elementor-child'),
    'remove_featured_image' => __('Remove service image', 'hello-elementor-child'),
    'use_featured_image'    => __('Use as service image', 'hello-elementor-child'),
  ];

  $args = [
    'labels' => $labels,
    'public' => true,
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-clipboard',
    'has_archive' => true,
    'rewrite' => [
      'slug' => 'services',
      'with_front' => false,
    ],
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    'hierarchical' => false,
    'show_in_nav_menus' => true,
  ];

  register_post_type('service', $args);
});

// -----------------------------------------------------------------------------
//  Editor: disable Gutenberg for Services (ACF Modules only)
// -----------------------------------------------------------------------------
add_filter('use_block_editor_for_post', function ($use_block_editor, $post) {
  if (!$post) { return $use_block_editor; }
  if ($post->post_type === 'service') {
    return false;
  }
  return $use_block_editor;
}, 10, 2);

// Back-compat: older Gutenberg filter name
add_filter('gutenberg_can_edit_post', function ($can_edit, $post) {
  if (!$post) { return $can_edit; }
  if (!empty($post->post_type) && $post->post_type === 'service') {
    return false;
  }
  return $can_edit;
}, 10, 2);
