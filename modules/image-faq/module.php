<?php
$eyebrow = trim((string) get_sub_field('eyebrow'));
$title = trim((string) get_sub_field('title'));
$subtitle = trim((string) get_sub_field('subtitle'));
$image = get_sub_field('image');
$source = (string) get_sub_field('source');
$selected_faqs = get_sub_field('selected_faqs') ?: [];
$faq_category = get_sub_field('faq_category');
$items_limit = (int) get_sub_field('items_limit');
$read_all_url = trim((string) get_sub_field('read_all_url'));
$read_all_text = trim((string) get_sub_field('read_all_text'));
$legacy_items = get_sub_field('items') ?: [];
$items = function_exists('hj_resolve_faq_module_items')
  ? hj_resolve_faq_module_items([
      'source' => $source,
      'selected_ids' => $selected_faqs,
      'term_ids' => $faq_category,
      'limit' => $items_limit,
      'legacy_items' => $legacy_items,
    ])
  : $legacy_items;

if (function_exists('hj_get_faq_module_read_more_url')) {
  $read_all_url = (string) hj_get_faq_module_read_more_url([
    'custom_url' => $read_all_url,
    'source' => $source,
    'term_ids' => $faq_category,
  ]);
} elseif ($read_all_url === '' && function_exists('hj_get_main_faq_page_url')) {
  $read_all_url = (string) hj_get_main_faq_page_url();
}

if ($read_all_text === '') {
  $read_all_text = __('Read more', 'hello-elementor-child');
}

$uid = uniqid('hj-ifaq-');
$arrow_down_icon = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-down.svg';
$arrow_up_icon = get_stylesheet_directory_uri() . '/assets/img/icons/arrow-up.svg';

$image_id = is_array($image) ? (int) ($image['ID'] ?? 0) : 0;
$image_url = is_array($image) ? ($image['url'] ?? '') : '';
$image_alt = is_array($image) ? trim((string) ($image['alt'] ?? '')) : '';

if ($image_alt === '' && is_array($image)) {
  $image_alt = trim((string) ($image['title'] ?? ''));
}
?>
<section class="hj-image-faq" id="<?php echo esc_attr($uid); ?>" aria-label="Image and FAQ">
  <div class="hj-ifaq-wrap">
    <div class="hj-ifaq-header">
      <?php if ($eyebrow !== ''): ?>
        <p class="hj-ifaq-eyebrow"><?php echo esc_html($eyebrow); ?></p>
      <?php endif; ?>

      <?php if ($title !== ''): ?>
        <h2 class="hj-ifaq-title"><?php echo esc_html($title); ?></h2>
      <?php endif; ?>

      <?php if ($subtitle !== ''): ?>
        <p class="hj-ifaq-subtitle"><?php echo esc_html($subtitle); ?></p>
      <?php endif; ?>
    </div>

    <div class="hj-ifaq-grid">
      <?php if ($image_url): ?>
        <div class="hj-ifaq-media">
          <?php if ($image_id): ?>
            <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'hj-ifaq-image', 'loading' => 'lazy', 'decoding' => 'async']); ?>
          <?php else: ?>
            <img class="hj-ifaq-image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy" decoding="async">
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($items) || $read_all_url !== ''): ?>
        <div class="hj-ifaq-content">
          <?php if (!empty($items)): ?>
            <div class="hj-ifaq-list" data-image-faq-accordion>
              <?php foreach ($items as $index => $item):
                $question = trim((string) ($item['question'] ?? ''));
                $answer = trim((string) ($item['answer'] ?? ''));
                if ($question === '') { continue; }
              ?>
                <details class="hj-ifaq-item" <?php echo $index === 0 ? 'open' : ''; ?>>
                  <summary>
                    <span class="hj-ifaq-question"><?php echo esc_html($question); ?></span>
                    <span class="hj-ifaq-icon" aria-hidden="true">
                      <img class="hj-ifaq-icon__img hj-ifaq-icon__img--closed" src="<?php echo esc_url($arrow_down_icon); ?>" alt="" loading="lazy" decoding="async">
                      <img class="hj-ifaq-icon__img hj-ifaq-icon__img--open" src="<?php echo esc_url($arrow_up_icon); ?>" alt="" loading="lazy" decoding="async">
                    </span>
                  </summary>
                  <?php if ($answer !== ''): ?>
                    <div class="hj-ifaq-answer">
                      <div class="hj-ifaq-answer__inner">
                        <?php echo wp_kses_post(wpautop($answer)); ?>
                      </div>
                    </div>
                  <?php endif; ?>
                </details>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($read_all_url !== ''): ?>
            <div class="hj-ifaq-actions">
              <a class="hj-ifaq-read-all" href="<?php echo esc_url($read_all_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($read_all_text); ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>