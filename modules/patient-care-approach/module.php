<?php
if (!defined('ABSPATH')) {
    exit;
}

$anchor_id = trim((string) get_sub_field('anchor_id'));
$title = trim((string) get_sub_field('title'));
$title_accent = trim((string) get_sub_field('title_accent'));
$rows = get_sub_field('rows') ?: [];

$anchor_id = ltrim($anchor_id, '#');
$anchor_id = $anchor_id !== '' ? sanitize_html_class($anchor_id) : '';

if ($title === '' && $title_accent === '' && empty($rows)) {
    return;
}
?>
<section class="hj-patient-care-approach"<?php echo $anchor_id !== '' ? ' id="' . esc_attr($anchor_id) . '"' : ''; ?> aria-label="Patient care approach">
  <div class="hj-pca-wrap">
    <?php if ($title !== '' || $title_accent !== '') : ?>
      <header class="hj-pca-head">
        <h2 class="hj-pca-title">
          <span class="hj-pca-title__mark" aria-hidden="true"></span>
          <span class="hj-pca-title__text">
            <?php if ($title !== '') : ?><span class="hj-pca-title__main"><?php echo esc_html($title); ?></span><?php endif; ?>
            <?php if ($title_accent !== '') : ?> <span class="hj-pca-title__accent"><?php echo esc_html($title_accent); ?></span><?php endif; ?>
          </span>
        </h2>
      </header>
    <?php endif; ?>

    <?php if (!empty($rows)) : ?>
      <div class="hj-pca-rows">
        <?php foreach ($rows as $row) :
            $row_title = trim((string) ($row['title'] ?? ''));
            $row_content = (string) ($row['content'] ?? '');

            if ($row_title === '' && trim(wp_strip_all_tags($row_content)) === '') {
                continue;
            }
        ?>
          <article class="hj-pca-row">
            <div class="hj-pca-row__title-col">
              <?php if ($row_title !== '') : ?><h3 class="hj-pca-row__title"><?php echo esc_html($row_title); ?></h3><?php endif; ?>
            </div>

            <div class="hj-pca-row__content-col">
              <?php if ($row_content !== '') : ?><div class="hj-pca-row__content"><?php echo wp_kses_post($row_content); ?></div><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>