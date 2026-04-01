<?php
if (!defined('ABSPATH')) exit;

$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$selected_testimonials = get_sub_field('selected_testimonials') ?: [];
$legacy_items = get_sub_field('items') ?: [];
$uid = uniqid('hj-ts-');

$arrow_left = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-left.svg';
$arrow_right = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-right.svg';
$quote_icon = get_stylesheet_directory_uri() . '/assets/img/icons/quote.svg';

$items = function_exists('hj_get_testimonials_for_slider') ? hj_get_testimonials_for_slider($selected_testimonials) : [];

if (empty($items) && empty($selected_testimonials) && !empty($legacy_items)) {
  $items = $legacy_items;
}

$slides = [];
foreach ($items as $item) {
  $rating = max(1, min(5, (int) ($item['rating'] ?? 5)));
  $name = trim((string) ($item['name'] ?? ''));
  $role = trim((string) ($item['role'] ?? ''));
  $text = trim((string) ($item['text'] ?? ''));
  $photo = null;

  if (!empty($item['photo_id'])) {
    $photo = [
      'ID' => (int) $item['photo_id'],
      'alt' => (string) ($item['photo_alt'] ?? $name),
    ];
  } elseif (is_array($item['photo'] ?? null)) {
    $photo = $item['photo'];
  }

  if ($name === '' && $text === '') {
    continue;
  }

  $slides[] = [
    'rating' => $rating,
    'name' => $name,
    'role' => $role,
    'text' => $text,
    'photo' => $photo,
  ];
}

if ($title === '' && empty($slides)) {
    return;
}
?>
<section class="hj-testimonials-slider" id="<?php echo esc_attr($uid); ?>" aria-label="Testimonials slider">
  <div class="hj-ts-wrap">
    <?php if ($eyebrow !== '' || $title !== ''): ?>
      <header class="hj-ts-header">
        <?php if ($eyebrow !== ''): ?>
          <p class="hj-ts-eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>

        <?php if ($title !== ''): ?>
          <h2 class="hj-ts-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($slides)): ?>
      <div class="hj-ts-slider" data-ts-slider>
        <div class="hj-ts-stage">
          <button class="hj-ts-arrow hj-ts-arrow--prev" type="button" data-ts-prev aria-label="<?php esc_attr_e('Previous testimonials', 'hello-elementor-child'); ?>">
            <img src="<?php echo esc_url($arrow_left); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
          </button>

          <div class="hj-ts-track" data-ts-track>
            <?php foreach ($slides as $item):
              $rating = (int) $item['rating'];
              $name = $item['name'];
              $role = $item['role'];
              $text = $item['text'];
              $photo = $item['photo'];
            ?>
              <article class="hj-ts-slide" data-ts-slide>
                <div class="hj-ts-card">
                  <div class="hj-ts-stars" aria-label="<?php echo esc_attr(sprintf(__('%d out of 5 stars', 'hello-elementor-child'), $rating)); ?>">
                    <?php for ($i = 0; $i < $rating; $i++): ?>
                      <span aria-hidden="true">★</span>
                    <?php endfor; ?>
                  </div>

                  <?php if ($text !== ''): ?>
                    <div class="hj-ts-copy is-clamped" data-ts-copy><?php echo nl2br(esc_html($text)); ?></div>
                    <button class="hj-ts-read-more" type="button" data-ts-read-more hidden><?php esc_html_e('Read more', 'hello-elementor-child'); ?></button>
                  <?php endif; ?>

                  <footer class="hj-ts-footer">
                    <div class="hj-ts-person">
                      <div class="hj-ts-avatar">
                        <?php if (is_array($photo) && !empty($photo['ID'])): ?>
                          <?php echo wp_get_attachment_image((int) $photo['ID'], 'thumbnail', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                        <?php elseif (is_array($photo) && !empty($photo['url'])): ?>
                          <img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt'] ?? $name); ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                          <span class="hj-ts-avatar__fallback"><?php echo esc_html(mb_substr($name ?: 'P', 0, 1)); ?></span>
                        <?php endif; ?>
                      </div>

                      <div class="hj-ts-meta">
                        <?php if ($name !== ''): ?>
                          <div class="hj-ts-name"><?php echo esc_html($name); ?></div>
                        <?php endif; ?>
                        <?php if ($role !== ''): ?>
                          <div class="hj-ts-role"><?php echo esc_html($role); ?></div>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="hj-ts-quote" aria-hidden="true">
                      <img src="<?php echo esc_url($quote_icon); ?>" alt="" loading="lazy" decoding="async">
                    </div>
                  </footer>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <button class="hj-ts-arrow hj-ts-arrow--next" type="button" data-ts-next aria-label="<?php esc_attr_e('Next testimonials', 'hello-elementor-child'); ?>">
            <img src="<?php echo esc_url($arrow_right); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
          </button>
        </div>

        <div class="hj-ts-dots" role="tablist" aria-label="<?php esc_attr_e('Testimonials slides', 'hello-elementor-child'); ?>" data-ts-dots>
          <?php foreach ($slides as $index => $item): ?>
            <button class="hj-ts-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-ts-dot="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Testimonial %d', 'hello-elementor-child'), $index + 1)); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>