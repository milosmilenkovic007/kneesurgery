<?php
if (!defined('ABSPATH')) {
    exit;
}

$background_image = get_sub_field('background_image');
$background_video = get_sub_field('background_video');
$use_video_on_mobile = (bool) get_sub_field('use_video_on_mobile');
$title = trim((string) get_sub_field('title'));
$subheading = trim((string) get_sub_field('subheading'));
$block_height = trim((string) get_sub_field('block_height'));

$background_url = '';
$background_video_url = '';
if (is_array($background_video) && !empty($background_video['url'])) {
  $background_video_url = (string) $background_video['url'];
} elseif (is_string($background_video)) {
  $background_video_url = trim($background_video);
}

if (is_array($background_image) && !empty($background_image['url'])) {
    $background_url = (string) $background_image['url'];
}

if ($block_height === '') {
  $block_height = '400px';
}

if (!preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|vh|vw|svh|lvh|dvh|%)$/', $block_height)) {
  $block_height = '400px';
}

if ($title === '' && $subheading === '' && $background_url === '' && $background_video_url === '') {
    return;
}

$style_vars = '--hj-ha-height:' . $block_height . ';';
if ($background_url !== '') {
  $style_vars .= '--hj-ha-bg-image:url(\'' . esc_url($background_url) . '\');';
}

$section_classes = ['hj-hero-about'];
$section_classes[] = $use_video_on_mobile ? 'hj-hero-about--video-mobile-on' : 'hj-hero-about--video-mobile-off';
?>
<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>" aria-label="About hero" style="<?php echo esc_attr($style_vars); ?>">
  <?php if ($background_video_url !== '') : ?>
    <div class="hj-ha-media" aria-hidden="true">
      <video
        class="hj-ha-video"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        <?php echo $background_url !== '' ? 'poster="' . esc_url($background_url) . '"' : ''; ?>
      >
        <source src="<?php echo esc_url($background_video_url); ?>">
      </video>
    </div>
  <?php endif; ?>
  <div class="hj-ha-overlay"></div>
  <div class="hj-ha-wrap">
    <?php if ($title !== '') : ?>
      <h1 class="hj-ha-title"><?php echo esc_html($title); ?></h1>
    <?php endif; ?>

    <?php if ($subheading !== '') : ?>
      <p class="hj-ha-subheading"><?php echo nl2br(esc_html($subheading)); ?></p>
    <?php endif; ?>
  </div>
</section>