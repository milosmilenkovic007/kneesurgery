<?php
if (!defined('ABSPATH')) {
    exit;
}

$treatments = new WP_Query([
    'post_type' => 'service',
    'post_status' => 'publish',
    'posts_per_page' => 8,
    'orderby' => [
        'menu_order' => 'ASC',
        'title' => 'ASC',
    ],
    'order' => 'ASC',
    'no_found_rows' => true,
]);
?>

<?php if ($treatments->have_posts()) : ?>
  <section class="widget hj-blog-sidebar-card hj-blog-sidebar-card--treatments" aria-label="<?php esc_attr_e('Treatments', 'hello-elementor-child'); ?>">
    <div class="hj-blog-sidebar-card__head">
      <p class="hj-blog-sidebar-card__eyebrow"><?php esc_html_e('Explore Care', 'hello-elementor-child'); ?></p>
      <h3 class="widget-title"><?php esc_html_e('Treatments', 'hello-elementor-child'); ?></h3>
    </div>

    <div class="hj-blog-treatments-list">
      <?php while ($treatments->have_posts()) : $treatments->the_post(); ?>
        <a class="hj-blog-treatments-list__item" href="<?php the_permalink(); ?>">
          <span class="hj-blog-treatments-list__label"><?php the_title(); ?></span>
          <span class="hj-blog-treatments-list__arrow" aria-hidden="true">→</span>
        </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <a class="hj-blog-sidebar-card__cta btn btn-primary" href="<?php echo esc_url(get_post_type_archive_link('service') ?: home_url('/treatments/')); ?>">
      <?php esc_html_e('View All Treatments', 'hello-elementor-child'); ?>
    </a>
  </section>
<?php endif; ?>

<?php if (is_active_sidebar('ortho-sidebar')) : ?>
  <?php dynamic_sidebar('ortho-sidebar'); ?>
<?php endif; ?>