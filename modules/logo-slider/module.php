<?php
$prefix = trim((string) get_sub_field('title_prefix'));
$accent = trim((string) get_sub_field('title_accent'));
$logos  = get_sub_field('logos') ?: [];
$uid = uniqid('hj-ls-');

// Normalize logos to array: [ url, alt, href, maxw ]
$items = [];
foreach ($logos as $logo) {
    $img = $logo['image'] ?? null;
    if (!$img) { continue; }
    $url = ''; $alt = '';
    if (is_array($img)) { $url = $img['url'] ?? ''; $alt = $img['alt'] ?? ''; }
    elseif (is_numeric($img)) {
        $u = wp_get_attachment_image_src((int)$img, 'full'); if ($u) { $url = $u[0]; }
        $alt = get_post_meta((int)$img, '_wp_attachment_image_alt', true);
    } elseif (is_string($img)) { $url = $img; }
    if (!$url) { continue; }
    $items[] = [
        'url' => $url,
        'alt' => ($logo['alt'] ?? '') ?: $alt,
        'href' => $logo['url'] ?? '',
        'maxw' => isset($logo['max_width']) ? (int)$logo['max_width'] : 0,
    ];
}
?>

<section class="hj-logo-slider" id="<?php echo esc_attr($uid); ?>" aria-label="Logo Slider">
  <div class="hj-ls-wrap">
    <div class="hj-ls-head">
      <?php if ($prefix || $accent): ?>
        <h2 class="hj-cb-title">
          <?php if ($prefix): ?><span class="muted"><?php echo esc_html($prefix); ?></span> <?php endif; ?>
          <?php if ($accent): ?><span class="accent-italic"><?php echo esc_html($accent); ?></span><?php endif; ?>
        </h2>
      <?php endif; ?>
    </div>
    <div class="hj-ls-divider" aria-hidden="true"></div>

    <?php if (!empty($items)): ?>
    <div class="hj-ls-viewport">
      <div class="hj-ls-marquee" aria-hidden="false">
        <ul class="hj-ls-seq" role="list">
          <?php foreach ($items as $it): ?>
            <li class="hj-ls-item" role="listitem">
              <?php if (!empty($it['href'])): ?><a href="<?php echo esc_url($it['href']); ?>" target="_blank" rel="noopener">
                <img src="<?php echo esc_url($it['url']); ?>" alt="<?php echo esc_attr($it['alt']); ?>" draggable="false" />
              </a><?php else: ?>
                <img src="<?php echo esc_url($it['url']); ?>" alt="<?php echo esc_attr($it['alt']); ?>" draggable="false" />
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <ul class="hj-ls-seq" role="list" aria-hidden="true">
          <?php foreach ($items as $it): ?>
            <li class="hj-ls-item" role="listitem">
              <?php if (!empty($it['href'])): ?><a href="<?php echo esc_url($it['href']); ?>" target="_blank" rel="noopener">
                <img src="<?php echo esc_url($it['url']); ?>" alt="" aria-hidden="true" draggable="false" />
              </a><?php else: ?>
                <img src="<?php echo esc_url($it['url']); ?>" alt="" aria-hidden="true" draggable="false" />
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
