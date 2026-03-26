<?php
$media_type = get_sub_field('media_type') ?: 'animation';
$image = get_sub_field('image');
$rating_trustindex_shortcode = trim((string) get_sub_field('rating_trustindex_shortcode'));
$rating_style = trim((string) get_sub_field('rating_style')) ?: 'default';
$heading = trim((string) get_sub_field('heading'));
$heading_accent = trim((string) get_sub_field('heading_accent'));
$subheading = trim((string) get_sub_field('subheading'));
$bg_color = trim((string) get_sub_field('bg_color')) ?: '#ffffff';
$text_color = trim((string) get_sub_field('text_color')) ?: '#111827';
$accent_color = trim((string) get_sub_field('accent_color')) ?: '#4951d5';
$separator_color = trim((string) get_sub_field('separator_color')) ?: '#4951d5';
$button_bg_color = trim((string) get_sub_field('button_bg_color')) ?: '#4951d5';
$button_text_color = trim((string) get_sub_field('button_text_color')) ?: '#ffffff';
$terms_link_color = trim((string) get_sub_field('terms_link_color')) ?: '#4951d5';
$animation_speed = (float) get_sub_field('animation_speed');
$animation_loop = get_sub_field('animation_loop');
$form_id = get_sub_field('fluent_form_id');
$uid = uniqid('hj-cfb-');
$animation_url = get_stylesheet_directory_uri() . '/assets/animation/contactmail.lottie';

if ($animation_speed <= 0) {
  $animation_speed = 0.4;
}

$animation_speed = max(0.1, min(5, $animation_speed));
$animation_loop_enabled = $animation_loop === null ? true : !empty($animation_loop);

if ($media_type === 'animation' && !defined('HJ_CFB_DOTLOTTIE_PLAYER_LOADED')) {
  define('HJ_CFB_DOTLOTTIE_PLAYER_LOADED', true);

  add_action('wp_footer', static function () {
    ?>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs"></script>
    <?php
  }, 1);
}

$image_url = is_array($image) ? ($image['url'] ?? '') : '';
$image_alt = is_array($image) ? ($image['alt'] ?? '') : '';
if (!$image_alt && is_array($image)) { $image_alt = $image['title'] ?? ''; }

$sanitize_color = static function ($value, $fallback) {
  $sanitized = sanitize_hex_color($value);
  return $sanitized ?: $fallback;
};

$bg_color_clean = $sanitize_color($bg_color, '#ffffff');
$text_color_clean = $sanitize_color($text_color, '#111827');
$accent_color_clean = $sanitize_color($accent_color, '#4951d5');
$separator_color_clean = $sanitize_color($separator_color, '#4951d5');
$button_bg_color_clean = $sanitize_color($button_bg_color, '#4951d5');
$button_text_color_clean = $sanitize_color($button_text_color, '#ffffff');
$terms_link_color_clean = $sanitize_color($terms_link_color, '#4951d5');

$use_trustindex_reviews = $media_type === 'rating' && $rating_trustindex_shortcode !== '';
$media_is_decorative = $media_type === 'animation' || ($media_type === 'image' && $image_url !== '');

$hex = ltrim($bg_color_clean, '#');
if (strlen($hex) === 3) {
  $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
}
$r = hexdec(substr($hex, 0, 2));
$g = hexdec(substr($hex, 2, 2));
$b = hexdec(substr($hex, 4, 2));
$is_dark = (($r * 299) + ($g * 587) + ($b * 114)) / 1000 < 155;

$style_vars = '--cfb-bg:' . $bg_color_clean . ';';
$style_vars .= '--cfb-text:' . $text_color_clean . ';';
$style_vars .= '--cfb-muted:' . $text_color_clean . ';';
$style_vars .= '--cfb-accent:' . $accent_color_clean . ';';
$style_vars .= '--cfb-separator:' . $separator_color_clean . ';';
$style_vars .= '--cfb-button-bg:' . $button_bg_color_clean . ';';
$style_vars .= '--cfb-button-text:' . $button_text_color_clean . ';';
$style_vars .= '--cfb-link:' . $terms_link_color_clean . ';';
?>
<section class="hj-cta-form-block<?php echo $is_dark ? ' is-dark' : ''; ?><?php echo $media_type === 'rating' ? ' is-rating' : ($media_type === 'animation' ? ' is-animation' : ' is-image'); ?><?php echo $use_trustindex_reviews ? ' is-rating-trustindex' : ''; ?><?php echo $media_type === 'rating' ? ' is-rating-style-' . esc_attr(sanitize_html_class($rating_style)) : ''; ?>" id="<?php echo esc_attr($uid); ?>" style="<?php echo esc_attr($style_vars); ?>" aria-label="CTA">
  <div class="hj-cfb-wrap">
    <div class="hj-cfb-grid">
      <div class="hj-cfb-media"<?php echo $media_is_decorative ? ' aria-hidden="true"' : ''; ?>>
        <?php if ($use_trustindex_reviews): ?>
          <div class="hj-cfb-trustindex">
            <?php echo apply_filters('the_content', $rating_trustindex_shortcode); ?>
          </div>
        <?php elseif ($media_type === 'animation'): ?>
          <div class="hj-cfb-animation-wrap">
            <dotlottie-player
              class="hj-cfb-animation"
              src="<?php echo esc_url($animation_url); ?>"
              autoplay
              background="transparent"
              speed="<?php echo esc_attr((string) $animation_speed); ?>"
              <?php echo $animation_loop_enabled ? 'loop' : ''; ?>
            ></dotlottie-player>
          </div>
        <?php elseif ($image_url): ?>
          <img class="hj-cfb-img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy" />
        <?php endif; ?>
      </div>

      <div class="hj-cfb-content">
        <?php if ($heading || $heading_accent): ?>
          <h2 class="hj-cb-title hj-cfb-title">
            <span class="hj-cfb-title-mark" aria-hidden="true"></span>
            <span class="hj-cfb-title-text">
              <?php if ($heading): ?><span class="hj-cfb-title-main"><?php echo esc_html($heading); ?></span><?php endif; ?>
              <?php if ($heading_accent): ?> <span class="hj-cfb-title-accent"><?php echo esc_html($heading_accent); ?></span><?php endif; ?>
            </span>
          </h2>
        <?php endif; ?>

        <?php if ($subheading): ?>
          <p class="hj-cfb-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>

        <?php if ($form_id): ?>
          <div class="hj-cfb-form">
            <?php echo do_shortcode('[fluentform id="' . esc_attr($form_id) . '"]'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
