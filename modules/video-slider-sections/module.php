<?php
$videos = get_sub_field('videos') ?: [];
$sections = get_sub_field('sections') ?: [];
$uid = uniqid('hj-vss-');

if (!wp_script_is('hj-video-slider-sections', 'enqueued')) {
  wp_enqueue_script(
    'hj-video-slider-sections',
    get_stylesheet_directory_uri() . '/assets/js/video-slider-sections.js',
    [],
    wp_get_theme()->get('Version'),
    true
  );
}

function hj_vss_is_media_url($url) {
  $path = (string) wp_parse_url($url, PHP_URL_PATH);
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  return in_array($ext, ['mp4', 'webm', 'ogg'], true);
}

function hj_vss_poster_url($poster) {
  if (is_array($poster) && !empty($poster['url'])) {
    return $poster['url'];
  }
  if (is_numeric($poster)) {
    $u = wp_get_attachment_image_src((int) $poster, 'large');
    return $u ? $u[0] : '';
  }
  return '';
}

// Normalize videos to a clean sequential list (so dots match slides)
$items = [];
foreach ($videos as $row) {
  $url = trim((string) ($row['url'] ?? ''));
  if (!$url) { continue; }
  $items[] = [
    'url' => $url,
    'poster' => hj_vss_poster_url($row['poster'] ?? null),
  ];
}
?>

<section class="hj-vss" id="<?php echo esc_attr($uid); ?>" aria-label="Video Slider + Sections">
  <div class="hj-vss-wrap">

    <div class="hj-vss-left">
      <?php if (!empty($items)): ?>
        <div class="hj-vss-slider" data-vss-slider>
          <div class="hj-vss-track" data-vss-track>
            <?php foreach ($items as $i => $row):
              $url = $row['url'];
              $poster = $row['poster'];
              $is_media = hj_vss_is_media_url($url);
              $embed = '';
              if (!$is_media) {
                $embed = wp_oembed_get($url);
                if (!$embed) {
                  $embed = sprintf(
                    '<iframe src="%s" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="%s"></iframe>',
                    esc_url($url),
                    esc_attr__('Video', 'hello-elementor-child')
                  );
                }
              }
            ?>
              <div class="hj-vss-slide" data-vss-slide>
                <div class="hj-vss-card">
                  <?php if ($is_media): ?>
                    <video class="hj-vss-video" controls playsinline preload="metadata" <?php if($poster) echo 'poster="' . esc_url($poster) . '"'; ?>>
                      <source src="<?php echo esc_url($url); ?>" />
                    </video>
                  <?php else: ?>
                    <div class="hj-vss-embed">
                      <?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                  <?php endif; ?>

                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="hj-vss-dots" role="tablist" aria-label="<?php esc_attr_e('Video slides', 'hello-elementor-child'); ?>" data-vss-dots>
            <?php foreach ($items as $i => $row): ?>
              <button class="hj-vss-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button" aria-label="<?php echo esc_attr(sprintf(__('Slide %d', 'hello-elementor-child'), $i + 1)); ?>" data-vss-dot="<?php echo esc_attr($i); ?>" aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="hj-vss-right" data-vss-right>
      <?php foreach ($sections as $s):
        $heading = trim((string) ($s['heading'] ?? ''));
        $section_type = $s['section_type'] ?? 'content';
        $subheading = trim((string) ($s['subheading'] ?? ''));
        $content = $s['content'] ?? '';
        $btn = $s['button'] ?? null;
        $included_title = trim((string) ($s['included_title'] ?? ''));
        $included_items = $s['included_items'] ?? [];
        $price = trim((string) ($s['price'] ?? ''));
        $price_note = trim((string) ($s['price_note'] ?? ''));
        $price_btn = $s['price_button'] ?? null;
        $rating = $s['rating'] ?? [];
        $has_content_block = ($section_type !== 'price') && ($subheading || $content || !empty($btn));
        $has_price_block = ($section_type === 'price') && ($included_title || !empty($included_items) || $price || $price_note || !empty($price_btn) || !empty($rating));
        if (!$heading && !$has_content_block && !$has_price_block) continue;
        $btn_url = is_array($btn) ? ($btn['url'] ?? '') : '';
        $btn_title = is_array($btn) ? ($btn['title'] ?? '') : '';
        $btn_target = is_array($btn) ? ($btn['target'] ?? '') : '';
        $price_btn_url = is_array($price_btn) ? ($price_btn['url'] ?? '') : '';
        $price_btn_title = is_array($price_btn) ? ($price_btn['title'] ?? '') : '';
        $price_btn_target = is_array($price_btn) ? ($price_btn['target'] ?? '') : '';
        $rating_label = trim((string) ($rating['label'] ?? ''));
        $rating_reviews_count = (int) ($rating['reviews_count'] ?? 0);
        $rating_reviews_url = trim((string) ($rating['reviews_url'] ?? ''));
      ?>
        <div class="hj-vss-section">
          <?php if ($heading): ?>
            <h2 class="hj-cb-title"><span class="hj-vss-accent" aria-hidden="true"></span><?php echo esc_html($heading); ?></h2>
          <?php endif; ?>

          <?php if ($section_type === 'price'): ?>
            <?php if ($included_title): ?>
              <p class="hj-vss-included-title"><?php echo esc_html($included_title); ?></p>
            <?php endif; ?>

            <?php if (!empty($included_items)): ?>
              <ul class="hj-vss-included-list" role="list">
                <?php foreach ($included_items as $item):
                  $item_text = is_array($item) ? ($item['text'] ?? '') : $item;
                  $item_text = trim((string) $item_text);
                  if (!$item_text) continue;
                ?>
                  <li class="hj-vss-included-item">
                    <span class="ic" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"/>
                        <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </span>
                    <span class="tx"><?php echo esc_html($item_text); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if ($price || $price_note): ?>
              <div class="hj-vss-price-row">
                <?php if ($price): ?><span class="hj-vss-price"><?php echo esc_html($price); ?></span><?php endif; ?>
                <?php if ($price_note): ?><span class="hj-vss-price-note"><?php echo esc_html($price_note); ?></span><?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if (($price_btn_url && $price_btn_title) || ($rating_label || $rating_reviews_count)): ?>
              <div class="hj-vss-cta-row">
                <?php if ($price_btn_url && $price_btn_title): ?>
                  <div class="hj-vss-actions">
                    <a class="hj-vss-btn" href="<?php echo esc_url($price_btn_url); ?>"<?php echo $price_btn_target ? ' target="' . esc_attr($price_btn_target) . '" rel="noopener"' : ''; ?>>
                      <?php echo esc_html($price_btn_title); ?>
                    </a>
                  </div>
                <?php endif; ?>

                <?php if ($rating_label || $rating_reviews_count): ?>
                  <div class="hj-vss-rating">
                    <div class="row">
                      <span class="stars" aria-hidden="true">★★★★★</span>
                      <?php if ($rating_label): ?><span class="label"><?php echo esc_html($rating_label); ?></span><?php endif; ?>
                    </div>
                    <?php if ($rating_reviews_count): ?>
                      <div class="sub">
                        <span class="prefix"><?php esc_html_e('Based on', 'hello-elementor-child'); ?></span>
                        <?php if ($rating_reviews_url): ?>
                          <a href="<?php echo esc_url($rating_reviews_url); ?>"><?php echo intval($rating_reviews_count); ?> reviews</a>
                        <?php else: ?>
                          <span><?php echo intval($rating_reviews_count); ?> reviews</span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <?php if ($subheading): ?>
              <p class="hj-vss-subheading"><?php echo esc_html($subheading); ?></p>
            <?php endif; ?>

            <?php if (!empty($content)): ?>
              <div class="hj-vss-content">
                <?php echo wp_kses_post($content); ?>
              </div>
            <?php endif; ?>

            <?php if ($btn_url && $btn_title): ?>
              <div class="hj-vss-actions">
                <a class="hj-vss-btn" href="<?php echo esc_url($btn_url); ?>"<?php echo $btn_target ? ' target="' . esc_attr($btn_target) . '" rel="noopener"' : ''; ?>>
                  <?php echo esc_html($btn_title); ?>
                </a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
