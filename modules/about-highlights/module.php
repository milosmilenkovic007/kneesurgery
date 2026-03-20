<?php
if (!defined('ABSPATH')) {
    exit;
}

$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$items = get_sub_field('items') ?: [];

if ($title === '' && empty($items)) {
    return;
}
?>
<section class="hj-about-highlights" aria-label="About highlights">
  <div class="hj-ah-wrap">
    <?php if ($eyebrow !== '' || $title !== '') : ?>
      <header class="hj-ah-head">
        <?php if ($eyebrow !== '') : ?><p class="hj-ah-eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
        <?php if ($title !== '') : ?><h2 class="hj-ah-title"><?php echo esc_html($title); ?></h2><?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($items)) : ?>
      <div class="hj-ah-grid">
        <?php foreach ($items as $item) :
            $icon = $item['icon'] ?? null;
            $item_title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));

            if (!$icon && $item_title === '' && $description === '') {
                continue;
            }
        ?>
          <article class="hj-ah-card">
            <div class="hj-ah-card__head">
              <?php if (!empty($icon)) : ?>
                <span class="hj-ah-card__icon" aria-hidden="true">
                  <?php if (!empty($icon['ID'])) : ?>
                    <?php echo wp_get_attachment_image((int) $icon['ID'], 'medium', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                  <?php elseif (!empty($icon['url'])) : ?>
                    <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($icon['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                  <?php endif; ?>
                </span>
              <?php endif; ?>

              <?php if ($item_title !== '') : ?><h3 class="hj-ah-card__title"><?php echo esc_html($item_title); ?></h3><?php endif; ?>
            </div>

            <?php if ($description !== '') : ?>
              <div class="hj-ah-card__description"><?php echo nl2br(esc_html($description)); ?></div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>