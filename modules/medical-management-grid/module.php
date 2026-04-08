<?php
if (!defined('ABSPATH')) {
    exit;
}

$anchor_id = trim((string) get_sub_field('anchor_id'));
$title = trim((string) get_sub_field('title'));
$title_accent = trim((string) get_sub_field('title_accent'));
$title_suffix = trim((string) get_sub_field('title_suffix'));
$intro = trim((string) get_sub_field('intro'));
$items = get_sub_field('items') ?: [];

$default_config = function_exists('hj_get_medical_management_grid_defaults') ? hj_get_medical_management_grid_defaults() : [];
$default_items = $default_config['items'] ?? [];

$icon_map = [];

foreach ($default_items as $index => $default_item) {
  $icon_path = trim((string) ($default_item['icon_path'] ?? ''));
  $icon_key = trim((string) ($default_item['icon_key'] ?? ''));

  if ($icon_path === '') {
    continue;
  }

  $icon_data = [
    'url' => trailingslashit(get_stylesheet_directory_uri()) . ltrim($icon_path, '/'),
    'alt' => trim((string) ($default_item['title'] ?? '')),
  ];

  $default_items[$index]['icon'] = $icon_data;

  if ($icon_key !== '') {
    $icon_map[$icon_key] = $icon_data;
  }
}

$anchor_id = ltrim($anchor_id, '#');
$anchor_id = $anchor_id !== '' ? sanitize_html_class($anchor_id) : '';

$title = $title !== '' ? $title : trim((string) ($default_config['title'] ?? 'What'));
$title_accent = $title_accent !== '' ? $title_accent : trim((string) ($default_config['title_accent'] ?? 'Medical Management & Coordination'));
$title_suffix = $title_suffix !== '' ? $title_suffix : trim((string) ($default_config['title_suffix'] ?? 'Mean To You'));
$intro = $intro !== '' ? $intro : trim((string) ($default_config['intro'] ?? 'Medical treatment abroad is not like treatment at home. Different systems. Different languages. Different levels of follow-up. We are here to fill the gaps, reduce risks, and protect patients at every stage.'));

if (empty($items)) {
  $items = $default_items;
}

if ($title === '' && $title_accent === '' && $title_suffix === '' && $intro === '' && empty($items)) {
    return;
}
?>
<section class="hj-medical-management-grid"<?php echo $anchor_id !== '' ? ' id="' . esc_attr($anchor_id) . '"' : ''; ?> aria-label="Medical management grid">
  <div class="hj-mmg-wrap">
    <?php if ($title !== '' || $title_accent !== '' || $title_suffix !== '' || $intro !== '') : ?>
      <header class="hj-mmg-head">
        <?php if ($title !== '' || $title_accent !== '' || $title_suffix !== '') : ?>
          <h2 class="hj-mmg-title">
            <span class="hj-mmg-title__mark" aria-hidden="true"></span>
            <span class="hj-mmg-title__text">
              <?php if ($title !== '') : ?><span class="hj-mmg-title__main"><?php echo esc_html($title); ?></span><?php endif; ?>
              <?php if ($title_accent !== '') : ?> <span class="hj-mmg-title__accent"><?php echo esc_html($title_accent); ?></span><?php endif; ?>
              <?php if ($title_suffix !== '') : ?> <span class="hj-mmg-title__suffix"><?php echo esc_html($title_suffix); ?></span><?php endif; ?>
            </span>
          </h2>
        <?php endif; ?>

        <?php if ($intro !== '') : ?>
          <p class="hj-mmg-intro"><?php echo nl2br(esc_html($intro)); ?></p>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($items)) : ?>
      <div class="hj-mmg-grid">
      <?php foreach ($items as $index => $item) :
        $default_item = $default_items[$index] ?? [];
            $icon = $item['icon'] ?? null;
        $icon_key = trim((string) ($item['icon_key'] ?? ''));
            $card_title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));

        if (empty($icon) && $icon_key !== '' && !empty($icon_map[$icon_key])) {
          $icon = $icon_map[$icon_key];
        }

        if (empty($icon) && !empty($default_item['icon'])) {
          $icon = $default_item['icon'];
        }

        if ($card_title === '' && !empty($default_item['title'])) {
          $card_title = trim((string) $default_item['title']);
        }

        if ($description === '' && !empty($default_item['description'])) {
          $description = trim((string) $default_item['description']);
        }

            if ($card_title === '' && $description === '' && empty($icon)) {
                continue;
            }
        ?>
          <article class="hj-mmg-card">
            <div class="hj-mmg-card__head">
              <?php if (!empty($icon)) : ?>
                <span class="hj-mmg-card__icon" aria-hidden="true">
                  <?php if (!empty($icon['ID'])) : ?>
                    <?php echo wp_get_attachment_image((int) $icon['ID'], 'thumbnail', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                  <?php elseif (!empty($icon['url'])) : ?>
                    <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($icon['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                  <?php endif; ?>
                </span>
              <?php endif; ?>

              <?php if ($card_title !== '') : ?><h3 class="hj-mmg-card__title"><?php echo nl2br(esc_html($card_title)); ?></h3><?php endif; ?>
            </div>

            <?php if ($description !== '') : ?>
              <p class="hj-mmg-card__description"><?php echo nl2br(esc_html($description)); ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>