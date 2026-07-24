<?php
$subheading = trim((string) get_sub_field('subheading'));
$title = trim((string) get_sub_field('title'));
$source = trim((string) get_sub_field('source'));
$shortcode = trim((string) get_sub_field('shortcode'));
$review_limit = (int) get_sub_field('review_limit');
$review_limit = $review_limit > 0 ? max(1, min(50, $review_limit)) : 3;
$uid = uniqid('hj-rt-');

if (!in_array($source, ['trustindex', 'google'], true)) {
    $source = 'trustindex';
}

$reviews_data = $source === 'google' && function_exists('hj_get_google_reviews_data')
    ? hj_get_google_reviews_data(['limit' => $review_limit])
    : null;

$use_google_reviews = $source === 'google' && !empty($reviews_data['has_content']);
$use_trustindex_embed = $shortcode !== '' && (!$use_google_reviews || $source === 'trustindex');
$all_reviews_url = $use_google_reviews ? trim((string) ($reviews_data['reviews_url'] ?? '')) : '';
$show_all_reviews_card = $use_google_reviews && $all_reviews_url !== '';
$rendered_reviews = $use_google_reviews && !empty($reviews_data['reviews'])
  ? array_values((array) $reviews_data['reviews'])
  : [];
$summary_has_content = $use_google_reviews && !empty($reviews_data['has_summary']);
$summary_rating = $summary_has_content && !empty($reviews_data['rating'])
  ? hj_google_reviews_format_rating($reviews_data['rating'])
  : '';
$summary_stars_text = $summary_has_content ? trim((string) ($reviews_data['stars_text'] ?? '')) : '';
$summary_place_name = $summary_has_content ? trim((string) ($reviews_data['place_name'] ?? '')) : '';
$summary_meta = $summary_has_content && !empty($reviews_data['reviews_count'])
  ? sprintf(_n('Based on %d review', 'Based on %d reviews', (int) $reviews_data['reviews_count'], 'hello-elementor-child'), (int) $reviews_data['reviews_count'])
  : '';
$summary_tag = $all_reviews_url !== '' ? 'a' : 'div';

if ($show_all_reviews_card && $review_limit > 0) {
  $review_slots = max(0, $review_limit - 1);
  $rendered_reviews = $review_slots > 0
    ? array_slice($rendered_reviews, 0, $review_slots)
    : [];
}

$review_cards = [];
foreach ($rendered_reviews as $review) {
  $author_name = trim((string) ($review['author_name'] ?? ''));
  $author_url = trim((string) ($review['author_url'] ?? ''));
  $author_avatar = trim((string) ($review['author_avatar'] ?? ''));
  $author_initials = trim((string) ($review['author_initials'] ?? ''));
  $review_meta = trim((string) ($review['relative_time'] ?? ''));
  $review_text = trim((string) ($review['text'] ?? ''));
  $name_tag = $author_url !== '' ? 'a' : 'span';

  if ($author_name === '' && $review_text === '') {
    continue;
  }

  $review_cards[] = [
    'author_name' => $author_name,
    'author_url' => $author_url,
    'author_avatar' => $author_avatar,
    'author_initials' => $author_initials,
    'review_meta' => $review_meta,
    'review_text' => $review_text,
    'name_tag' => $name_tag,
    'stars_text' => trim((string) ($review['stars_text'] ?? '')),
  ];
}

$slides_count = count($review_cards) + ($show_all_reviews_card ? 1 : 0);
$all_reviews_label = !empty($reviews_data['reviews_count'])
  ? sprintf(
    _n('See all %d Google review', 'See all %d Google reviews', (int) $reviews_data['reviews_count'], 'hello-elementor-child'),
    (int) $reviews_data['reviews_count']
  )
  : __('See all Google reviews', 'hello-elementor-child');

if ($subheading === '' && $title === '' && !$use_google_reviews && !$use_trustindex_embed) {
    return;
}

$section_classes = 'hj-reviews-trustindex';
if ($use_google_reviews) {
    $section_classes .= ' hj-reviews-google is-source-google';
}
?>
<section class="<?php echo esc_attr($section_classes); ?>" id="<?php echo esc_attr($uid); ?>" aria-label="Reviews">
  <div class="hj-rt-wrap">
    <?php if ($subheading !== '' || $title !== '' || $summary_has_content): ?>
      <header class="hj-rt-header">
        <?php if ($subheading !== ''): ?>
          <p class="hj-rt-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>

        <?php if ($title !== ''): ?>
          <h2 class="hj-rt-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>

        <?php if ($summary_has_content): ?>
          <<?php echo $summary_tag; ?> class="hj-rt-summary-inline"<?php echo $summary_tag === 'a' ? ' href="' . esc_url($all_reviews_url) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <span class="hj-rt-summary-inline__eyebrow"><?php esc_html_e('Google Reviews', 'hello-elementor-child'); ?></span>
            <div class="hj-rt-summary-inline__row">
              <?php if ($summary_rating !== ''): ?>
                <strong class="hj-rt-summary-inline__score"><?php echo esc_html($summary_rating); ?></strong>
              <?php endif; ?>

              <?php if ($summary_stars_text !== ''): ?>
                <span class="hj-rt-summary-inline__stars" aria-hidden="true"><?php echo esc_html($summary_stars_text); ?></span>
              <?php endif; ?>

              <?php if ($summary_place_name !== ''): ?>
                <span class="hj-rt-summary-inline__place"><?php echo esc_html($summary_place_name); ?></span>
              <?php endif; ?>
            </div>

            <?php if ($summary_meta !== ''): ?>
              <span class="hj-rt-summary-inline__meta"><?php echo esc_html($summary_meta); ?></span>
            <?php endif; ?>
          </<?php echo $summary_tag; ?>>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if ($use_google_reviews): ?>
      <?php if ($slides_count > 0): ?>
        <div class="hj-rt-slider" data-rt-slider>
          <div class="hj-rt-stage">
            <button class="hj-rt-arrow hj-rt-arrow--prev" type="button" data-rt-prev aria-label="<?php esc_attr_e('Previous reviews', 'hello-elementor-child'); ?>">
              <span aria-hidden="true">&larr;</span>
            </button>

            <div class="hj-rt-track" data-rt-track>
              <?php foreach ($review_cards as $review): ?>
                <div class="hj-rt-slide" data-rt-slide>
                  <article class="hj-rt-card">
                    <div class="hj-rt-card__head">
                      <div class="hj-rt-card__person">
                        <div class="hj-rt-card__avatar" aria-hidden="true">
                          <?php if ($review['author_avatar'] !== ''): ?>
                            <img src="<?php echo esc_url($review['author_avatar']); ?>" alt="" loading="lazy" decoding="async">
                          <?php else: ?>
                            <span class="hj-rt-card__avatar-fallback"><?php echo esc_html($review['author_initials'] !== '' ? $review['author_initials'] : 'G'); ?></span>
                          <?php endif; ?>
                        </div>

                        <div class="hj-rt-card__identity">
                          <<?php echo $review['name_tag']; ?> class="hj-rt-card__name"<?php echo $review['name_tag'] === 'a' ? ' href="' . esc_url($review['author_url']) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($review['author_name'] !== '' ? $review['author_name'] : __('Google user', 'hello-elementor-child')); ?></<?php echo $review['name_tag']; ?>>
                          <?php if ($review['review_meta'] !== ''): ?>
                            <span class="hj-rt-card__meta"><?php echo esc_html($review['review_meta']); ?></span>
                          <?php endif; ?>
                        </div>
                      </div>

                      <?php if ($review['stars_text'] !== ''): ?>
                        <span class="hj-rt-card__stars" aria-hidden="true"><?php echo esc_html($review['stars_text']); ?></span>
                      <?php endif; ?>
                    </div>

                    <?php if ($review['review_text'] !== ''): ?>
                      <div class="hj-rt-card__copy is-clamped" data-rt-copy><?php echo nl2br(esc_html($review['review_text'])); ?></div>
                      <button class="hj-rt-card__read-more" type="button" data-rt-read-more hidden><?php esc_html_e('Read more', 'hello-elementor-child'); ?></button>
                    <?php endif; ?>
                  </article>
                </div>
              <?php endforeach; ?>

              <?php if ($show_all_reviews_card): ?>
                <div class="hj-rt-slide" data-rt-slide>
                  <a class="hj-rt-card hj-rt-card--all-reviews" href="<?php echo esc_url($all_reviews_url); ?>" target="_blank" rel="noopener noreferrer">
                    <span class="hj-rt-card__eyebrow"><?php esc_html_e('Google Reviews', 'hello-elementor-child'); ?></span>
                    <strong class="hj-rt-card__cta-title"><?php esc_html_e('Read all reviews', 'hello-elementor-child'); ?></strong>
                    <span class="hj-rt-card__cta-copy"><?php echo esc_html($all_reviews_label); ?></span>
                  </a>
                </div>
              <?php endif; ?>
            </div>

            <button class="hj-rt-arrow hj-rt-arrow--next" type="button" data-rt-next aria-label="<?php esc_attr_e('Next reviews', 'hello-elementor-child'); ?>">
              <span aria-hidden="true">&rarr;</span>
            </button>
          </div>

          <?php if ($slides_count > 1): ?>
            <div class="hj-rt-dots" role="tablist" aria-label="<?php esc_attr_e('Reviews slides', 'hello-elementor-child'); ?>" data-rt-dots>
              <?php for ($index = 0; $index < $slides_count; $index++): ?>
                <button class="hj-rt-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-rt-dot="<?php echo esc_attr($index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Review %d', 'hello-elementor-child'), $index + 1)); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"></button>
              <?php endfor; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php elseif ($use_trustindex_embed): ?>
      <div class="hj-rt-embed">
        <?php echo apply_filters('the_content', $shortcode); ?>
      </div>
    <?php endif; ?>
  </div>
</section>
