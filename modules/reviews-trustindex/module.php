<?php
$subheading = trim((string) get_sub_field('subheading'));
$title = trim((string) get_sub_field('title'));
$source = trim((string) get_sub_field('source'));
$shortcode = trim((string) get_sub_field('shortcode'));
$review_limit = (int) get_sub_field('review_limit');
$review_limit = $review_limit > 0 ? max(1, min(5, $review_limit)) : 3;
$uid = uniqid('hj-rt-');

if (!in_array($source, ['trustindex', 'google'], true)) {
    $source = 'trustindex';
}

$reviews_data = $source === 'google' && function_exists('hj_get_google_reviews_data')
    ? hj_get_google_reviews_data(['limit' => $review_limit])
    : null;

$use_google_reviews = $source === 'google' && !empty($reviews_data['has_content']);
$use_trustindex_embed = $shortcode !== '' && (!$use_google_reviews || $source === 'trustindex');

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

    <?php if ($use_google_reviews): ?>
      <?php if (!empty($reviews_data['has_summary'])): ?>
        <?php $summary_tag = !empty($reviews_data['reviews_url']) ? 'a' : 'div'; ?>
        <div class="hj-rt-summary">
          <<?php echo $summary_tag; ?> class="hj-rt-summary__card"<?php echo $summary_tag === 'a' ? ' href="' . esc_url($reviews_data['reviews_url']) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <span class="hj-rt-summary__eyebrow"><?php esc_html_e('Google Reviews', 'hello-elementor-child'); ?></span>

            <div class="hj-rt-summary__score-row">
              <?php if (!empty($reviews_data['rating'])): ?>
                <strong class="hj-rt-summary__score"><?php echo esc_html(hj_google_reviews_format_rating($reviews_data['rating'])); ?></strong>
              <?php endif; ?>

              <?php if (!empty($reviews_data['stars_text'])): ?>
                <span class="hj-rt-summary__stars" aria-hidden="true"><?php echo esc_html($reviews_data['stars_text']); ?></span>
              <?php endif; ?>

              <?php if (!empty($reviews_data['place_name'])): ?>
                <span class="hj-rt-summary__place"><?php echo esc_html($reviews_data['place_name']); ?></span>
              <?php endif; ?>
            </div>

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
            $author_name = trim((string) ($review['author_name'] ?? ''));
            $author_url = trim((string) ($review['author_url'] ?? ''));
            $author_avatar = trim((string) ($review['author_avatar'] ?? ''));
            $author_initials = trim((string) ($review['author_initials'] ?? ''));
            $review_meta = trim((string) ($review['relative_time'] ?? ''));
            $review_text = trim((string) ($review['text'] ?? ''));
            $review_text = $review_text !== '' ? wp_trim_words($review_text, 40, '...') : '';
            $name_tag = $author_url !== '' ? 'a' : 'span';

            if ($author_name === '' && $review_text === '') {
                continue;
            }
            ?>
            <article class="hj-rt-card">
              <div class="hj-rt-card__head">
                <div class="hj-rt-card__person">
                  <div class="hj-rt-card__avatar" aria-hidden="true">
                    <?php if ($author_avatar !== ''): ?>
                      <img src="<?php echo esc_url($author_avatar); ?>" alt="" loading="lazy" decoding="async">
                    <?php else: ?>
                      <span class="hj-rt-card__avatar-fallback"><?php echo esc_html($author_initials !== '' ? $author_initials : 'G'); ?></span>
                    <?php endif; ?>
                  </div>

                  <div class="hj-rt-card__identity">
                    <<?php echo $name_tag; ?> class="hj-rt-card__name"<?php echo $name_tag === 'a' ? ' href="' . esc_url($author_url) . '" target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($author_name !== '' ? $author_name : __('Google user', 'hello-elementor-child')); ?></<?php echo $name_tag; ?>>
                    <?php if ($review_meta !== ''): ?>
                      <span class="hj-rt-card__meta"><?php echo esc_html($review_meta); ?></span>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($review['stars_text'])): ?>
                  <span class="hj-rt-card__stars" aria-hidden="true"><?php echo esc_html((string) $review['stars_text']); ?></span>
                <?php endif; ?>
              </div>

              <?php if ($review_text !== ''): ?>
                <div class="hj-rt-card__copy"><?php echo esc_html($review_text); ?></div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php elseif ($use_trustindex_embed): ?>
      <div class="hj-rt-embed">
        <?php echo apply_filters('the_content', $shortcode); ?>
      </div>
    <?php endif; ?>
  </div>
</section>
