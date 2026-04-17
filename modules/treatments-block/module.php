<?php
$title = trim((string) get_sub_field('title'));
$subtitle = trim((string) get_sub_field('subtitle'));
$sections = get_sub_field('sections') ?: [];
$uid = uniqid('hj-tb-');

$prepared_sections = [];

foreach ($sections as $index => $section) {
    $heading = trim((string) ($section['heading'] ?? ''));
    $subheading = trim((string) ($section['subheading'] ?? ''));
    $source = ($section['source'] ?? 'category') === 'manual' ? 'manual' : 'category';
    $display_mode = ($section['display_mode'] ?? 'grid') === 'slider' ? 'slider' : 'grid';
    $slides_desktop = max(1, min(4, (int) ($section['slides_desktop'] ?? 3)));
    $slides_tablet = max(1, min(3, (int) ($section['slides_tablet'] ?? 2)));
    $slides_mobile = max(1, min(2, (int) ($section['slides_mobile'] ?? 1)));
    $limit = max(1, min(18, (int) ($section['items_limit'] ?? 6)));
    $enable_accordion = !empty($section['enable_accordion']);
    $start_collapsed = $enable_accordion && !empty($section['start_collapsed']);
    $post_ids = [];

    if ($source === 'manual') {
      $manual_ids = [];
      foreach ((array) ($section['manual_treatments'] ?? []) as $manual_item) {
        if (is_object($manual_item) && !empty($manual_item->ID)) {
          $manual_ids[] = (int) $manual_item->ID;
        } elseif (is_array($manual_item) && !empty($manual_item['ID'])) {
          $manual_ids[] = (int) $manual_item['ID'];
        } else {
          $manual_ids[] = (int) $manual_item;
        }
      }
      $post_ids = array_slice(array_values(array_filter($manual_ids)), 0, $limit);
    } else {
        $category_id = (int) ($section['category'] ?? 0);
        if ($category_id) {
        $post_ids = get_posts([
                'post_type' => 'service',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
          'fields' => 'ids',
          'orderby' => 'date',
          'order' => 'DESC',
          'suppress_filters' => true,
                'tax_query' => [[
                    'taxonomy' => 'treatment_category',
                    'field' => 'term_id',
                    'terms' => [$category_id],
                ]],
            ]);
        }
    }

    if (!$heading && !$subheading && empty($post_ids)) {
        continue;
    }

    $items = [];
    foreach ($post_ids as $treatment_id) {
      $treatment_id = (int) $treatment_id;
      $permalink = get_permalink($treatment_id);
      $card_title = get_the_title($treatment_id);
        if (!$permalink || !$card_title) {
            continue;
        }

      $raw_excerpt = trim((string) get_post_field('post_excerpt', $treatment_id));
      $raw_content = trim((string) get_post_field('post_content', $treatment_id));
      $excerpt_source = $raw_excerpt !== '' ? $raw_excerpt : wp_strip_all_tags(strip_shortcodes($raw_content));
      $thumbnail_id = (int) get_post_thumbnail_id($treatment_id);

        $items[] = [
            'title' => $card_title,
        'excerpt' => wp_trim_words($excerpt_source, 24, '...'),
            'permalink' => $permalink,
        'image' => $thumbnail_id ? wp_get_attachment_image($thumbnail_id, 'large', false, [
                'loading' => 'lazy',
                'decoding' => 'async',
                'class' => 'hj-tb-card__img',
        ]) : '',
        ];
    }

    $prepared_sections[] = [
        'id' => $uid . '-section-' . ($index + 1),
        'heading' => $heading,
        'subheading' => $subheading,
        'display_mode' => $display_mode,
        'slides_desktop' => $slides_desktop,
        'slides_tablet' => $slides_tablet,
        'slides_mobile' => $slides_mobile,
        'enable_accordion' => $enable_accordion,
        'start_collapsed' => $start_collapsed,
        'items' => $items,
    ];
}
?>
<section class="hj-treatments-block" id="<?php echo esc_attr($uid); ?>" aria-label="Treatments Block">
  <div class="hj-tb-wrap">
    <?php if ($title || $subtitle): ?>
      <header class="hj-tb-header">
        <?php if ($title): ?>
          <h2 class="hj-tb-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if ($subtitle): ?>
          <p class="hj-tb-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
      </header>
    <?php endif; ?>

    <?php if (!empty($prepared_sections)): ?>
      <div class="hj-tb-sections">
        <?php foreach ($prepared_sections as $section): ?>
          <section class="hj-tb-section<?php echo $section['start_collapsed'] ? ' is-collapsed' : ''; ?><?php echo $section['enable_accordion'] ? ' is-accordion' : ''; ?>" aria-labelledby="<?php echo esc_attr($section['id']); ?>-title">
            <div class="hj-tb-section__shell">
              <?php if ($section['enable_accordion']): ?>
                <button
                  class="hj-tb-section__toggle"
                  type="button"
                  aria-expanded="<?php echo $section['start_collapsed'] ? 'false' : 'true'; ?>"
                  aria-controls="<?php echo esc_attr($section['id']); ?>-panel"
                >
                  <span class="hj-tb-section__icon" aria-hidden="true"></span>
                  <span class="hj-tb-section__intro">
                    <?php if ($section['heading']): ?>
                      <span class="hj-tb-section__title" id="<?php echo esc_attr($section['id']); ?>-title"><?php echo esc_html($section['heading']); ?></span>
                    <?php endif; ?>
                    <?php if ($section['subheading']): ?>
                      <span class="hj-tb-section__subheading"><?php echo esc_html($section['subheading']); ?></span>
                    <?php endif; ?>
                  </span>
                </button>
              <?php else: ?>
                <div class="hj-tb-section__heading-block">
                  <?php if ($section['heading']): ?>
                    <h3 class="hj-tb-section__title" id="<?php echo esc_attr($section['id']); ?>-title"><?php echo esc_html($section['heading']); ?></h3>
                  <?php endif; ?>
                  <?php if ($section['subheading']): ?>
                    <p class="hj-tb-section__subheading"><?php echo esc_html($section['subheading']); ?></p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="hj-tb-section__panel" id="<?php echo esc_attr($section['id']); ?>-panel"<?php echo $section['start_collapsed'] ? ' hidden' : ''; ?>>
                <?php if (!empty($section['items'])): ?>
                  <?php if ($section['display_mode'] === 'slider'): ?>
                    <div
                      class="hj-tb-slider"
                      data-tb-slider
                      style="--tb-slides-desktop: <?php echo esc_attr((string) $section['slides_desktop']); ?>; --tb-slides-tablet: <?php echo esc_attr((string) $section['slides_tablet']); ?>; --tb-slides-mobile: <?php echo esc_attr((string) $section['slides_mobile']); ?>;"
                    >
                      <div class="hj-tb-slider__viewport" data-tb-track>
                        <?php foreach ($section['items'] as $slide_index => $item): ?>
                          <article class="hj-tb-card hj-tb-slide" data-tb-slide>
                            <a class="hj-tb-card__link" href="<?php echo esc_url($item['permalink']); ?>">
                              <div class="hj-tb-card__media<?php echo $item['image'] ? '' : ' is-empty'; ?>">
                                <?php if ($item['image']): ?>
                                  <?php echo $item['image']; ?>
                                <?php else: ?>
                                  <span class="hj-tb-card__media-fallback"></span>
                                <?php endif; ?>
                              </div>
                              <div class="hj-tb-card__content">
                                <h4 class="hj-tb-card__title"><?php echo esc_html($item['title']); ?></h4>
                                <?php if ($item['excerpt']): ?>
                                  <p class="hj-tb-card__excerpt"><?php echo esc_html($item['excerpt']); ?></p>
                                <?php endif; ?>
                                <span class="hj-tb-card__button">Learn More</span>
                              </div>
                            </a>
                          </article>
                        <?php endforeach; ?>
                      </div>

                      <?php if (count($section['items']) > 1): ?>
                        <div class="hj-tb-slider__controls" data-tb-controls>
                          <div class="hj-tb-slider__hint" data-tb-hint>
                            <span class="hj-tb-slider__hint-icon" aria-hidden="true"></span>
                            <span>Swipe to explore</span>
                          </div>

                          <div class="hj-tb-slider__dots" aria-label="Slider pagination">
                            <?php foreach ($section['items'] as $slide_index => $item): ?>
                              <button
                                class="hj-tb-slider__dot<?php echo $slide_index === 0 ? ' is-active' : ''; ?>"
                                type="button"
                                data-tb-dot="<?php echo esc_attr((string) $slide_index); ?>"
                                aria-label="Go to slide <?php echo esc_attr((string) ($slide_index + 1)); ?>"
                                aria-pressed="<?php echo $slide_index === 0 ? 'true' : 'false'; ?>"
                              ></button>
                            <?php endforeach; ?>
                          </div>

                          <div class="hj-tb-slider__arrows">
                            <button class="hj-tb-slider__arrow hj-tb-slider__arrow--prev" type="button" data-tb-prev aria-label="Previous slide" disabled>
                              <span class="hj-tb-slider__arrow-icon" aria-hidden="true"></span>
                            </button>
                            <button class="hj-tb-slider__arrow hj-tb-slider__arrow--next" type="button" data-tb-next aria-label="Next slide">
                              <span class="hj-tb-slider__arrow-icon" aria-hidden="true"></span>
                            </button>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <div class="hj-tb-grid">
                      <?php foreach ($section['items'] as $item): ?>
                        <article class="hj-tb-card">
                          <a class="hj-tb-card__link" href="<?php echo esc_url($item['permalink']); ?>">
                            <div class="hj-tb-card__media<?php echo $item['image'] ? '' : ' is-empty'; ?>">
                              <?php if ($item['image']): ?>
                                <?php echo $item['image']; ?>
                              <?php else: ?>
                                <span class="hj-tb-card__media-fallback"></span>
                              <?php endif; ?>
                            </div>
                            <div class="hj-tb-card__content">
                              <h4 class="hj-tb-card__title"><?php echo esc_html($item['title']); ?></h4>
                              <?php if ($item['excerpt']): ?>
                                <p class="hj-tb-card__excerpt"><?php echo esc_html($item['excerpt']); ?></p>
                              <?php endif; ?>
                              <span class="hj-tb-card__button">Learn More</span>
                            </div>
                          </a>
                        </article>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="hj-tb-empty">No treatments found for this section.</div>
                <?php endif; ?>
              </div>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
