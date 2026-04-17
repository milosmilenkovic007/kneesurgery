<?php
/**
 * Hello Elementor Child – core bootstrap
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('hj_get_theme_asset')) {
  function hj_get_theme_asset_manifest() {
    static $manifest = null;

    if ($manifest !== null) {
      return $manifest;
    }

    $manifest_path = get_stylesheet_directory() . '/dist/asset-manifest.json';
    if (!file_exists($manifest_path)) {
      $manifest = [];
      return $manifest;
    }

    $contents = file_get_contents($manifest_path);
    $decoded = json_decode($contents, true);
    $manifest = is_array($decoded) ? $decoded : [];

    return $manifest;
  }

  function hj_get_theme_asset($relative_path) {
    $relative_path = ltrim((string) $relative_path, '/');

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $manifest = hj_get_theme_asset_manifest();

    $candidates = [
      [
        'path' => $theme_dir . '/dist/' . $relative_path,
        'url' => $theme_uri . '/dist/' . $relative_path,
      ],
      [
        'path' => $theme_dir . '/' . $relative_path,
        'url' => $theme_uri . '/' . $relative_path,
      ],
    ];

    foreach ($candidates as $asset) {
      if (file_exists($asset['path'])) {
        $manifest_entry = $manifest[$relative_path] ?? null;

        return [
          'exists' => true,
          'path' => $asset['path'],
          'url' => $asset['url'],
          'version' => !empty($manifest_entry['version']) ? (string) $manifest_entry['version'] : (string) filemtime($asset['path']),
        ];
      }
    }

    return [
      'exists' => false,
      'path' => $theme_dir . '/' . $relative_path,
      'url' => $theme_uri . '/' . $relative_path,
      'version' => wp_get_theme(get_stylesheet())->get('Version') ?: '1.0.0',
    ];
  }
}

if (!function_exists('hj_is_ortho_single_template')) {
  function hj_is_ortho_single_template() {
    return is_singular('post') && get_page_template_slug(get_queried_object_id()) === 'single-ortho.php';
  }
}

if (!function_exists('hj_get_header_cta')) {
  function hj_get_header_cta() {
    $cta = [
      'label' => __('Let\'s Get in Touch', 'hello-elementor-child'),
      'url' => home_url('/contact/'),
      'target' => '',
    ];

    if (function_exists('get_field')) {
      $label = trim((string) get_field('header_cta_label', 'option'));
      $url = trim((string) get_field('header_cta_url', 'option'));
      $target = trim((string) get_field('header_cta_target', 'option'));

      if ($label !== '') {
        $cta['label'] = $label;
      }

      if ($url !== '') {
        $cta['url'] = $url;
      }

      if ($target !== '') {
        $cta['target'] = $target;
      }
    }

    return apply_filters('hj_header_cta', $cta);
  }
}

if (!function_exists('hj_is_desktop_header_menu_args')) {
  function hj_is_desktop_header_menu_args($args) {
    return is_object($args)
      && !empty($args->menu_class)
      && strpos((string) $args->menu_class, 'hj-site-header__menu') !== false;
  }
}

if (!function_exists('hj_get_menu_item_initials')) {
  function hj_get_menu_item_initials($title) {
    $title = trim(wp_strip_all_tags((string) $title));
    if ($title === '') {
      return 'T';
    }

    $words = preg_split('/[\s\-&]+/u', $title, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($words)) {
      $words = [$title];
    }

    $initials = '';

    foreach (array_slice($words, 0, 2) as $word) {
      $letter = function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
      $initials .= function_exists('mb_strtoupper') ? mb_strtoupper($letter) : strtoupper($letter);
    }

    return $initials !== '' ? $initials : 'T';
  }
}

if (!function_exists('hj_get_mega_menu_item_type_label')) {
  function hj_get_mega_menu_item_type_label($item) {
    $item_id = !empty($item->object_id) ? absint($item->object_id) : 0;

    if ($item_id > 0) {
      $post_type = get_post_type($item_id);
      $post_type_object = $post_type ? get_post_type_object($post_type) : null;

      if ($post_type_object && !empty($post_type_object->labels->singular_name)) {
        return (string) $post_type_object->labels->singular_name;
      }
    }

    return __('Explore', 'hello-elementor-child');
  }
}

if (!function_exists('hj_get_mega_menu_item_media')) {
  function hj_get_mega_menu_item_media($item, $title) {
    $item_id = !empty($item->object_id) ? absint($item->object_id) : 0;

    if ($item_id > 0 && has_post_thumbnail($item_id)) {
      return sprintf(
        '<span class="hj-mega-card__media">%s</span>',
        get_the_post_thumbnail($item_id, 'thumbnail', [
          'class' => 'hj-mega-card__image',
          'loading' => 'lazy',
          'decoding' => 'async',
        ])
      );
    }

    return sprintf(
      '<span class="hj-mega-card__media hj-mega-card__media--placeholder" aria-hidden="true"><span>%s</span></span>',
      esc_html(hj_get_menu_item_initials($title))
    );
  }
}

add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {
  if (!hj_is_desktop_header_menu_args($args)) {
    return $classes;
  }

  if ((int) $depth === 0) {
    $classes[] = 'hj-site-header__mega-panel';
  }

  if ((int) $depth === 1) {
    $classes[] = 'hj-site-header__mega-links';
  }

  return array_values(array_unique($classes));
}, 10, 3);

add_filter('nav_menu_css_class', function ($classes, $item, $args, $depth) {
  if (!hj_is_desktop_header_menu_args($args)) {
    return $classes;
  }

  if ((int) $depth === 1) {
    $classes[] = 'hj-site-header__mega-group';
  }

  if ((int) $depth === 2) {
    $classes[] = 'hj-site-header__mega-item';
  }

  return array_values(array_unique($classes));
}, 10, 4);

add_filter('nav_menu_link_attributes', function ($atts, $item, $args, $depth) {
  if (!hj_is_desktop_header_menu_args($args)) {
    return $atts;
  }

  $classes = trim((string) ($atts['class'] ?? ''));

  if ((int) $depth === 1) {
    $classes .= in_array('menu-item-has-children', (array) $item->classes, true)
      ? ' hj-site-header__mega-group-link'
      : ' hj-site-header__mega-standalone-link';
  }

  if ((int) $depth === 2) {
    $classes .= ' hj-mega-card';
  }

  if ($classes !== '') {
    $atts['class'] = trim($classes);
  }

  return $atts;
}, 10, 4);

add_filter('nav_menu_item_title', function ($title, $item, $args, $depth) {
  if (!hj_is_desktop_header_menu_args($args) || (int) $depth !== 2) {
    return $title;
  }

  $plain_title = trim(wp_strip_all_tags((string) $title));

  return sprintf(
    '<span class="hj-mega-card__inner">%1$s<span class="hj-mega-card__content"><span class="hj-mega-card__eyebrow">%2$s</span><span class="hj-mega-card__title">%3$s</span></span><span class="hj-mega-card__arrow" aria-hidden="true"></span></span>',
    hj_get_mega_menu_item_media($item, $plain_title),
    esc_html(hj_get_mega_menu_item_type_label($item)),
    esc_html($plain_title)
  );
}, 10, 4);

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
  $child_style = hj_get_theme_asset('style.css');
  $ortho_style = hj_get_theme_asset('assets/css/single-ortho.css');
  $ortho_script = hj_get_theme_asset('assets/js/single-ortho.js');
  $blog_single_style = hj_get_theme_asset('assets/css/single-article.css');
  $thank_you_style = hj_get_theme_asset('assets/css/page-thank-you.css');
  $faq_page_style = hj_get_theme_asset('assets/css/page-faq.css');
  $doctor_style = hj_get_theme_asset('assets/css/single-doctor.css');
  $site_header_style = hj_get_theme_asset('assets/css/site-header.css');
  $site_footer_style = hj_get_theme_asset('assets/css/site-footer.css');
  $site_header_script = hj_get_theme_asset('assets/js/site-header.js');
  $faq_page_script = hj_get_theme_asset('assets/js/page-faq.js');

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
    $child_style['url'],
    ['hello-elementor-parent'],
    $child_style['version']
  );

  wp_enqueue_style('hj-site-header', $site_header_style['url'], ['hello-elementor-child'], $site_header_style['version']);
  wp_enqueue_style('hj-site-footer', $site_footer_style['url'], ['hello-elementor-child'], $site_footer_style['version']);
  wp_enqueue_script('hj-site-header', $site_header_script['url'], [], $site_header_script['version'], true);

  // Load Ortho single assets only on our custom template
  if (hj_is_ortho_single_template()) {
    wp_enqueue_style('ortho-single', $ortho_style['url'], ['hello-elementor-child'], $ortho_style['version']);
    wp_enqueue_script('ortho-single', $ortho_script['url'], [], $ortho_script['version'], true);
  }

  if (is_singular('post') && !hj_is_ortho_single_template()) {
    wp_enqueue_style('hj-blog-single', $blog_single_style['url'], ['hello-elementor-child'], $blog_single_style['version']);
  }

  if (is_page_template('page-thank-you.php')) {
    wp_enqueue_style('hj-thank-you-page', $thank_you_style['url'], ['hello-elementor-child'], $thank_you_style['version']);
  }

  if (is_page_template('page-faq.php')) {
    wp_enqueue_style('hj-faq-page', $faq_page_style['url'], ['hello-elementor-child'], $faq_page_style['version']);
    wp_enqueue_script('hj-faq-page', $faq_page_script['url'], [], $faq_page_script['version'], true);
  }

  // Load Doctor single assets only on doctor pages
  if (is_singular('doctor')) {
    wp_enqueue_style('doctor-single', $doctor_style['url'], ['hello-elementor-child'], $doctor_style['version']);
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
  if (!hj_is_ortho_single_template() || !is_active_sidebar('ortho-sidebar')) {
    return;
  }

  $widget_style = hj_get_theme_asset('assets/css/widget-doctors-cta.css');

  wp_enqueue_style(
    'ortho-widget-doctors-cta',
    $widget_style['url'],
    ['hello-elementor-child'],
    $widget_style['version']
  );
}, 30);

// -----------------------------------------------------------------------------
//  Frontend performance: trim non-critical core assets and defer safe scripts
// -----------------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
  if (is_admin()) {
    return;
  }

  wp_dequeue_style('wp-block-library');
  wp_dequeue_style('wp-block-library-theme');
  wp_dequeue_style('classic-theme-styles');
  wp_dequeue_style('global-styles');
  wp_dequeue_script('wp-embed');
}, 100);

add_action('wp_default_scripts', function ($scripts) {
  if (is_admin() || empty($scripts->registered['jquery'])) {
    return;
  }

  $jquery = $scripts->registered['jquery'];
  if (!empty($jquery->deps)) {
    $jquery->deps = array_values(array_diff($jquery->deps, ['jquery-migrate']));
  }
});

add_filter('script_loader_tag', function ($tag, $handle, $src) {
  if (is_admin() || !$src) {
    return $tag;
  }

  $defer_src_patterns = [
    '/owl.carousel.min.js',
    '/allscripts.js',
    '/price-table.js',
    '/v4-shims.min.js',
  ];

  foreach ($defer_src_patterns as $pattern) {
    if (strpos($src, $pattern) !== false) {
      if (strpos($tag, ' defer') !== false) {
        return $tag;
      }

      return str_replace('<script ', '<script defer ', $tag);
    }
  }

  return $tag;
}, 10, 3);

/* ---------- Admin media uploader for widget ---------- */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'widgets.php' && $hook !== 'customize.php') return;
  $widget_media_script = hj_get_theme_asset('assets/js/widget-media.js');
  wp_enqueue_media();
  wp_enqueue_script(
    'ortho-widget-media',
    $widget_media_script['url'],
    ['jquery'],
    $widget_media_script['version'],
    true
  );
});

add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'post.php' && $hook !== 'post-new.php') {
    return;
  }

  $acf_collapse_script = hj_get_theme_asset('assets/admin/acf-flexible-collapse.js');
  $acf_module_defaults_script = hj_get_theme_asset('assets/admin/acf-module-defaults.js');

  wp_enqueue_script(
    'hj-acf-flexible-collapse',
    $acf_collapse_script['url'],
    ['jquery'],
    $acf_collapse_script['version'],
    true
  );

  wp_enqueue_script(
    'hj-acf-module-defaults',
    $acf_module_defaults_script['url'],
    ['jquery'],
    $acf_module_defaults_script['version'],
    true
  );

  if (function_exists('hj_get_medical_management_grid_defaults')) {
    wp_localize_script('hj-acf-module-defaults', 'hjAcfModuleDefaults', [
      'medicalManagementGrid' => hj_get_medical_management_grid_defaults(),
    ]);
  }
}, 20);

// -----------------------------------------------------------------------------
//  Includes (ACF options + flexible modules)
// -----------------------------------------------------------------------------
require_once get_stylesheet_directory() . '/inc/acf-options.php';
require_once get_stylesheet_directory() . '/inc/google-reviews.php';
require_once get_stylesheet_directory() . '/inc/modules.php';
require_once get_stylesheet_directory() . '/inc/articles-grid-ajax.php';
require_once get_stylesheet_directory() . '/inc/doctors.php';
require_once get_stylesheet_directory() . '/inc/faqs.php';
require_once get_stylesheet_directory() . '/inc/testimonials.php';
require_once get_stylesheet_directory() . '/inc/treatment-package-includes.php';
require_once get_stylesheet_directory() . '/inc/booking-form-submissions.php';
require_once get_stylesheet_directory() . '/inc/single-package-cardbox-leads.php';

// Admin/editor tweaks for the Price List template (includes Gutenberg disable)
if (is_admin()) {
  require_once get_stylesheet_directory() . '/inc/pricelist-setup.php';
}

// -----------------------------------------------------------------------------
//  CPT: Treatments (post_type key remains "service")
// -----------------------------------------------------------------------------
add_action('init', function () {
  $labels = [
    'name'                  => __('Treatments', 'hello-elementor-child'),
    'singular_name'         => __('Treatment', 'hello-elementor-child'),
    'menu_name'             => __('Treatments', 'hello-elementor-child'),
    'name_admin_bar'        => __('Treatment', 'hello-elementor-child'),
    'add_new'               => __('Add New', 'hello-elementor-child'),
    'add_new_item'          => __('Add New Treatment', 'hello-elementor-child'),
    'new_item'              => __('New Treatment', 'hello-elementor-child'),
    'edit_item'             => __('Edit Treatment', 'hello-elementor-child'),
    'view_item'             => __('View Treatment', 'hello-elementor-child'),
    'all_items'             => __('All Treatments', 'hello-elementor-child'),
    'search_items'          => __('Search Treatments', 'hello-elementor-child'),
    'not_found'             => __('No treatments found.', 'hello-elementor-child'),
    'not_found_in_trash'    => __('No treatments found in Trash.', 'hello-elementor-child'),
    'featured_image'        => __('Treatment Image', 'hello-elementor-child'),
    'set_featured_image'    => __('Set treatment image', 'hello-elementor-child'),
    'remove_featured_image' => __('Remove treatment image', 'hello-elementor-child'),
    'use_featured_image'    => __('Use as treatment image', 'hello-elementor-child'),
  ];

  $args = [
    'labels' => $labels,
    'public' => true,
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-clipboard',
    'has_archive' => 'treatments',
    'rewrite' => [
      'slug' => 'treatments',
      'with_front' => false,
    ],
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    'hierarchical' => false,
    'show_in_nav_menus' => true,
  ];

  register_post_type('service', $args);
});

// -----------------------------------------------------------------------------
//  CPT: Doctors
// -----------------------------------------------------------------------------
add_action('init', function () {
  $labels = [
    'name'                  => __('Doctors', 'hello-elementor-child'),
    'singular_name'         => __('Doctor', 'hello-elementor-child'),
    'menu_name'             => __('Doctors', 'hello-elementor-child'),
    'name_admin_bar'        => __('Doctor', 'hello-elementor-child'),
    'add_new'               => __('Add New', 'hello-elementor-child'),
    'add_new_item'          => __('Add New Doctor', 'hello-elementor-child'),
    'new_item'              => __('New Doctor', 'hello-elementor-child'),
    'edit_item'             => __('Edit Doctor', 'hello-elementor-child'),
    'view_item'             => __('View Doctor', 'hello-elementor-child'),
    'all_items'             => __('All Doctors', 'hello-elementor-child'),
    'search_items'          => __('Search Doctors', 'hello-elementor-child'),
    'not_found'             => __('No doctors found.', 'hello-elementor-child'),
    'not_found_in_trash'    => __('No doctors found in Trash.', 'hello-elementor-child'),
    'featured_image'        => __('Doctor Photo', 'hello-elementor-child'),
    'set_featured_image'    => __('Set doctor photo', 'hello-elementor-child'),
    'remove_featured_image' => __('Remove doctor photo', 'hello-elementor-child'),
    'use_featured_image'    => __('Use as doctor photo', 'hello-elementor-child'),
  ];

  $args = [
    'labels' => $labels,
    'public' => true,
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-id',
    'has_archive' => 'doctors',
    'rewrite' => [
      'slug' => 'doctors',
      'with_front' => false,
    ],
    'supports' => ['title', 'thumbnail', 'revisions', 'page-attributes'],
    'hierarchical' => false,
    'show_in_nav_menus' => true,
  ];

  register_post_type('doctor', $args);
});

// -----------------------------------------------------------------------------
//  CPT: Testimonials
// -----------------------------------------------------------------------------
add_action('init', function () {
  $labels = [
    'name'                  => __('Testimonials', 'hello-elementor-child'),
    'singular_name'         => __('Testimonial', 'hello-elementor-child'),
    'menu_name'             => __('Testimonials', 'hello-elementor-child'),
    'name_admin_bar'        => __('Testimonial', 'hello-elementor-child'),
    'add_new'               => __('Add New', 'hello-elementor-child'),
    'add_new_item'          => __('Add New Testimonial', 'hello-elementor-child'),
    'new_item'              => __('New Testimonial', 'hello-elementor-child'),
    'edit_item'             => __('Edit Testimonial', 'hello-elementor-child'),
    'view_item'             => __('View Testimonial', 'hello-elementor-child'),
    'all_items'             => __('All Testimonials', 'hello-elementor-child'),
    'search_items'          => __('Search Testimonials', 'hello-elementor-child'),
    'not_found'             => __('No testimonials found.', 'hello-elementor-child'),
    'not_found_in_trash'    => __('No testimonials found in Trash.', 'hello-elementor-child'),
    'featured_image'        => __('Testimonial Photo', 'hello-elementor-child'),
    'set_featured_image'    => __('Set testimonial photo', 'hello-elementor-child'),
    'remove_featured_image' => __('Remove testimonial photo', 'hello-elementor-child'),
    'use_featured_image'    => __('Use as testimonial photo', 'hello-elementor-child'),
  ];

  $args = [
    'labels' => $labels,
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-testimonial',
    'has_archive' => false,
    'rewrite' => false,
    'supports' => ['title', 'thumbnail', 'revisions', 'page-attributes'],
    'hierarchical' => false,
    'show_in_nav_menus' => false,
    'publicly_queryable' => false,
    'exclude_from_search' => true,
  ];

  register_post_type('testimonial', $args);
});

// -----------------------------------------------------------------------------
//  Doctors reorder (drag & drop) for service page related-doctors block
// -----------------------------------------------------------------------------
add_action('admin_menu', function () {
  add_submenu_page(
    'edit.php?post_type=doctor',
    __('Reorder Doctors', 'hello-elementor-child'),
    __('Reorder', 'hello-elementor-child'),
    'edit_posts',
    'hj-reorder-doctors',
    'hj_render_reorder_doctors_page'
  );
});

function hj_render_reorder_doctors_page() {
  if (!current_user_can('edit_posts')) {
    wp_die(esc_html__('You do not have permission to access this page.', 'hello-elementor-child'));
  }

  wp_enqueue_script('jquery-ui-sortable');

  $doctors = get_posts([
    'post_type' => 'doctor',
    'post_status' => ['publish', 'draft', 'pending', 'private'],
    'posts_per_page' => -1,
    'orderby' => [
      'menu_order' => 'ASC',
      'title' => 'ASC',
    ],
    'order' => 'ASC',
  ]);

  $nonce = wp_create_nonce('hj_doctors_reorder');
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('Reorder Doctors', 'hello-elementor-child'); ?></h1>
    <p><?php esc_html_e('Drag and drop doctors to set display order for the Service page related doctors block.', 'hello-elementor-child'); ?></p>

    <ul id="hj-doctors-sortable" style="max-width:760px; margin-top:16px;">
      <?php foreach ($doctors as $doctor): ?>
        <li class="hj-doctor-item" data-id="<?php echo esc_attr($doctor->ID); ?>" style="background:#fff; border:1px solid #dcdcde; padding:10px 12px; margin-bottom:8px; cursor:move;">
          <?php echo esc_html($doctor->post_title ?: __('(no title)', 'hello-elementor-child')); ?>
        </li>
      <?php endforeach; ?>
    </ul>

    <p>
      <button type="button" class="button button-primary" id="hj-save-doctors-order"><?php esc_html_e('Save order', 'hello-elementor-child'); ?></button>
      <span id="hj-order-status" style="margin-left:8px;"></span>
    </p>
  </div>

  <script>
    jQuery(function ($) {
      var $list = $('#hj-doctors-sortable');
      var $status = $('#hj-order-status');

      if ($list.length) {
        $list.sortable({
          axis: 'y',
          placeholder: 'ui-state-highlight'
        });
      }

      $('#hj-save-doctors-order').on('click', function () {
        var ids = $list.find('.hj-doctor-item').map(function () {
          return $(this).data('id');
        }).get();

        $status.text('<?php echo esc_js(__('Saving...', 'hello-elementor-child')); ?>');

        $.post(ajaxurl, {
          action: 'hj_save_doctors_order',
          nonce: '<?php echo esc_js($nonce); ?>',
          ids: ids
        })
        .done(function (response) {
          if (response && response.success) {
            $status.text('<?php echo esc_js(__('Saved.', 'hello-elementor-child')); ?>');
          } else {
            $status.text('<?php echo esc_js(__('Error while saving order.', 'hello-elementor-child')); ?>');
          }
        })
        .fail(function () {
          $status.text('<?php echo esc_js(__('Request failed.', 'hello-elementor-child')); ?>');
        });
      });
    });
  </script>
  <?php
}

add_action('wp_ajax_hj_save_doctors_order', function () {
  if (!current_user_can('edit_posts')) {
    wp_send_json_error(['message' => 'Forbidden'], 403);
  }

  check_ajax_referer('hj_doctors_reorder', 'nonce');

  $ids = isset($_POST['ids']) ? (array) $_POST['ids'] : [];
  $ids = array_values(array_filter(array_map('intval', $ids)));

  foreach ($ids as $index => $id) {
    if (get_post_type($id) !== 'doctor') {
      continue;
    }

    wp_update_post([
      'ID' => $id,
      'menu_order' => $index,
    ]);
  }

  wp_send_json_success();
});

// Doctors admin list: show and sort by display order (menu_order).
add_filter('manage_edit-doctor_columns', function ($columns) {
  $new_columns = [];

  foreach ($columns as $key => $label) {
    $new_columns[$key] = $label;

    if ($key === 'title') {
      $new_columns['doctor_order'] = __('Order', 'hello-elementor-child');
    }
  }

  if (!isset($new_columns['doctor_order'])) {
    $new_columns['doctor_order'] = __('Order', 'hello-elementor-child');
  }

  return $new_columns;
});

add_action('manage_doctor_posts_custom_column', function ($column, $post_id) {
  if ($column !== 'doctor_order') {
    return;
  }

  echo (int) get_post_field('menu_order', $post_id);
}, 10, 2);

add_filter('manage_edit-doctor_sortable_columns', function ($columns) {
  $columns['doctor_order'] = 'menu_order';
  return $columns;
});

add_action('pre_get_posts', function ($query) {
  if (!is_admin() || !$query->is_main_query()) {
    return;
  }

  if ($query->get('post_type') !== 'doctor') {
    return;
  }

  if ($query->get('orderby') === 'menu_order') {
    $query->set('orderby', ['menu_order' => 'ASC', 'title' => 'ASC']);
  }
});

// Taxonomy: Treatment Categories (hierarchical)
add_action('init', function () {
  $labels = [
    'name'              => __('Treatment Categories', 'hello-elementor-child'),
    'singular_name'     => __('Treatment Category', 'hello-elementor-child'),
    'search_items'      => __('Search Treatment Categories', 'hello-elementor-child'),
    'all_items'         => __('All Treatment Categories', 'hello-elementor-child'),
    'parent_item'       => __('Parent Treatment Category', 'hello-elementor-child'),
    'parent_item_colon' => __('Parent Treatment Category:', 'hello-elementor-child'),
    'edit_item'         => __('Edit Treatment Category', 'hello-elementor-child'),
    'update_item'       => __('Update Treatment Category', 'hello-elementor-child'),
    'add_new_item'      => __('Add New Treatment Category', 'hello-elementor-child'),
    'new_item_name'     => __('New Treatment Category Name', 'hello-elementor-child'),
    'menu_name'         => __('Treatment Categories', 'hello-elementor-child'),
  ];

  $args = [
    'hierarchical'      => true,
    'labels'            => $labels,
    'show_ui'           => true,
    'show_admin_column' => true,
    'query_var'         => true,
    'show_in_rest'      => true,
    'rewrite'           => [
      'slug' => 'treatment-category',
      'with_front' => false,
      'hierarchical' => true,
    ],
  ];

  register_taxonomy('treatment_category', ['service'], $args);
});

// One-time rewrite flush after changing CPT slug (theme has no activation hook).
add_action('admin_init', function () {
  if (!current_user_can('manage_options')) {
    return;
  }
  $key = 'hj_rewrite_flushed_treatments_20260217';
  if (get_option($key) === '1') {
    return;
  }
  flush_rewrite_rules(false);
  update_option($key, '1');
});

// One-time rewrite flush for Doctors permalinks.
add_action('admin_init', function () {
  if (!current_user_can('manage_options')) {
    return;
  }
  $key = 'hj_rewrite_flushed_doctors_20260217';
  if (get_option($key) === '1') {
    return;
  }
  flush_rewrite_rules(false);
  update_option($key, '1');
});

// One-time rewrite flush for the taxonomy permalinks.
add_action('admin_init', function () {
  if (!current_user_can('manage_options')) {
    return;
  }
  $key = 'hj_rewrite_flushed_treatment_category_20260217';
  if (get_option($key) === '1') {
    return;
  }
  flush_rewrite_rules(false);
  update_option($key, '1');
});

// -----------------------------------------------------------------------------
//  Editor: disable Gutenberg for Treatments (ACF Modules only)
// -----------------------------------------------------------------------------
add_filter('use_block_editor_for_post', function ($use_block_editor, $post) {
  if (!$post) { return $use_block_editor; }
  if (in_array($post->post_type, ['service', 'doctor', 'faq'], true)) {
    return false;
  }
  return $use_block_editor;
}, 10, 2);

// Back-compat: older Gutenberg filter name
add_filter('gutenberg_can_edit_post', function ($can_edit, $post) {
  if (!$post) { return $can_edit; }
  if (!empty($post->post_type) && in_array($post->post_type, ['service', 'doctor'], true)) {
    return false;
  }
  return $can_edit;
}, 10, 2);

// -----------------------------------------------------------------------------
//  Editor: globally disable Gutenberg block editor
// -----------------------------------------------------------------------------
add_filter('use_block_editor_for_post', '__return_false', 9999);
add_filter('use_block_editor_for_post_type', '__return_false', 9999);
add_filter('gutenberg_can_edit_post', '__return_false', 9999);
add_filter('gutenberg_can_edit_post_type', '__return_false', 9999);
