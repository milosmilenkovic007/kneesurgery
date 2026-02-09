<?php
if (!defined('ABSPATH')) exit;
$terms = wp_get_post_terms(get_the_ID(), 'category', ['fields'=>'ids']);
$args = [
  'post_type'           => 'post',
  'posts_per_page'      => 3,
  'post__not_in'        => [get_the_ID()],
  'ignore_sticky_posts' => true,
];
if (!empty($terms)) $args['category__in'] = $terms;

$q = new WP_Query($args);
if ($q->have_posts()): ?>
<section class="ortho-related">
  <div class="container">
    <div class="ortho-related__header">
      <h2><?php _e('Related articles','hello-elementor-child'); ?></h2>
      <a class="ortho-related__more" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/blog/')); ?>">
        <?php _e('See all articles','hello-elementor-child'); ?> →
      </a>
    </div>

    <div class="ortho-cards">
      <?php while($q->have_posts()): $q->the_post(); ?>
        <article class="ortho-card">
          <a class="ortho-card__media" href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()): the_post_thumbnail('ortho-card', ['loading'=>'lazy','decoding'=>'async']); endif; ?>
          </a>
          <div class="ortho-card__body">
            <?php
              $c = get_the_category();
              if ($c) echo '<span class="ortho-card__cat">'.esc_html($c[0]->name).'</span>';
            ?>
            <h3 class="ortho-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p class="ortho-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
            <div class="ortho-card__meta">
              <?php echo get_avatar(get_the_author_meta('ID'), 28, '', '', ['style'=>'border-radius:50%']); ?>
              <span class="name"><?php the_author(); ?></span>
              <span class="dot">•</span>
              <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
            </div>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>
