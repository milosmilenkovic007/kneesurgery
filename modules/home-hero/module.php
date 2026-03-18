<?php
if (!defined('ABSPATH')) exit;

$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$intro = trim((string) get_sub_field('intro'));
$primary_button = get_sub_field('primary_button') ?: [];
$secondary_button = get_sub_field('secondary_button') ?: [];
$doctor_images = get_sub_field('doctor_images') ?: [];
$counters = get_sub_field('counters') ?: [];
$review_card = get_sub_field('review_card') ?: [];

$primary_href = trim((string) ($primary_button['url'] ?? ''));
$secondary_href = trim((string) ($secondary_button['url'] ?? ''));
$primary_is_candidate = $primary_href && strpos($primary_href, '#candidate') !== false;
$review_avatars = !empty($review_card['avatars']) && is_array($review_card['avatars']) ? $review_card['avatars'] : [];
$review_stars = (float) ($review_card['stars'] ?? 0);
$review_text = trim((string) ($review_card['text'] ?? ''));

$slides = [];
foreach ($doctor_images as $image_row) {
  $image = $image_row['image'] ?? null;
  if (!empty($image)) {
    $slides[] = $image;
  }
}

$title_html = $title !== '' ? nl2br(esc_html($title)) : '';
$intro_html = $intro !== '' ? nl2br(esc_html($intro)) : '';

$stars_output = '';
if ($review_stars > 0) {
    $full_stars = (int) floor($review_stars);
    $stars_output = str_repeat('★', max(0, min(5, $full_stars)));
}

$parse_counter = static function ($value) {
  $value = trim((string) $value);
  if ($value === '') {
    return [
      'raw' => '',
      'target' => '',
      'prefix' => '',
      'suffix' => '',
    ];
  }

  if (!preg_match('/^([^\d]*)(\d+(?:\.\d+)?)(.*)$/', $value, $matches)) {
    return [
      'raw' => $value,
      'target' => '',
      'prefix' => '',
      'suffix' => '',
    ];
  }

  return [
    'raw' => $value,
    'target' => $matches[2],
    'prefix' => trim((string) $matches[1]),
    'suffix' => trim((string) $matches[3]),
  ];
};
?>
<section class="hj-home-hero" aria-label="Home hero">
  <div class="hj-hh-wrap">
    <div class="hj-hh-copy">
      <?php if ($eyebrow !== ''): ?>
        <p class="hj-hh-eyebrow"><?php echo esc_html($eyebrow); ?></p>
      <?php endif; ?>

      <?php if ($title_html !== ''): ?>
        <h1 class="hj-hh-title"><?php echo $title_html; ?></h1>
      <?php endif; ?>

      <?php if ($intro_html !== ''): ?>
        <p class="hj-hh-intro"><?php echo $intro_html; ?></p>
      <?php endif; ?>

      <?php if (!empty($primary_button['label']) || !empty($secondary_button['label'])): ?>
        <div class="hj-hh-actions">
          <?php if (!empty($primary_button['label']) && $primary_href !== ''): ?>
            <a class="hj-hh-btn hj-hh-btn--primary" href="<?php echo esc_attr($primary_href); ?>"<?php if ($primary_is_candidate) echo ' data-candidate="1"'; ?>>
              <?php echo esc_html($primary_button['label']); ?>
            </a>
          <?php endif; ?>

          <?php if (!empty($secondary_button['label']) && $secondary_href !== ''): ?>
            <a class="hj-hh-btn hj-hh-btn--secondary" href="<?php echo esc_attr($secondary_href); ?>">
              <?php echo esc_html($secondary_button['label']); ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="hj-hh-visual">
      <div class="hj-hh-doctor"<?php echo count($slides) > 1 ? ' data-hh-slider' : ''; ?>>
        <?php foreach ($slides as $index => $slide): ?>
          <?php
            $slide_class = 'hj-hh-doctor__img' . ($index === 0 ? ' is-active' : '');
            $attrs = [
                'class' => $slide_class,
                'loading' => $index === 0 ? 'eager' : 'lazy',
                'decoding' => 'async',
                'data-hh-slide' => (string) $index,
            ];
          ?>
          <?php if (!empty($slide['ID'])): ?>
            <?php echo wp_get_attachment_image((int) $slide['ID'], 'large', false, $attrs); ?>
          <?php elseif (!empty($slide['url'])): ?>
            <img class="<?php echo esc_attr($slide_class); ?>" data-hh-slide="<?php echo esc_attr((string) $index); ?>" src="<?php echo esc_url($slide['url']); ?>" alt="<?php echo esc_attr($slide['alt'] ?? ''); ?>" loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
          <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($review_avatars) || $stars_output !== '' || $review_text !== ''): ?>
          <div class="hj-hh-review-card">
            <?php if (!empty($review_avatars)): ?>
              <div class="hj-hh-review-card__avatars">
                <?php foreach ($review_avatars as $avatar_row):
                  $avatar = $avatar_row['image'] ?? null;
                  if (empty($avatar)) {
                      continue;
                  }
                ?>
                  <span class="hj-hh-review-card__avatar">
                    <?php if (!empty($avatar['ID'])): ?>
                      <?php echo wp_get_attachment_image((int) $avatar['ID'], 'thumbnail', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                    <?php elseif (!empty($avatar['url'])): ?>
                      <img src="<?php echo esc_url($avatar['url']); ?>" alt="<?php echo esc_attr($avatar['alt'] ?? ''); ?>">
                    <?php endif; ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="hj-hh-review-card__body">
              <?php if ($stars_output !== ''): ?>
                <div class="hj-hh-review-card__stars" aria-label="<?php echo esc_attr($review_stars); ?> out of 5 stars"><?php echo esc_html($stars_output); ?></div>
              <?php endif; ?>
              <?php if ($review_text !== ''): ?>
                <div class="hj-hh-review-card__text"><?php echo esc_html($review_text); ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($counters)): ?>
        <div class="hj-hh-stats">
          <?php foreach ($counters as $counter):
            $counter_value = trim((string) ($counter['value'] ?? ''));
            $counter_label = trim((string) ($counter['label'] ?? ''));
            if ($counter_value === '' && $counter_label === '') {
                continue;
            }
            $parsed_counter = $parse_counter($counter_value);
          ?>
            <div class="hj-hh-stat-card">
              <?php if ($counter_value !== ''): ?>
                <div class="hj-hh-stat-card__value">
                  <?php if ($parsed_counter['prefix'] !== ''): ?><span class="hj-hh-stat-card__prefix"><?php echo esc_html($parsed_counter['prefix']); ?></span><?php endif; ?>
                  <?php if ($parsed_counter['target'] !== ''): ?>
                    <span class="hj-hh-stat-card__count" data-hh-count-to="<?php echo esc_attr($parsed_counter['target']); ?>">0</span>
                  <?php else: ?>
                    <span class="hj-hh-stat-card__count is-static"><?php echo esc_html($parsed_counter['raw']); ?></span>
                  <?php endif; ?>
                  <?php if ($parsed_counter['suffix'] !== ''): ?><span class="hj-hh-stat-card__suffix"><?php echo esc_html($parsed_counter['suffix']); ?></span><?php endif; ?>
                </div>
              <?php endif; ?>
              <?php if ($counter_label !== ''): ?>
                <div class="hj-hh-stat-card__label"><?php echo nl2br(esc_html($counter_label)); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>