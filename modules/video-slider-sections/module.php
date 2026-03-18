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

$vss_arrow_left = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-left.svg';
$vss_arrow_right = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-right.svg';

$vss_is_media_url = static function ($url) {
  $path = (string) wp_parse_url($url, PHP_URL_PATH);
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  return in_array($ext, ['mp4', 'webm', 'ogg'], true);
};

$vss_get_embed_url = static function ($url) {
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([^&?/]+)~i', $url, $matches)) {
    return 'https://www.youtube.com/embed/' . rawurlencode($matches[1]) . '?autoplay=1&rel=0';
  }

  if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $matches)) {
    return 'https://player.vimeo.com/video/' . rawurlencode($matches[1]) . '?autoplay=1';
  }

  return '';
};

$vss_poster_url = static function ($poster) {
  if (is_array($poster) && !empty($poster['url'])) {
    return $poster['url'];
  }
  if (is_numeric($poster)) {
    $u = wp_get_attachment_image_src((int) $poster, 'large');
    return $u ? $u[0] : '';
  }
  return '';
};

$vss_split_content_tabs = static function ($content) {
  $content = is_string($content) ? trim($content) : '';
  if ($content === '') {
    return [];
  }

  $items = [];
  $current_title = '';
  $current_body = '';

  $prev = libxml_use_internal_errors(true);
  $doc = new DOMDocument('1.0', 'UTF-8');
  $html = '<div>' . $content . '</div>';
  $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
  libxml_clear_errors();
  libxml_use_internal_errors($prev);

  $root = $doc->getElementsByTagName('div')->item(0);
  if (!$root) {
    return [[ 'title' => '', 'body' => $content ]];
  }

  foreach ($root->childNodes as $node) {
    if ($node->nodeType === XML_ELEMENT_NODE) {
      $tag = strtolower($node->nodeName);
      if (in_array($tag, ['h2', 'h3', 'h4'], true)) {
        if ($current_title !== '' || trim($current_body) !== '') {
          $items[] = [
            'title' => $current_title,
            'body' => $current_body,
          ];
        }
        $current_title = trim($node->textContent);
        $current_body = '';
        continue;
      }
    }

    $current_body .= $doc->saveHTML($node);
  }

  if ($current_title !== '' || trim($current_body) !== '') {
    $items[] = [
      'title' => $current_title,
      'body' => $current_body,
    ];
  }

  return $items;
};

$vss_render_section_body = static function ($section, $enable_accordion) use ($vss_split_content_tabs) {
  $section_type = $section['section_type'] ?? 'content';
  $subheading = trim((string) ($section['subheading'] ?? ''));
  $content = $section['content'] ?? '';
  $btn = $section['button'] ?? null;
  $price_btn = $section['price_button'] ?? null;
  $rating = $section['rating'] ?? [];
  $btn_url = is_array($btn) ? ($btn['url'] ?? '') : '';
  $btn_title = is_array($btn) ? ($btn['title'] ?? '') : '';
  $btn_target = is_array($btn) ? ($btn['target'] ?? '') : '';
  $price_btn_url = is_array($price_btn) ? ($price_btn['url'] ?? '') : '';
  $price_btn_title = is_array($price_btn) ? trim((string) ($price_btn['title'] ?? '')) : '';
  $price_btn_target = is_array($price_btn) ? ($price_btn['target'] ?? '') : '';
  $rating_stars = max(0, min(5, (float) ($rating['stars'] ?? 0)));
  $rating_label = trim((string) ($rating['label'] ?? ''));
  $rating_reviews_count = (int) ($rating['reviews_count'] ?? 0);
  $rating_reviews_url = trim((string) ($rating['reviews_url'] ?? ''));

  ob_start();

  if ($section_type === 'price') {
    $price_cta_label = $price_btn_title;
    $has_rating = $rating_stars > 0 || $rating_label !== '' || $rating_reviews_count > 0;
    $rating_stars_text = $rating_stars > 0 ? str_repeat('★', (int) round($rating_stars)) : '';

    if ($price_cta_label === '') {
      $price_cta_label = __('View Surgery Pricing', 'hello-elementor-child');
    }
    ?>
    <?php if ($price_btn_url || $has_rating): ?>
      <div class="hj-vss-cta-row hj-vss-cta-row--price-only">
        <?php if ($price_btn_url): ?>
          <div class="hj-vss-actions hj-vss-actions--price-only">
            <a class="hj-vss-btn" href="<?php echo esc_url($price_btn_url); ?>"<?php echo $price_btn_target ? ' target="' . esc_attr($price_btn_target) . '" rel="noopener"' : ''; ?>>
              <?php echo esc_html($price_cta_label); ?>
            </a>
          </div>
        <?php endif; ?>

        <?php if ($has_rating): ?>
          <div class="hj-vss-rating hj-vss-rating--inline">
            <div class="row">
              <?php if ($rating_stars_text !== ''): ?><span class="stars" aria-hidden="true"><?php echo esc_html($rating_stars_text); ?></span><?php endif; ?>
              <?php if ($rating_label !== ''): ?><span class="label"><?php echo esc_html($rating_label); ?></span><?php endif; ?>
            </div>
            <?php if ($rating_reviews_count > 0): ?>
              <div class="sub">
                <?php if ($rating_reviews_url !== ''): ?>
                  <a href="<?php echo esc_url($rating_reviews_url); ?>"><?php echo esc_html(sprintf(_n('%d review', '%d reviews', $rating_reviews_count, 'hello-elementor-child'), $rating_reviews_count)); ?></a>
                <?php else: ?>
                  <span><?php echo esc_html(sprintf(_n('%d review', '%d reviews', $rating_reviews_count, 'hello-elementor-child'), $rating_reviews_count)); ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php
  } else {
    if ($subheading) {
      echo '<p class="hj-vss-subheading">' . esc_html($subheading) . '</p>';
    }

    if (!empty($content)) {
      if ($enable_accordion) {
        $accordion_items = $vss_split_content_tabs($content);
        $first_titled_index = null;
        foreach ($accordion_items as $index => $it) {
          if (!empty($it['title'])) {
            $first_titled_index = $index;
            break;
          }
        }

        if ($first_titled_index !== null && empty($accordion_items[0]['title']) && trim((string) $accordion_items[0]['body']) !== '') {
          $accordion_items[$first_titled_index]['body'] = $accordion_items[0]['body'] . $accordion_items[$first_titled_index]['body'];
          array_shift($accordion_items);
        }

        if (empty($accordion_items)) {
          $accordion_items = [[
            'title' => $subheading ?: __('Details', 'hello-elementor-child'),
            'body' => $content,
          ]];
        }

        foreach ($accordion_items as $index => $it) {
          if (empty($it['title'])) {
            $accordion_items[$index]['title'] = $subheading ?: __('Details', 'hello-elementor-child');
          }
        }
        ?>
        <ul class="hj-pa-list hj-vss-accordion" role="list">
          <?php foreach ($accordion_items as $it): ?>
            <li class="hj-pa-item">
              <details>
                <summary>
                  <span class="ind" aria-hidden="true"></span>
                  <span class="t"><?php echo esc_html($it['title']); ?></span>
                  <span class="dots" aria-hidden="true"></span>
                </summary>
                <div class="desc">
                  <?php echo wp_kses_post($it['body']); ?>
                </div>
              </details>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php
      } else {
        echo '<div class="hj-vss-content">' . wp_kses_post($content) . '</div>';
      }
    }

    if ($btn_url && $btn_title) {
      echo '<div class="hj-vss-actions"><a class="hj-vss-btn" href="' . esc_url($btn_url) . '"' . ($btn_target ? ' target="' . esc_attr($btn_target) . '" rel="noopener"' : '') . '>' . esc_html($btn_title) . '</a></div>';
    }
  }

  return ob_get_clean();
};

// Normalize videos to a clean sequential list (so dots match slides)
$items = [];
foreach ($videos as $row) {
  $url = trim((string) ($row['url'] ?? ''));
  if (!$url) { continue; }
  $video_type = $vss_is_media_url($url) ? 'file' : 'embed';
  $items[] = [
    'url' => $url,
    'poster' => $vss_poster_url($row['poster'] ?? null),
    'caption' => trim((string) ($row['caption'] ?? '')),
    'type' => $video_type,
    'src' => $video_type === 'file' ? $url : $vss_get_embed_url($url),
  ];
}
?>

<section class="hj-vss" id="<?php echo esc_attr($uid); ?>" aria-label="Video Slider + Sections">
  <div class="hj-vss-wrap">

    <div class="hj-vss-left">
      <?php if (!empty($items)): ?>
        <div class="hj-vss-slider" data-vss-slider>
          <div class="hj-vss-stage">
            <button class="hj-vss-arrow hj-vss-arrow--prev" type="button" data-vss-prev aria-label="<?php esc_attr_e('Previous slide', 'hello-elementor-child'); ?>">
              <img src="<?php echo esc_url($vss_arrow_left); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
            </button>

            <div class="hj-vss-track" data-vss-track>
            <?php foreach ($items as $i => $row):
              $poster = $row['poster'];
            ?>
              <div class="hj-vss-slide" data-vss-slide>
                <button
                  class="hj-vss-card hj-vss-card--poster"
                  type="button"
                  data-vss-open
                  data-video-type="<?php echo esc_attr($row['type']); ?>"
                  data-video-src="<?php echo esc_url($row['src']); ?>"
                  aria-label="<?php esc_attr_e('Open video', 'hello-elementor-child'); ?>"
                >
                  <?php if ($poster): ?>
                    <img class="hj-vss-poster" src="<?php echo esc_url($poster); ?>" alt="<?php echo esc_attr($row['caption']); ?>" loading="lazy" decoding="async">
                  <?php else: ?>
                    <span class="hj-vss-poster hj-vss-poster--fallback"></span>
                  <?php endif; ?>
                  <span class="hj-vss-play" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9 7.75V16.25L16 12L9 7.75Z" fill="currentColor"/>
                    </svg>
                  </span>
                </button>
              </div>
            <?php endforeach; ?>
            </div>

            <button class="hj-vss-arrow hj-vss-arrow--next" type="button" data-vss-next aria-label="<?php esc_attr_e('Next slide', 'hello-elementor-child'); ?>">
              <img src="<?php echo esc_url($vss_arrow_right); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
            </button>
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
      <?php foreach ($sections as $i => $s):
        $section_type = $s['section_type'] ?? 'content';
        $heading = $section_type === 'price' ? '' : trim((string) ($s['heading'] ?? ''));
        $enable_accordion = array_key_exists('enable_accordion', $s) ? !empty($s['enable_accordion']) : true;
        $has_content_block = ($section_type !== 'price') && (!empty($s['subheading']) || !empty($s['content']) || !empty($s['button']));
        $price_button = $s['price_button'] ?? [];
        $rating = $s['rating'] ?? [];
        $has_price_block = ($section_type === 'price') && (!empty($price_button['url']) || !empty($rating['label']) || !empty($rating['reviews_count']) || !empty($rating['stars']));
        if (!$heading && !$has_content_block && !$has_price_block) continue;
        $section_body = $vss_render_section_body($s, $enable_accordion);
      ?>
        <?php if ($section_type === 'price'): ?>
          <section class="hj-vss-section hj-vss-section--price-only">
            <?php echo $section_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </section>
        <?php elseif ($enable_accordion): ?>
          <details class="hj-vss-section hj-vss-main-accordion" <?php echo $i === 0 ? 'open' : ''; ?>>
            <summary class="hj-vss-main-summary">
              <span class="hj-vss-main-ind" aria-hidden="true"></span>
              <?php if ($heading): ?>
                <h2 class="hj-cb-title"><?php echo esc_html($heading); ?></h2>
              <?php endif; ?>
            </summary>
            <?php echo $section_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </details>
        <?php else: ?>
          <section class="hj-vss-section hj-vss-section--static">
            <?php if ($heading): ?>
              <h2 class="hj-cb-title hj-vss-static-title"><?php echo esc_html($heading); ?></h2>
            <?php endif; ?>
            <?php echo $section_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </section>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

  </div>

  <div class="hj-vss-modal" data-vss-modal hidden>
    <div class="hj-vss-modal__backdrop" data-vss-close></div>
    <div class="hj-vss-modal__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Video player', 'hello-elementor-child'); ?>">
      <button class="hj-vss-modal__close" type="button" data-vss-close aria-label="<?php esc_attr_e('Close video', 'hello-elementor-child'); ?>">×</button>
      <div class="hj-vss-modal__body" data-vss-modal-body></div>
    </div>
  </div>
</section>
