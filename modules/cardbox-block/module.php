<?php
$title = get_sub_field('title');
$subheading = get_sub_field('subheading');
$subtitle = get_sub_field('subtitle');
$has_subtitle = (bool) trim((string) $subtitle);
$left_align = (bool) get_sub_field('left_align');
$columns = get_sub_field('columns') ?: '2';
$items = get_sub_field('items') ?: [];
$uid = uniqid('hj-fb-');
$columns_class = $columns === '3' ? ' is-cols-3' : ' is-cols-2';
?>
<section class="hj-cardbox<?php echo $left_align ? ' is-left' : ''; ?><?php echo $has_subtitle ? ' has-subtitle' : ''; ?><?php echo esc_attr($columns_class); ?>" id="<?php echo esc_attr($uid); ?>" aria-label="Cardbox Block">
  <div class="hj-cb-wrap">
    <?php if ($title || $subheading): ?>
      <div class="hj-cb-header">
        <?php if ($title): ?>
          <h2 class="hj-cb-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if ($subheading): ?>
          <p class="hj-cb-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($subtitle): ?>
      <p class="hj-cb-subtitle"><?php echo esc_html($subtitle); ?></p>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
      <div class="hj-cb-grid">
        <?php foreach ($items as $item):
          $item_title = trim((string) ($item['title'] ?? ''));
          $item_text = trim((string) ($item['text'] ?? ''));
          if (!$item_title && !$item_text) { continue; }
          $icon = $item['icon'] ?? null;
          $icon_url = is_array($icon) ? ($icon['url'] ?? '') : '';
          $icon_alt = is_array($icon) ? ($icon['alt'] ?? '') : '';
          if (!$icon_alt && is_array($icon)) { $icon_alt = $icon['title'] ?? ''; }
        ?>
          <div class="hj-cb-card">
            <div class="hj-cb-card-head">
              <?php if ($icon_url): ?>
                <span class="hj-cb-icon" aria-hidden="true">
                  <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>" />
                </span>
              <?php endif; ?>
              <?php if ($item_title): ?>
                <h3 class="hj-cb-card-title"><?php echo esc_html($item_title); ?></h3>
              <?php endif; ?>
            </div>
            <?php if ($item_text): ?>
              <p class="hj-cb-card-text"><?php echo esc_html($item_text); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
