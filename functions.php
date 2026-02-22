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

  // Load Doctor single assets only on doctor pages
  if (is_singular('doctor')) {
    wp_enqueue_style('doctor-single', get_stylesheet_directory_uri() . '/assets/css/single-doctor.css', ['hello-elementor-child'], $child_ver);
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
require_once get_stylesheet_directory() . '/inc/articles-grid-ajax.php';
require_once get_stylesheet_directory() . '/inc/doctors.php';

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
  if (in_array($post->post_type, ['service', 'doctor'], true)) {
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
