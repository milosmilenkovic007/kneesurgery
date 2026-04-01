<?php
if (!defined('ABSPATH')) exit;

$title = get_sub_field('title') ?: '';
$intro = get_sub_field('intro') ?: '';

$treatment_id = get_the_ID();
if (!$treatment_id) {
  return;
}

$q = new WP_Query([
  'post_type' => 'doctor',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
  'meta_query' => [[
    'key' => 'treatments',
    'value' => '"' . (string) $treatment_id . '"',
    'compare' => 'LIKE',
  ]],
]);

if (!$q->have_posts()) {
  wp_reset_postdata();
  return;
}

$doctor_count = (int) $q->post_count;
$arrow_left = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-left.svg';
$arrow_right = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-right.svg';
?>
<section class="hj-related-doctors" aria-label="Related doctors">
  <div class="hj-rd-wrap">
    <?php if ($title || $intro): ?>
      <div class="hj-cb-header">
        <?php if ($title): ?>
          <h2 class="hj-cb-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if ($intro): ?>
          <p class="hj-cb-subheading"><?php echo wp_kses_post($intro); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="hj-rd-stage" data-rd-slider>
      <button class="hj-rd-arrow hj-rd-arrow--prev" type="button" aria-label="Previous doctors" data-rd-prev <?php disabled($doctor_count <= 3); ?>>
        <img src="<?php echo esc_url($arrow_left); ?>" alt="" aria-hidden="true">
      </button>

      <div class="hj-rd-viewport">
        <div class="hj-rd-grid" data-rd-track>
          <?php while ($q->have_posts()): $q->the_post();
            $doctor_id = get_the_ID();
            $bio = function_exists('get_field') ? get_field('short_bio', $doctor_id) : '';
            $doctor_title = function_exists('get_field') ? get_field('doctor_title', $doctor_id) : '';
          ?>
            <div class="hj-rd-slide" data-rd-slide>
              <article <?php post_class('hj-rd-card'); ?> id="doctor-<?php echo esc_attr($doctor_id); ?>">
                <a class="hj-rd-card__link" href="<?php the_permalink(); ?>">
                  <?php if (has_post_thumbnail()): ?>
                    <figure class="hj-rd-card__media">
                      <?php the_post_thumbnail('medium', ['loading' => 'lazy', 'decoding' => 'async']); ?>
                    </figure>
                  <?php endif; ?>

                  <h3 class="hj-rd-card__name"><?php the_title(); ?></h3>

                  <?php if (!empty($doctor_title)): ?>
                    <div class="hj-rd-card__title"><?php echo esc_html($doctor_title); ?></div>
                  <?php endif; ?>

                  <?php if (!empty($bio)): ?>
                    <div class="hj-rd-card__bio"><?php echo wp_kses_post($bio); ?></div>
                  <?php endif; ?>

                  <span class="hj-rd-card__btn" aria-hidden="true">Learn more</span>
                </a>
              </article>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <button class="hj-rd-arrow hj-rd-arrow--next" type="button" aria-label="Next doctors" data-rd-next <?php disabled($doctor_count <= 3); ?>>
        <img src="<?php echo esc_url($arrow_right); ?>" alt="" aria-hidden="true">
      </button>
    </div>

    <div class="hj-rd-dots" data-rd-dots aria-label="Doctor slider pagination"></div>
    </div>
  </div>
</section>
<?php
wp_reset_postdata();
