<?php
if (!defined('ABSPATH')) exit;

$bg_color = trim((string) get_sub_field('bg_color')) ?: '#4d55dd';
$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$steps = get_sub_field('steps') ?: [];

if ($title === '' && empty($steps)) {
    return;
}

$fallback_icon_url = get_stylesheet_directory_uri() . '/assets/img/icons/hospital.svg';
?>
<section class="hj-patient-journey-steps" style="--pjs-bg: <?php echo esc_attr($bg_color); ?>;" aria-label="Patient journey steps">
  <div class="hj-pjs-wrap">
    <div class="hj-pjs-grid">
      <div class="hj-pjs-intro">
        <?php if ($eyebrow !== ''): ?>
          <div class="hj-pjs-eyebrow"><?php echo esc_html($eyebrow); ?></div>
        <?php endif; ?>

        <?php if ($title !== ''): ?>
          <h2 class="hj-pjs-title"><?php echo nl2br(esc_html($title)); ?></h2>
        <?php endif; ?>
      </div>

      <?php foreach ($steps as $index => $step):
        $step_number = trim((string) ($step['step_number'] ?? ''));
        $step_title = trim((string) ($step['title'] ?? ''));
        $description = trim((string) ($step['description'] ?? ''));
        $icon = $step['icon'] ?? null;

        if ($step_title === '' && $description === '') {
            continue;
        }

        $icon_html = '';
        if (is_array($icon) && !empty($icon['ID'])) {
            $icon_html = wp_get_attachment_image((int) $icon['ID'], 'medium', false, [
                'loading' => 'lazy',
                'decoding' => 'async',
                'class' => 'hj-pjs-card__icon-img',
            ]);
        } elseif (is_array($icon) && !empty($icon['url'])) {
            $icon_html = '<img class="hj-pjs-card__icon-img" src="' . esc_url($icon['url']) . '" alt="' . esc_attr($icon['alt'] ?? '') . '" loading="lazy" decoding="async">';
        } else {
            $icon_html = '<img class="hj-pjs-card__icon-img" src="' . esc_url($fallback_icon_url) . '" alt="" loading="lazy" decoding="async">';
        }
      ?>
        <article class="hj-pjs-card<?php echo $index === 0 ? ' is-featured' : ''; ?>">
          <div class="hj-pjs-card__inner">
            <div class="hj-pjs-card__icon" aria-hidden="true"><?php echo $icon_html; ?></div>
            <div class="hj-pjs-card__content">
              <?php if ($step_number !== '' || $step_title !== ''): ?>
                <h3 class="hj-pjs-card__title">
                  <?php if ($step_number !== ''): ?><span class="hj-pjs-card__number"><?php echo esc_html($step_number); ?></span><?php endif; ?>
                  <?php if ($step_title !== ''): ?><span class="hj-pjs-card__label"><?php echo esc_html($step_title); ?></span><?php endif; ?>
                </h3>
              <?php endif; ?>

              <?php if ($description !== ''): ?>
                <p class="hj-pjs-card__text"><?php echo nl2br(esc_html($description)); ?></p>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
