<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
the_post();

if (!function_exists('hj_blog_primary_category_name')) {
    function hj_blog_primary_category_name($post_id) {
        if (function_exists('yoast_get_primary_term_id')) {
            $term_id = yoast_get_primary_term_id('category', $post_id);
            if ($term_id && !is_wp_error($term_id)) {
                $term = get_term($term_id, 'category');
                if ($term && !is_wp_error($term)) {
                    return $term->name;
                }
            }
        }

        $cats = get_the_category($post_id);
        return !empty($cats) ? $cats[0]->name : __('Blog', 'hello-elementor-child');
    }
}

$cat_name = hj_blog_primary_category_name(get_the_ID());
$blog_url = get_post_type_archive_link('post') ?: home_url('/blog/');
$feat = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'ortho-hero') : '';
?>
<main id="primary" class="hj-blog-single">
  <section class="hj-blog-single__hero">
    <div class="hj-blog-single__hero-inner container">
      <div class="hj-blog-single__hero-copy">
        <nav class="hj-blog-single__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'hello-elementor-child'); ?>">
          <a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('Blog', 'hello-elementor-child'); ?></a>
          <span aria-hidden="true">›</span>
          <span><?php echo esc_html($cat_name); ?></span>
        </nav>

        <h1 class="hj-blog-single__title"><?php the_title(); ?></h1>
        <?php get_template_part('template-parts/authorbox'); ?>
      </div>

      <div class="hj-blog-single__hero-media">
        <?php if ($feat) : ?>
          <figure class="hj-blog-single__hero-figure">
            <img src="<?php echo esc_url($feat); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="eager" decoding="async">
          </figure>
        <?php else : ?>
          <div class="hj-blog-single__hero-placeholder" aria-hidden="true"></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="hj-blog-single__content-area container">
    <div class="hj-blog-single__grid">
      <article <?php post_class('hj-blog-single__content'); ?> id="post-<?php the_ID(); ?>">
        <?php
        the_content();
        wp_link_pages([
            'before' => '<div class="hj-blog-single__pages">' . __('Pages:', 'hello-elementor-child'),
            'after' => '</div>',
        ]);
        ?>
      </article>

      <aside class="hj-blog-single__sidebar">
        <?php get_sidebar('blog'); ?>
      </aside>
    </div>
  </section>

  <?php get_template_part('template-parts/related-articles'); ?>
</main>
<?php get_footer(); ?>