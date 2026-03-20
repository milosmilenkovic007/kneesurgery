<?php
if (!defined('ABSPATH')) {
    exit;
}

$heading = trim((string) get_sub_field('heading'));
$intro = trim((string) get_sub_field('intro'));
$desktop_columns = (int) get_sub_field('desktop_columns');
$tablet_columns = (int) get_sub_field('tablet_columns');
$mobile_columns = (int) get_sub_field('mobile_columns');
$rows = get_sub_field('items') ?: [];
$uid = uniqid('hj-vg-');

$desktop_columns = in_array($desktop_columns, [2, 3, 4], true) ? $desktop_columns : 4;
$tablet_columns = in_array($tablet_columns, [1, 2, 3], true) ? $tablet_columns : 2;
$mobile_columns = in_array($mobile_columns, [1, 2], true) ? $mobile_columns : 1;

$vg_is_media_url = static function ($url) {
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return in_array($ext, ['mp4', 'webm', 'ogg'], true);
};

$vg_get_embed_url = static function ($url) {
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

$vg_poster_url = static function ($poster) {
    if (is_array($poster) && !empty($poster['url'])) {
        return $poster['url'];
    }

    if (is_numeric($poster)) {
        $image = wp_get_attachment_image_src((int) $poster, 'large');
        return $image ? $image[0] : '';
    }

    return '';
};

$items = [];
foreach ($rows as $row) {
    $video_url = trim((string) ($row['video_url'] ?? ''));
    if ($video_url === '') {
        continue;
    }

    $video_type = $vg_is_media_url($video_url) ? 'file' : 'embed';
    $src = $video_type === 'file' ? $video_url : $vg_get_embed_url($video_url);
    if ($src === '') {
        continue;
    }

    $items[] = [
        'title' => trim((string) ($row['title'] ?? '')),
        'poster' => $vg_poster_url($row['poster'] ?? null),
        'poster_alt' => is_array($row['poster'] ?? null) ? trim((string) (($row['poster']['alt'] ?? ''))) : '',
        'type' => $video_type,
        'src' => $src,
    ];
}

if ($heading === '' && $intro === '' && empty($items)) {
    return;
}
?>

<section
  class="hj-video-grid"
  id="<?php echo esc_attr($uid); ?>"
  aria-label="<?php echo esc_attr($heading !== '' ? $heading : __('Video grid', 'hello-elementor-child')); ?>"
  style="--hj-vg-cols:<?php echo esc_attr((string) $desktop_columns); ?>;--hj-vg-cols-md:<?php echo esc_attr((string) $tablet_columns); ?>;--hj-vg-cols-sm:<?php echo esc_attr((string) $mobile_columns); ?>;"
>
  <div class="hj-vg-wrap">
    <?php if ($heading !== '' || $intro !== '') : ?>
      <header class="hj-vg-head">
        <?php if ($heading !== '') : ?><h2 class="hj-vg-title"><?php echo esc_html($heading); ?></h2><?php endif; ?>
        <?php if ($intro !== '') : ?><p class="hj-vg-intro"><?php echo esc_html($intro); ?></p><?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($items)) : ?>
      <div class="hj-vg-grid">
        <?php foreach ($items as $index => $item) : ?>
          <button
            class="hj-vg-card"
            type="button"
            data-vg-open
            data-vg-index="<?php echo esc_attr((string) $index); ?>"
            data-video-type="<?php echo esc_attr($item['type']); ?>"
            data-video-src="<?php echo esc_url($item['src']); ?>"
            aria-label="<?php echo esc_attr($item['title'] !== '' ? $item['title'] : __('Open video', 'hello-elementor-child')); ?>"
          >
            <?php if ($item['poster'] !== '') : ?>
              <img class="hj-vg-poster" src="<?php echo esc_url($item['poster']); ?>" alt="<?php echo esc_attr($item['poster_alt']); ?>" loading="lazy" decoding="async">
            <?php else : ?>
              <span class="hj-vg-poster hj-vg-poster--fallback" aria-hidden="true"></span>
            <?php endif; ?>

            <span class="hj-vg-play" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 7.75V16.25L16 12L9 7.75Z" fill="currentColor"/>
              </svg>
            </span>

            <?php if ($item['title'] !== '') : ?>
              <span class="hj-vg-card__caption"><?php echo esc_html($item['title']); ?></span>
            <?php endif; ?>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="hj-vg-modal" data-vg-modal hidden>
    <div class="hj-vg-modal__backdrop" data-vg-close></div>
    <div class="hj-vg-modal__dialog" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Video player', 'hello-elementor-child'); ?>">
      <button class="hj-vg-modal__close" type="button" data-vg-close aria-label="<?php esc_attr_e('Close video', 'hello-elementor-child'); ?>">×</button>
      <div class="hj-vg-modal__body" data-vg-modal-body></div>
    </div>
  </div>
</section>