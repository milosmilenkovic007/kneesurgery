<?php
/*
Template Name: Ortho Single (Blog)
Template Post Type: post
*/
if (!defined('ABSPATH')) exit;
get_header();
the_post();

/** Helpers */
function ortho_primary_category_name($post_id){
  // Yoast primary term if available
  if (function_exists('yoast_get_primary_term_id')) {
    $term_id = yoast_get_primary_term_id('category', $post_id);
    if ($term_id && !is_wp_error($term_id)) {
      $term = get_term($term_id, 'category');
      if ($term && !is_wp_error($term)) return $term->name;
    }
  }
  $cats = get_the_category($post_id);
  return !empty($cats) ? $cats[0]->name : __('Blog','hello-elementor-child');
}

$cat_name = ortho_primary_category_name(get_the_ID());
$blog_url = get_post_type_archive_link('post') ?: home_url('/blog/');
$feat     = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'ortho-hero') : '';
?>
<main id="primary" class="ortho-single">

  <!-- HERO -->
  <section class="ortho-hero">
    <div class="ortho-hero__inner container">
      <div class="ortho-hero__left">
        <nav class="ortho-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb','hello-elementor-child'); ?>">
          <a href="<?php echo esc_url($blog_url); ?>"><?php _e('Blog','hello-elementor-child'); ?></a>
          <span aria-hidden="true">›</span>
          <span><?php echo esc_html($cat_name); ?></span>
        </nav>

        <h1 class="ortho-title"><?php the_title(); ?></h1>

        <?php get_template_part('template-parts/authorbox'); ?>
      </div>

      <div class="ortho-hero__right">
        <?php if($feat): ?>
          <figure class="ortho-hero__figure">
            <img src="<?php echo esc_url($feat); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="eager" decoding="async">
          </figure>
        <?php else: ?>
          <div class="ortho-hero__placeholder" aria-hidden="true"></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- CONTENT + SIDEBAR -->
  <section class="ortho-content-area container">
    <div class="ortho-grid">
      <article <?php post_class('ortho-post-content'); ?> id="post-<?php the_ID(); ?>">
        <?php
          the_content();
          wp_link_pages([
            'before' => '<div class="ortho-pages">'.__('Pages:','hello-elementor-child'),
            'after'  => '</div>'
          ]);
        ?>
      </article>

      <aside class="ortho-sidebar">
        <?php get_sidebar('ortho'); // expects sidebar-ortho.php in the theme root ?>
      </aside>
    </div>
  </section>

  <!-- RELATED -->
  <?php get_template_part('template-parts/related-articles'); ?>

</main>
<?php get_footer(); ?>
