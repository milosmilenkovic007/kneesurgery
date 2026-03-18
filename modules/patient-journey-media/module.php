<?php
if (!defined('ABSPATH')) exit;

$image = get_sub_field('image');
$image_badge_icon = get_sub_field('image_badge_icon');
$image_badge_title = trim((string) get_sub_field('image_badge_title'));
$image_badge_text = trim((string) get_sub_field('image_badge_text'));
$video_poster = get_sub_field('video_poster');
$video_url = trim((string) get_sub_field('video_url'));
$title = trim((string) get_sub_field('title'));
$content = get_sub_field('content');
$primary_button = get_sub_field('primary_button');
$secondary_button = get_sub_field('secondary_button');
$uid = uniqid('hj-pjm-');

if (empty($image) && empty($video_poster) && $title === '' && empty($content)) {
    return;
}

$pjm_is_media_url = static function ($url) {
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array($ext, ['mp4', 'webm', 'ogg'], true);
};

$pjm_get_embed_url = static function ($url) {
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

$video_type = $pjm_is_media_url($video_url) ? 'file' : 'embed';
$video_embed_url = $video_type === 'embed' ? $pjm_get_embed_url($video_url) : '';
?>
<section class="hj-patient-journey-media" id="<?php echo esc_attr($uid); ?>" aria-label="Patient journey media">
  <div class="hj-pjm-wrap">
    <div class="hj-pjm-media-col">
      <?php if (!empty($image)): ?>
        <figure class="hj-pjm-card hj-pjm-card--image">
          <?php if (!empty($image['ID'])): ?>
            <?php echo wp_get_attachment_image((int) $image['ID'], 'large', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
          <?php elseif (!empty($image['url'])): ?>
            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" decoding="async">
          <?php endif; ?>

          <?php if (!empty($image_badge_icon) || $image_badge_title !== '' || $image_badge_text !== ''): ?>
            <figcaption class="hj-pjm-badge">
              <span class="hj-pjm-badge__icon" aria-hidden="true">
                <?php if (!empty($image_badge_icon['ID'])): ?>
                  <?php echo wp_get_attachment_image((int) $image_badge_icon['ID'], 'thumbnail', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                <?php elseif (!empty($image_badge_icon['url'])): ?>
                  <img src="<?php echo esc_url($image_badge_icon['url']); ?>" alt="<?php echo esc_attr($image_badge_icon['alt'] ?? ''); ?>" loading="lazy" decoding="async">
                <?php else: ?>
                  <span class="icon icon-checked1 hj-pjm-badge__icon-fallback"></span>
                <?php endif; ?>
              </span>

              <span class="hj-pjm-badge__text">
                <?php if ($image_badge_title !== ''): ?><span class="hj-pjm-badge__title"><?php echo esc_html($image_badge_title); ?></span><?php endif; ?>
                <?php if ($image_badge_text !== ''): ?><span class="hj-pjm-badge__copy"><?php echo esc_html($image_badge_text); ?></span><?php endif; ?>
              </span>
            </figcaption>
          <?php endif; ?>
        </figure>
      <?php endif; ?>

      <?php if (!empty($video_poster) && $video_url !== ''): ?>
        <button
          class="hj-pjm-card hj-pjm-card--video"
          type="button"
          data-pjm-open
          data-video-type="<?php echo esc_attr($video_type); ?>"
          data-video-src="<?php echo esc_url($video_type === 'file' ? $video_url : $video_embed_url); ?>"
          aria-label="<?php esc_attr_e('Open video', 'hello-elementor-child'); ?>"
        >
          <?php if (!empty($video_poster['ID'])): ?>
            <?php echo wp_get_attachment_image((int) $video_poster['ID'], 'large', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
          <?php elseif (!empty($video_poster['url'])): ?>
            <img src="<?php echo esc_url($video_poster['url']); ?>" alt="<?php echo esc_attr($video_poster['alt'] ?? ''); ?>" loading="lazy" decoding="async">
          <?php endif; ?>
          <span class="hj-pjm-play" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M9 7.75V16.25L16 12L9 7.75Z" fill="currentColor"/>
            </svg>
          </span>
        </button>
      <?php endif; ?>
    </div>

    <div class="hj-pjm-content-col">
      <?php if ($title !== ''): ?>
        <h2 class="hj-pjm-title"><?php echo nl2br(esc_html($title)); ?></h2>
      <?php endif; ?>

      <?php if (!empty($content)): ?>
        <div class="hj-pjm-content">
          <?php echo wp_kses_post($content); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($primary_button['url']) || !empty($secondary_button['url'])): ?>
        <div class="hj-pjm-actions">
          <?php if (!empty($primary_button['url']) && !empty($primary_button['title'])): ?>
            <a class="hj-pjm-btn hj-pjm-btn--primary" href="<?php echo esc_url($primary_button['url']); ?>"<?php echo !empty($primary_button['target']) ? ' target="' . esc_attr($primary_button['target']) . '" rel="noopener"' : ''; ?>>
              <?php echo esc_html($primary_button['title']); ?>
            </a>
          <?php endif; ?>

          <?php if (!empty($secondary_button['url']) && !empty($secondary_button['title'])): ?>
            <a class="hj-pjm-btn hj-pjm-btn--secondary" href="<?php echo esc_url($secondary_button['url']); ?>"<?php echo !empty($secondary_button['target']) ? ' target="' . esc_attr($secondary_button['target']) . '" rel="noopener"' : ''; ?>>
              <?php echo esc_html($secondary_button['title']); ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="hj-pjm-modal" data-pjm-modal hidden>
    <div class="hj-pjm-modal__backdrop" data-pjm-close></div>
    <div class="hj-pjm-modal__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Video player', 'hello-elementor-child'); ?>">
      <button class="hj-pjm-modal__close" type="button" data-pjm-close aria-label="<?php esc_attr_e('Close video', 'hello-elementor-child'); ?>">×</button>
      <div class="hj-pjm-modal__body" data-pjm-modal-body></div>
    </div>
  </div>
</section>