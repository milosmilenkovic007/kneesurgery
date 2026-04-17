<?php
$subheading = trim((string) get_sub_field('subheading'));
$title = trim((string) get_sub_field('title'));
$review_limit = max(1, min(5, (int) get_sub_field('review_limit')));
$reviews_data = function_exists('hj_get_google_reviews_data') ? hj_get_google_reviews_data(['limit' => $review_limit]) : null;
$uid = uniqid('hj-rt-');

if ($subheading === '' && $title === '' && empty($reviews_data['has_content'])) {
    return;
}
?>
<section class="hj-reviews-google" id="<?php echo esc_attr($uid); ?>" aria-label="Reviews">
  <div class="hj-rt-wrap">
    <?php if ($subheading !== '' || $title !== ''): ?>
      <header class="hj-rt-header">
        <?php if ($subheading !== ''): ?>
          <p class="hj-rt-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>

        <?php if ($title !== ''): ?>
          <h2 class="hj-rt-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($reviews_data['has_summary'])): ?>
      <?php $summary_tag = !empty($reviews_data['reviews_url']) ? 'a' : 'div'; ?>
      <div class="hj-rt-summary">
        <<?php echo $summary_tag; ?> class="hj-rt-summary__card"<?php echo $summary_tag === 'a' ? ' href="' . esc_url($reviews_data['reviews_url']) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>>
          <span class="hj-rt-summary__eyebrow">Google Reviews</span>

          <div class="hj-rt-summary__score-row">
            <?php if (!empty($reviews_data['rating'])): ?>
              <strong class="hj-rt-summary__score"><?php echo esc_html(hj_google_reviews_format_rating($reviews_data['rating'])); ?></strong>
            <?php endif; ?>

            <?php if (!empty($reviews_data['stars_text'])): ?>
              <span class="hj-rt-summary__stars" aria-hidden="true"><?php echo esc_html($reviews_data['stars_text']); ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($reviews_data['place_name'])): ?>
            <span class="hj-rt-summary__place"><?php echo esc_html($reviews_data['place_name']); ?></span>
          <?php endif; ?>

          <?php if (!empty($reviews_data['reviews_count'])): ?>
            <span class="hj-rt-summary__meta"><?php echo esc_html(sprintf(_n('Based on %d review', 'Based on %d reviews', (int) $reviews_data['reviews_count'], 'hello-elementor-child'), (int) $reviews_data['reviews_count'])); ?></span>
          <?php endif; ?>
        </<?php echo $summary_tag; ?>>
      </div>
    <?php endif; ?>

    <?php if (!empty($reviews_data['reviews'])): ?>
      <div class="hj-rt-grid">
        <?php foreach ($reviews_data['reviews'] as $review): ?>
          <?php
          $review_text = trim((string) ($review['text'] ?? ''));
          $review_meta = array_filter([
            trim((string) ($review['relative_time'] ?? '')),
            !empty($reviews_data['place_name']) ? trim((string) $reviews_data['place_name']) : 'Google Reviews',
          ]);
          ?>
          <article class="hj-rt-card">
            <div class="hj-rt-card__head">
              <div class="hj-rt-card__person">
                <span class="hj-rt-card__avatar">
                  <?php if (!empty($review['author_avatar'])): ?>
                    <img src="<?php echo esc_url($review['author_avatar']); ?>" alt="<?php echo esc_attr($review['author_name'] ?? ''); ?>" loading="lazy" />
                  <?php else: ?>
                    <span class="hj-rt-card__avatar-fallback"><?php echo esc_html($review['author_initials'] ?? 'G'); ?></span>
                  <?php endif; ?>
                </span>

                <span class="hj-rt-card__identity">
                  <?php if (!empty($review['author_url'])): ?>
                    <a class="hj-rt-card__name" href="<?php echo esc_url($review['author_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($review['author_name'] ?? ''); ?></a>
                  <?php else: ?>
                    <span class="hj-rt-card__name"><?php echo esc_html($review['author_name'] ?? ''); ?></span>
                  <?php endif; ?>

                  <?php if (!empty($review_meta)): ?>
                    <span class="hj-rt-card__meta"><?php echo esc_html(implode(' • ', $review_meta)); ?></span>
                  <?php endif; ?>
                </span>
              </div>

              <?php if (!empty($review['stars_text'])): ?>
                <span class="hj-rt-card__stars" aria-hidden="true"><?php echo esc_html($review['stars_text']); ?></span>
              <?php endif; ?>
            </div>

            <?php if ($review_text !== ''): ?>
              <div class="hj-rt-card__copy"><?php echo esc_html(wp_trim_words($review_text, 40, '...')); ?></div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
