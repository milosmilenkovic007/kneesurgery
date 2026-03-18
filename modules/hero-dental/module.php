<?php
// Data from ACF sub fields
$title   = get_sub_field('title');
$intro   = get_sub_field('intro');
$bullets = get_sub_field('bullets');
$cta     = get_sub_field('cta');
$rating  = get_sub_field('rating');
$media   = get_sub_field('media');

function hj_hd_split_title($t){
    $t = trim((string) $t);
    if ($t === '') {
        return ['', ''];
    }
    $parts = preg_split('/\s+/', $t, 2);
    return [$parts[0] ?? '', $parts[1] ?? ''];
}
list($t1, $t2) = hj_hd_split_title($title);
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

      <?php if (!empty($cta['label']) && !empty($cta['url'])): $cta_href = trim((string)$cta['url']); $is_cand = strpos($cta_href, '#candidate') !== false; ?>
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

      <?php if (!empty($rating['label'])): ?>
        <div class="hj-hd-rating hj-hd-rating--under-image">
          <div class="row">
            <span class="stars" aria-hidden="true">★★★★★</span>
            <span class="label">(<?php echo esc_html($rating['label']); ?>)</span>
            <?php if (!empty($rating['reviews_count'])): ?>
              <span class="meta">Based on</span>
              <?php if (!empty($rating['reviews_url'])): ?>
                <a href="<?php echo esc_url($rating['reviews_url']); ?>"><?php echo intval($rating['reviews_count']); ?> reviews</a>
              <?php else: ?>
                <span><?php echo intval($rating['reviews_count']); ?> reviews</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
