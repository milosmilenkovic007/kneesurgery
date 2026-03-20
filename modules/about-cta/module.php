<?php
if (!defined('ABSPATH')) {
    exit;
}

$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$subheading = trim((string) get_sub_field('subheading'));
$primary_button = get_sub_field('primary_button') ?: [];
$secondary_button = get_sub_field('secondary_button') ?: [];
$image = get_sub_field('image');
$bg_color = trim((string) get_sub_field('bg_color')) ?: '#f4f5ff';

$primary_url = trim((string) ($primary_button['url'] ?? ''));
$primary_title = trim((string) ($primary_button['title'] ?? ''));
$primary_target = trim((string) ($primary_button['target'] ?? ''));

$secondary_url = trim((string) ($secondary_button['url'] ?? ''));
$secondary_title = trim((string) ($secondary_button['title'] ?? ''));
$secondary_target = trim((string) ($secondary_button['target'] ?? ''));

$bg_color_clean = sanitize_hex_color($bg_color) ?: '#f4f5ff';

if ($title === '' && $subheading === '' && empty($image)) {
    return;
}
?>
<section class="hj-about-cta" style="--acta-bg: <?php echo esc_attr($bg_color_clean); ?>;" aria-label="About call to action">
  <div class="hj-acta-wrap">
    <div class="hj-acta-copy">
      <?php if ($eyebrow !== '') : ?><p class="hj-acta-eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
      <?php if ($title !== '') : ?><h2 class="hj-acta-title"><?php echo nl2br(esc_html($title)); ?></h2><?php endif; ?>
      <?php if ($subheading !== '') : ?><p class="hj-acta-subheading"><?php echo esc_html($subheading); ?></p><?php endif; ?>

      <?php if (($primary_url !== '' && $primary_title !== '') || ($secondary_url !== '' && $secondary_title !== '')) : ?>
        <div class="hj-acta-actions">
          <?php if ($primary_url !== '' && $primary_title !== '') : ?>
            <a class="hj-acta-btn hj-acta-btn--primary" href="<?php echo esc_url($primary_url); ?>"<?php echo $primary_target !== '' ? ' target="' . esc_attr($primary_target) . '" rel="noopener"' : ''; ?>>
              <?php echo esc_html($primary_title); ?>
            </a>
          <?php endif; ?>

          <?php if ($secondary_url !== '' && $secondary_title !== '') : ?>
            <a class="hj-acta-btn hj-acta-btn--secondary" href="<?php echo esc_url($secondary_url); ?>"<?php echo $secondary_target !== '' ? ' target="' . esc_attr($secondary_target) . '" rel="noopener"' : ''; ?>>
              <?php echo esc_html($secondary_title); ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="hj-acta-visual" aria-hidden="true">
      <?php if (!empty($image)) : ?>
        <?php if (!empty($image['ID'])) : ?>
          <?php echo wp_get_attachment_image((int) $image['ID'], 'large', false, ['class' => 'hj-acta-image', 'loading' => 'lazy', 'decoding' => 'async']); ?>
        <?php elseif (!empty($image['url'])) : ?>
          <img class="hj-acta-image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" decoding="async">
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>