<?php
$media_type = get_sub_field('media_type') ?: 'image';
$image = get_sub_field('image');
$rating_slides = get_sub_field('rating_slides') ?: [];
$heading = trim((string) get_sub_field('heading'));
$heading_accent = trim((string) get_sub_field('heading_accent'));
$subheading = trim((string) get_sub_field('subheading'));
$bg_color = trim((string) get_sub_field('bg_color')) ?: '#ffffff';
$text_color = trim((string) get_sub_field('text_color')) ?: '#111827';
$accent_color = trim((string) get_sub_field('accent_color')) ?: '#4951d5';
$separator_color = trim((string) get_sub_field('separator_color')) ?: '#4951d5';
$button_bg_color = trim((string) get_sub_field('button_bg_color')) ?: '#4951d5';
$button_text_color = trim((string) get_sub_field('button_text_color')) ?: '#ffffff';
$terms_link_color = trim((string) get_sub_field('terms_link_color')) ?: '#ffffff';
$form_id = get_sub_field('fluent_form_id');
$uid = uniqid('hj-cfb-');

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
$terms_link_color_clean = $sanitize_color($terms_link_color, '#ffffff');

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
<section class="hj-cta-form-block<?php echo $is_dark ? ' is-dark' : ''; ?><?php echo $media_type === 'rating' ? ' is-rating' : ' is-image'; ?>" id="<?php echo esc_attr($uid); ?>" style="<?php echo esc_attr($style_vars); ?>" aria-label="CTA">
  <div class="hj-cfb-wrap">
    <div class="hj-cfb-grid">
      <div class="hj-cfb-media" aria-hidden="true">
        <?php if ($media_type === 'rating' && !empty($rating_slides)): ?>
          <div class="hj-cfb-rating-slider" data-cfb-slider>
            <div class="hj-cfb-rating-track" data-cfb-track>
              <?php foreach ($rating_slides as $index => $slide):
                $slide_name = trim((string) ($slide['name'] ?? ''));
                $slide_text = trim((string) ($slide['content'] ?? ($slide['text'] ?? '')));
                $slide_stars = max(1, min(5, (int) ($slide['rating'] ?? ($slide['stars'] ?? 5))));
                $slide_avatar = $slide['avatar'] ?? ($slide['photo'] ?? null);
                if ($slide_name === '' && $slide_text === '') { continue; }
              ?>
                <article class="hj-cfb-rating-card<?php echo $index === 0 ? ' is-active' : ''; ?>" data-cfb-slide>
                  <div class="hj-cfb-rating-head">
                    <div class="hj-cfb-rating-person">
                      <span class="hj-cfb-rating-avatar">
                        <?php if (is_array($slide_avatar) && !empty($slide_avatar['ID'])): ?>
                          <?php echo wp_get_attachment_image((int) $slide_avatar['ID'], 'thumbnail', false, ['loading' => 'lazy', 'decoding' => 'async']); ?>
                        <?php elseif (is_array($slide_avatar) && !empty($slide_avatar['url'])): ?>
                          <img src="<?php echo esc_url($slide_avatar['url']); ?>" alt="<?php echo esc_attr($slide_avatar['alt'] ?? $slide_name); ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                          <span class="hj-cfb-rating-avatar__fallback"><?php echo esc_html(mb_substr($slide_name ?: 'P', 0, 1)); ?></span>
                        <?php endif; ?>
                      </span>
                      <span class="hj-cfb-rating-meta">
                        <?php if ($slide_name !== ''): ?><span class="hj-cfb-rating-name"><?php echo esc_html($slide_name); ?></span><?php endif; ?>
                      </span>
                    </div>
                    <div class="hj-cfb-rating-stars" aria-hidden="true"><?php echo esc_html(str_repeat('★', $slide_stars)); ?></div>
                  </div>
                  <?php if ($slide_text !== ''): ?>
                    <div class="hj-cfb-rating-copy"><?php echo nl2br(esc_html($slide_text)); ?></div>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
            <?php if (count($rating_slides) > 1): ?>
              <div class="hj-cfb-rating-nav">
                <div class="hj-cfb-rating-dots" data-cfb-dots>
                  <?php foreach ($rating_slides as $index => $slide): ?>
                    <button class="hj-cfb-rating-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-cfb-dot="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Rating slide %d', 'hello-elementor-child'), $index + 1)); ?>"></button>
                  <?php endforeach; ?>
                </div>
                <div class="hj-cfb-rating-arrows">
                  <button class="hj-cfb-rating-arrow is-prev" type="button" data-cfb-prev aria-label="<?php echo esc_attr__('Previous rating', 'hello-elementor-child'); ?>">
                    <span aria-hidden="true">&#8592;</span>
                  </button>
                  <button class="hj-cfb-rating-arrow is-next" type="button" data-cfb-next aria-label="<?php echo esc_attr__('Next rating', 'hello-elementor-child'); ?>">
                    <span aria-hidden="true">&#8594;</span>
                  </button>
                </div>
              </div>
            <?php endif; ?>
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
