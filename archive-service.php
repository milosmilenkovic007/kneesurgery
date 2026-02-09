<?php
if (!defined('ABSPATH')) exit;

get_header();
?>

<main id="primary" class="site-main">
  <section class="hj-services-archive">
    <header class="hj-services-archive__header container">
      <h1 class="hj-services-archive__title"><?php echo esc_html(post_type_archive_title('', false)); ?></h1>
    </header>

    <div class="hj-services-archive__content container">
      <?php if (have_posts()) : ?>
        <div class="hj-services-archive__grid">
          <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('hj-service-card'); ?> id="post-<?php the_ID(); ?>">
              <a class="hj-service-card__link" href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) : ?>
                  <figure class="hj-service-card__media">
                    <?php the_post_thumbnail('medium', ['loading' => 'lazy', 'decoding' => 'async']); ?>
                  </figure>
                <?php endif; ?>

                <h2 class="hj-service-card__title"><?php the_title(); ?></h2>

                <?php if (has_excerpt()) : ?>
                  <div class="hj-service-card__excerpt">
                    <?php the_excerpt(); ?>
                  </div>
                <?php endif; ?>
              </a>
            </article>
          <?php endwhile; ?>
        </div>

        <nav class="hj-services-archive__pagination" aria-label="<?php esc_attr_e('Pagination', 'hello-elementor-child'); ?>">
          <?php
            the_posts_pagination([
              'mid_size' => 1,
              'prev_text' => __('Previous', 'hello-elementor-child'),
              'next_text' => __('Next', 'hello-elementor-child'),
            ]);
          ?>
        </nav>
      <?php else : ?>
        <div class="hj-services-archive__empty">
          <p><?php esc_html_e('No services found.', 'hello-elementor-child'); ?></p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
get_footer();
