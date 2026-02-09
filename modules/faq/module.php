<?php
$title = get_sub_field('title') ?: 'Frequently Asked Questions';
$items = get_sub_field('items') ?: [];
$uid = uniqid('hj-faq-');
?>
<section class="hj-faq" id="<?php echo esc_attr($uid); ?>" aria-label="FAQ">
  <div class="hj-faq-wrap">
    <?php if ($title): ?>
      <h2 class="hj-cb-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
      <div class="hj-faq-list">
        <?php foreach ($items as $i => $item):
          $question = $item['question'] ?? '';
          $answer = $item['answer'] ?? '';
          if (!$question) { continue; }
        ?>
          <details class="hj-faq-item" <?php echo $i === 0 ? 'open' : ''; ?>>
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
  </div>
</section>
