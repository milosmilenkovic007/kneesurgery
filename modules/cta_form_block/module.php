<?php
$media_type = get_sub_field('media_type') ?: 'animation';
$image = get_sub_field('image');
$rating_source = trim((string) get_sub_field('rating_source'));
$rating_trustindex_shortcode = trim((string) get_sub_field('rating_trustindex_shortcode'));
$rating_style = trim((string) get_sub_field('rating_style')) ?: 'default';
$heading = trim((string) get_sub_field('heading'));
$heading_accent = trim((string) get_sub_field('heading_accent'));
$subheading = trim((string) get_sub_field('subheading'));
$anchor_id = trim((string) get_sub_field('anchor_id'));
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
$anchor_id = ltrim($anchor_id, '#');
$anchor_id = $anchor_id !== '' ? sanitize_html_class($anchor_id) : '';
$section_id = $anchor_id !== '' ? $anchor_id : 'candidate-form';
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

if (!in_array($rating_source, ['trustindex', 'google'], true)) {
  $rating_source = 'trustindex';
}

$google_reviews_data = $media_type === 'rating' && $rating_source === 'google' && function_exists('hj_get_google_reviews_data')
  ? hj_get_google_reviews_data()
  : null;
$use_google_reviews = $media_type === 'rating' && !empty($google_reviews_data['has_content']);
$use_trustindex_reviews = $media_type === 'rating'
  && $rating_trustindex_shortcode !== ''
  && ($rating_source === 'trustindex' || !$use_google_reviews);
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
<section class="hj-cta-form-block<?php echo $is_dark ? ' is-dark' : ''; ?><?php echo $media_type === 'rating' ? ' is-rating' : ($media_type === 'animation' ? ' is-animation' : ' is-image'); ?><?php echo $use_trustindex_reviews ? ' is-rating-trustindex' : ''; ?><?php echo $use_google_reviews ? ' is-rating-google' : ''; ?><?php echo $media_type === 'rating' ? ' is-rating-style-' . esc_attr(sanitize_html_class($rating_style)) : ''; ?>" id="<?php echo esc_attr($section_id); ?>" style="<?php echo esc_attr($style_vars); ?>" aria-label="CTA">
  <div class="hj-cfb-wrap">
    <div class="hj-cfb-grid">
      <div class="hj-cfb-media"<?php echo $media_is_decorative ? ' aria-hidden="true"' : ''; ?>>
        <?php if ($use_google_reviews): ?>
          <div class="hj-cfb-google-reviews">
            <?php if (!empty($google_reviews_data['has_summary'])): ?>
              <?php $summary_tag = !empty($google_reviews_data['reviews_url']) ? 'a' : 'div'; ?>
              <div class="hj-cfb-rating-summary">
                <<?php echo $summary_tag; ?> class="hj-cfb-rating-summary__link"<?php echo $summary_tag === 'a' ? ' href="' . esc_url($google_reviews_data['reviews_url']) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>>
                  <span class="hj-cfb-rating-summary__badge"><?php esc_html_e('Google Reviews', 'hello-elementor-child'); ?></span>

                  <div class="hj-cfb-rating-summary__row">
                    <?php if (!empty($google_reviews_data['rating'])): ?>
                      <strong class="hj-cfb-rating-summary__score"><?php echo esc_html(hj_google_reviews_format_rating($google_reviews_data['rating'])); ?></strong>
                    <?php endif; ?>

                    <?php if (!empty($google_reviews_data['stars_text'])): ?>
                      <span class="hj-cfb-rating-summary__stars" aria-hidden="true"><?php echo esc_html($google_reviews_data['stars_text']); ?></span>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($google_reviews_data['place_name'])): ?>
                    <span class="hj-cfb-rating-summary__place"><?php echo esc_html($google_reviews_data['place_name']); ?></span>
                  <?php endif; ?>

                  <?php if (!empty($google_reviews_data['reviews_count'])): ?>
                    <span class="hj-cfb-rating-summary__meta"><?php echo esc_html(sprintf(_n('Based on %d review', 'Based on %d reviews', (int) $google_reviews_data['reviews_count'], 'hello-elementor-child'), (int) $google_reviews_data['reviews_count'])); ?></span>
                  <?php endif; ?>
                </<?php echo $summary_tag; ?>>
              </div>
            <?php endif; ?>

            <?php if (!empty($google_reviews_data['reviews'])): ?>
              <div class="hj-cfb-rating-slider">
                <div class="hj-cfb-rating-track">
                  <?php foreach ($google_reviews_data['reviews'] as $index => $review): ?>
                    <?php
                    $author_name = trim((string) ($review['author_name'] ?? ''));
                    $author_avatar = trim((string) ($review['author_avatar'] ?? ''));
                    $author_initials = trim((string) ($review['author_initials'] ?? ''));
                    $review_meta = trim((string) ($review['relative_time'] ?? ''));
                    $review_text = trim((string) ($review['text'] ?? ''));
                    $review_text = $review_text !== '' ? wp_trim_words($review_text, 40, '...') : '';
                    ?>
                    <article class="hj-cfb-rating-card<?php echo $index === 0 ? ' is-active' : ''; ?>" data-cfb-slide>
                      <div class="hj-cfb-rating-head">
                        <div class="hj-cfb-rating-person">
                          <div class="hj-cfb-rating-avatar" aria-hidden="true">
                            <?php if ($author_avatar !== ''): ?>
                              <img src="<?php echo esc_url($author_avatar); ?>" alt="" loading="lazy" decoding="async">
                            <?php else: ?>
                              <span class="hj-cfb-rating-avatar__fallback"><?php echo esc_html($author_initials !== '' ? $author_initials : 'G'); ?></span>
                            <?php endif; ?>
                          </div>

                          <div class="hj-cfb-rating-meta">
                            <span class="hj-cfb-rating-name"><?php echo esc_html($author_name !== '' ? $author_name : __('Google user', 'hello-elementor-child')); ?></span>
                            <?php if ($review_meta !== ''): ?>
                              <span class="hj-cfb-rating-role"><?php echo esc_html($review_meta); ?></span>
                            <?php endif; ?>
                          </div>
                        </div>

                        <?php if (!empty($review['stars_text'])): ?>
                          <span class="hj-cfb-rating-stars" aria-hidden="true"><?php echo esc_html((string) $review['stars_text']); ?></span>
                        <?php endif; ?>
                      </div>

                      <?php if ($review_text !== ''): ?>
                        <div class="hj-cfb-rating-copy"><?php echo esc_html($review_text); ?></div>
                      <?php endif; ?>
                    </article>
                  <?php endforeach; ?>
                </div>

                <?php if (count($google_reviews_data['reviews']) > 1): ?>
                  <div class="hj-cfb-rating-nav">
                    <div class="hj-cfb-rating-dots">
                      <?php foreach ($google_reviews_data['reviews'] as $index => $review): ?>
                        <button type="button" class="hj-cfb-rating-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" data-cfb-dot aria-label="<?php echo esc_attr(sprintf(__('Show review %d', 'hello-elementor-child'), $index + 1)); ?>"></button>
                      <?php endforeach; ?>
                    </div>

                    <div class="hj-cfb-rating-arrows">
                      <button type="button" class="hj-cfb-rating-arrow" data-cfb-prev aria-label="<?php esc_attr_e('Previous review', 'hello-elementor-child'); ?>">
                        <span aria-hidden="true">&larr;</span>
                      </button>
                      <button type="button" class="hj-cfb-rating-arrow" data-cfb-next aria-label="<?php esc_attr_e('Next review', 'hello-elementor-child'); ?>">
                        <span aria-hidden="true">&rarr;</span>
                      </button>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php elseif ($use_trustindex_reviews): ?>
          <div class="hj-cfb-trustindex">
            <?php echo do_shortcode($rating_trustindex_shortcode); ?>
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
        <?php else: ?>
          <div class="hj-cfb-form">
            <p style="color: red; font-weight: bold;">Fluent Forms ni nameščen ali ni izbran noben obrazec. Prosimo, preverite nastavitve modula.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
