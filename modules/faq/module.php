<?php
$title = get_sub_field('title') ?: 'Frequently Asked Questions';
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

$uid = uniqid('hj-faq-');
?>
<section class="hj-faq" id="<?php echo esc_attr($uid); ?>" aria-label="FAQ">
  <div class="hj-faq-wrap">
    <?php if ($title): ?>
      <h2 class="hj-cb-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
      <div class="hj-faq-list" data-hj-faq-accordion>
        <?php foreach ($items as $i => $item):
          $question = trim((string) ($item['question'] ?? ''));
          $answer = trim((string) ($item['answer'] ?? ''));
          if (!$question) { continue; }
        ?>
          <details class="hj-faq-item" data-hj-faq-item <?php echo $i === 0 ? 'open' : ''; ?>>
            <summary>
              <span class="hj-faq-q"><?php echo esc_html($question); ?></span>
              <span class="hj-faq-icon" aria-hidden="true"></span>
            </summary>
            <?php if ($answer): ?>
              <div class="hj-faq-a">
                <?php echo wp_kses_post(wpautop($answer)); ?>
              </div>
            <?php endif; ?>
          </details>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($read_all_url !== ''): ?>
      <div class="hj-faq-actions">
        <a class="hj-faq-read-all" href="<?php echo esc_url($read_all_url); ?>" target="_blank" rel="noopener noreferrer">
          <?php echo esc_html($read_all_text); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
