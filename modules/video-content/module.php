<?php
if (!defined('ABSPATH')) {
    exit;
}

$anchor_id = trim((string) get_sub_field('anchor_id'));
$video_poster = get_sub_field('video_poster');
$video_url = trim((string) get_sub_field('video_url'));
$title = trim((string) get_sub_field('title'));
$title_accent = trim((string) get_sub_field('title_accent'));
$content = (string) get_sub_field('content');
$accordion_items = get_sub_field('accordion_items') ?: [];
$button = get_sub_field('button') ?: [];

$button_url = trim((string) ($button['url'] ?? ''));
$button_title = trim((string) ($button['title'] ?? ''));
$button_target = trim((string) ($button['target'] ?? ''));

$anchor_id = ltrim($anchor_id, '#');
$anchor_id = $anchor_id !== '' ? sanitize_html_class($anchor_id) : '';

if (empty($video_poster) && $video_url === '' && $title === '' && $title_accent === '' && trim(wp_strip_all_tags($content)) === '' && empty($accordion_items)) {
    return;
}

$vc_is_media_url = static function ($url) {
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'webm', 'ogg'], true);
};

$vc_get_embed_url = static function ($url) {
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

$video_type = $vc_is_media_url($video_url) ? 'file' : 'embed';
$video_embed_url = $video_type === 'embed' ? $vc_get_embed_url($video_url) : '';
$video_src = $video_type === 'file' ? $video_url : $video_embed_url;
?>
<section class="hj-video-content"<?php echo $anchor_id !== '' ? ' id="' . esc_attr($anchor_id) . '"' : ''; ?> aria-label="Video content">
  <div class="hj-vc-shell">
    <?php if ($title !== '' || $title_accent !== '') : ?>
      <header class="hj-vc-head">
        <h2 class="hj-vc-title">
          <span class="hj-vc-title__mark" aria-hidden="true"></span>
          <span class="hj-vc-title__text">
            <?php if ($title !== '') : ?><span class="hj-vc-title__main"><?php echo esc_html($title); ?></span><?php endif; ?>
            <?php if ($title_accent !== '') : ?> <span class="hj-vc-title__accent"><?php echo esc_html($title_accent); ?></span><?php endif; ?>
          </span>
        </h2>
      </header>
    <?php endif; ?>

    <div class="hj-vc-grid">
      <div class="hj-vc-media-col">
        <?php if (!empty($video_poster) && $video_src !== '') : ?>
          <button
            class="hj-vc-media"
            type="button"
            data-vc-open
            data-video-type="<?php echo esc_attr($video_type); ?>"
            data-video-src="<?php echo esc_url($video_src); ?>"
            aria-label="<?php esc_attr_e('Open video', 'hello-elementor-child'); ?>"
          >
            <span class="hj-vc-media__frame" aria-hidden="true"></span>
            <?php if (!empty($video_poster['ID'])) : ?>
              <?php echo wp_get_attachment_image((int) $video_poster['ID'], 'large', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
            <?php elseif (!empty($video_poster['url'])) : ?>
              <img src="<?php echo esc_url($video_poster['url']); ?>" alt="<?php echo esc_attr($video_poster['alt'] ?? ''); ?>" loading="lazy" decoding="async">
            <?php endif; ?>
            <span class="hj-vc-play" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 7.75V16.25L16 12L9 7.75Z" fill="currentColor"/>
              </svg>
            </span>
          </button>
        <?php endif; ?>
      </div>

      <div class="hj-vc-content-col">
        <?php if (trim(wp_strip_all_tags($content)) !== '') : ?>
          <div class="hj-vc-content"><?php echo wp_kses_post($content); ?></div>
        <?php endif; ?>

        <?php if (!empty($accordion_items)) : ?>
          <div class="hj-vc-accordion" role="list">
            <?php foreach ($accordion_items as $index => $item) :
              $item_title = trim((string) ($item['title'] ?? ''));
              $item_content = (string) ($item['content'] ?? '');

              if ($item_title === '' && trim(wp_strip_all_tags($item_content)) === '') {
                continue;
              }
            ?>
              <details class="hj-vc-accordion__item" <?php echo $index === 0 ? 'open' : ''; ?>>
                <summary class="hj-vc-accordion__summary">
                  <span class="hj-vc-accordion__title"><?php echo esc_html($item_title); ?></span>
                  <span class="hj-vc-accordion__icon" aria-hidden="true"></span>
                </summary>
                <?php if (trim(wp_strip_all_tags($item_content)) !== '') : ?>
                  <div class="hj-vc-accordion__content">
                    <?php echo wp_kses_post($item_content); ?>
                  </div>
                <?php endif; ?>
              </details>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($button_url !== '' && $button_title !== '') : ?>
          <div class="hj-vc-actions">
            <a class="hj-vc-button" href="<?php echo esc_url($button_url); ?>"<?php echo $button_target !== '' ? ' target="' . esc_attr($button_target) . '" rel="noopener"' : ''; ?>>
              <span><?php echo esc_html($button_title); ?></span>
              <span class="hj-vc-button__arrow" aria-hidden="true"></span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="hj-vc-modal" data-vc-modal hidden>
    <div class="hj-vc-modal__backdrop" data-vc-close></div>
    <div class="hj-vc-modal__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Video player', 'hello-elementor-child'); ?>">
      <button class="hj-vc-modal__close" type="button" data-vc-close aria-label="<?php esc_attr_e('Close video', 'hello-elementor-child'); ?>">×</button>
      <div class="hj-vc-modal__body" data-vc-modal-body></div>
    </div>
  </div>
</section>