<?php
if (!defined('ABSPATH')) {
    exit;
}

$background_image = get_sub_field('background_image');
$title = trim((string) get_sub_field('title'));
$subheading = trim((string) get_sub_field('subheading'));
$block_height = trim((string) get_sub_field('block_height'));

$background_url = '';
if (is_array($background_image) && !empty($background_image['url'])) {
    $background_url = (string) $background_image['url'];
}

if ($block_height === '') {
  $block_height = '400px';
}

if (!preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|vh|vw|svh|lvh|dvh|%)$/', $block_height)) {
  $block_height = '400px';
}

if ($title === '' && $subheading === '' && $background_url === '') {
    return;
}

$style_vars = '--hj-ha-height:' . $block_height . ';';
if ($background_url !== '') {
  $style_vars .= '--hj-ha-bg-image:url(\'' . esc_url($background_url) . '\');';
}
?>
<section class="hj-hero-about" aria-label="About hero" style="<?php echo esc_attr($style_vars); ?>">
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