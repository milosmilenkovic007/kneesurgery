<?php
// Data from ACF sub fields
$title   = get_sub_field('title');
$intro   = get_sub_field('intro');
$bullets = get_sub_field('bullets');
$cta     = get_sub_field('cta');
$rating  = get_sub_field('rating');
$media   = get_sub_field('media');

if (!is_array($rating)) {
    $rating = [];
}

$rating_mode = trim((string) ($rating['mode'] ?? 'manual')) ?: 'manual';
$rating_mode = in_array($rating_mode, ['manual', 'dynamic'], true) ? $rating_mode : 'manual';
$rating_stars = max(0, min(5, (float) ($rating['stars'] ?? 0)));
$rating_stars_text = $rating_stars > 0 ? str_repeat('★', (int) round($rating_stars)) : '';
$rating_label = trim((string) ($rating['label'] ?? ''));
$rating_reviews_count = (int) ($rating['reviews_count'] ?? 0);
$rating_reviews_url = trim((string) ($rating['reviews_url'] ?? ''));
$rating_trustindex_shortcode = trim((string) ($rating['trustindex_shortcode'] ?? ''));
$rating_has_dynamic_source = false;

if ($rating_mode === 'dynamic' && $rating_trustindex_shortcode !== '') {
  global $trustindex_pm_google;

  if (is_object($trustindex_pm_google) && method_exists($trustindex_pm_google, 'getPageDetails')) {
    $page_details = $trustindex_pm_google->getPageDetails();

    if (is_array($page_details) && !empty($page_details)) {
      $dynamic_rating_score = (float) ($page_details['rating_score'] ?? 0);
      $dynamic_reviews_count = (int) ($page_details['rating_number'] ?? 0);
      $dynamic_page_id = trim((string) ($page_details['id'] ?? ''));

      if ($dynamic_rating_score > 0) {
        $rating_stars = max(0, min(5, $dynamic_rating_score));
        $rating_stars_text = str_repeat('★', (int) round($dynamic_rating_score));

        $formatted_rating = floor($dynamic_rating_score) === $dynamic_rating_score
          ? number_format($dynamic_rating_score, 0)
          : number_format($dynamic_rating_score, 1);

        $rating_label = $formatted_rating . ' stars';
      }

      if ($dynamic_reviews_count > 0) {
        $rating_reviews_count = $dynamic_reviews_count;
      }

      if ($dynamic_page_id !== '') {
        $rating_reviews_url = preg_match('/&c=\w+&v=\d+/', $dynamic_page_id)
          ? 'https://customerreviews.google.com/v/merchant?q=' . rawurlencode($dynamic_page_id)
          : 'https://admin.trustindex.io/api/googleReview?place-id=' . rawurlencode($dynamic_page_id);
      }
    }
  }

  $rating_has_dynamic_source = $rating_label === '' || $rating_reviews_count <= 0;
}

$rating_has_manual_content = $rating_stars_text !== '' || $rating_label !== '' || $rating_reviews_count > 0;
$rating_has_content = $rating_has_dynamic_source || $rating_has_manual_content;

$split_title = static function ($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return ['', ''];
    }

    $parts = preg_split('/\s+/', $value, 2);
    return [$parts[0] ?? '', $parts[1] ?? ''];
};

list($t1, $t2) = $split_title($title);
?>
<section class="hj-hero-dental">
  <div class="hj-hd-wrap">
    <div class="hj-hd-left">
      <?php if ($title): ?>
        <h1 class="hj-hd-title">
          <span class="accent-italic"><?php echo esc_html($t1); ?></span><?php if ($t2): ?> <?php echo esc_html($t2); ?><?php endif; ?>
        </h1>
      <?php endif; ?>

      <?php if ($intro): ?>
        <p class="hj-hd-intro"><?php echo esc_html($intro); ?></p>
      <?php endif; ?>

      <?php if (!empty($bullets)): ?>
        <ul class="hj-hd-bullets" role="list">
          <?php foreach ($bullets as $b): $txt = $b['text'] ?? ''; if (!$txt) continue; ?>
            <li class="hj-hd-bullet">
              <span class="ic">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"/>
                  <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
              <span><?php echo esc_html($txt); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if (!empty($cta['label']) && !empty($cta['url'])): $cta_href = trim((string) $cta['url']); $is_cand = strpos($cta_href, '#candidate') !== false; ?>
      <div class="hj-hd-cta-rating">

        <div class="hj-hd-cta">
          <a class="btn-primary" href="<?php echo esc_attr($cta_href); ?>" <?php if($is_cand) echo 'data-candidate="1"'; ?>>
            <?php echo esc_html($cta['label']); ?> →
          </a>
          <?php if (!empty($cta['note'])): ?>
          <div class="note">*<?php echo esc_html($cta['note']); ?></div>
          <?php endif; ?>
        </div>

      </div>
      <?php endif; ?>
    </div>

    <div class="hj-hd-right-col">
      <?php $layout = $media['layout'] ?? 'grid4'; ?>
      <?php if ($layout === 'single'): ?>
        <div class="hj-hd-right hj-hd-right--single">
          <?php if (!empty($media['single_image'])): $img = $media['single_image']; ?>
            <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
          <?php endif; ?>
        </div>
      <?php elseif ($layout === 'three'): ?>
        <div class="hj-hd-right hj-hd-right--three">
          <?php if (!empty($media['img1'])): $img = $media['img1']; ?>
            <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
          <?php endif; ?>
          <?php if (!empty($media['img2'])): $img = $media['img2']; ?>
            <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
          <?php endif; ?>
          <?php if (!empty($media['img3'])): $img = $media['img3']; ?>
            <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="hj-hd-right">
          <div>
            <?php if (!empty($media['left_image'])): $img = $media['left_image']; ?>
              <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
            <?php endif; ?>
          </div>
          <div>
            <?php if (!empty($media['top_right'])): $img = $media['top_right']; ?>
              <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
            <?php endif; ?>
          </div>
          <div>
            <?php if (!empty($media['bottom_left'])): $img = $media['bottom_left']; ?>
              <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
            <?php endif; ?>
          </div>
          <div>
            <?php if (!empty($media['bottom_right'])): $img = $media['bottom_right']; ?>
              <img class="card" src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($img['alt']); ?>">
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($rating_has_content): ?>
        <div class="hj-hd-rating hj-hd-rating--under-image<?php echo $rating_has_dynamic_source ? ' hj-hd-rating--dynamic' : ''; ?>" data-rating-mode="<?php echo esc_attr($rating_mode); ?>">
          <?php if ($rating_reviews_url !== ''): ?>
            <a class="row hj-hd-rating-link" href="<?php echo esc_url($rating_reviews_url); ?>" target="_blank" rel="noopener noreferrer">
              <span class="stars" aria-hidden="true"><?php echo esc_html($rating_stars_text); ?></span>
              <span class="label"<?php echo $rating_label === '' ? ' hidden' : ''; ?>><?php echo esc_html('(' . $rating_label . ')'); ?></span>
              <span class="meta"<?php echo $rating_reviews_count <= 0 ? ' hidden' : ''; ?>>Based on</span>
              <span class="hj-hd-rating-reviews"<?php echo $rating_reviews_count <= 0 ? ' hidden' : ''; ?>><?php echo $rating_reviews_count > 0 ? esc_html(sprintf(_n('%d review', '%d reviews', $rating_reviews_count, 'hello-elementor-child'), $rating_reviews_count)) : ''; ?></span>
            </a>
          <?php else: ?>
            <div class="row">
              <span class="stars" aria-hidden="true"><?php echo esc_html($rating_stars_text); ?></span>
              <span class="label"<?php echo $rating_label === '' ? ' hidden' : ''; ?>><?php echo esc_html('(' . $rating_label . ')'); ?></span>
              <span class="meta"<?php echo $rating_reviews_count <= 0 ? ' hidden' : ''; ?>>Based on</span>
              <span class="hj-hd-rating-reviews"<?php echo $rating_reviews_count <= 0 ? ' hidden' : ''; ?>><?php echo $rating_reviews_count > 0 ? esc_html(sprintf(_n('%d review', '%d reviews', $rating_reviews_count, 'hello-elementor-child'), $rating_reviews_count)) : ''; ?></span>
            </div>
          <?php endif; ?>

          <?php if ($rating_has_dynamic_source): ?>
            <div class="hj-hd-rating-source" aria-hidden="true">
              <?php echo apply_filters('the_content', $rating_trustindex_shortcode); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
