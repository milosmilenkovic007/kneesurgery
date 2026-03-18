<?php
if (!defined('ABSPATH')) exit;

$bg_color = trim((string) get_sub_field('bg_color')) ?: '#4d55dd';
$items = get_sub_field('items') ?: [];

if (empty($items) || !is_array($items)) {
    return;
}
?>
<section class="hj-trust-highlights" style="--th-bg: <?php echo esc_attr($bg_color); ?>;" aria-label="Trust highlights">
  <div class="hj-th-wrap">
    <div class="hj-th-grid">
      <?php foreach ($items as $item):
        $icon = $item['icon'] ?? null;
        $title = trim((string) ($item['title'] ?? ''));
        $description = trim((string) ($item['description'] ?? ''));

        if (!$icon && $title === '' && $description === '') {
            continue;
        }
      ?>
        <article class="hj-th-item">
          <?php if (!empty($icon)): ?>
            <div class="hj-th-item__icon-wrap" aria-hidden="true">
              <span class="hj-th-item__icon">
                <?php if (!empty($icon['ID'])): ?>
                  <?php echo wp_get_attachment_image((int) $icon['ID'], 'medium', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                <?php elseif (!empty($icon['url'])): ?>
                  <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($icon['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                <?php endif; ?>
              </span>
            </div>
          <?php endif; ?>

          <div class="hj-th-item__content">
            <?php if ($title !== ''): ?>
              <h3 class="hj-th-item__title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>

            <?php if ($description !== ''): ?>
              <p class="hj-th-item__description"><?php echo nl2br(esc_html($description)); ?></p>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>