<?php
$image = get_sub_field('image');
$heading = trim((string) get_sub_field('heading'));
$heading_accent = trim((string) get_sub_field('heading_accent'));
$subheading = trim((string) get_sub_field('subheading'));
$form_id = get_sub_field('fluent_form_id');
$uid = uniqid('hj-cfb-');

$image_url = is_array($image) ? ($image['url'] ?? '') : '';
$image_alt = is_array($image) ? ($image['alt'] ?? '') : '';
if (!$image_alt && is_array($image)) { $image_alt = $image['title'] ?? ''; }
?>
<section class="hj-cta-form-block" id="<?php echo esc_attr($uid); ?>" aria-label="CTA">
  <div class="hj-cfb-wrap">
    <div class="hj-cfb-grid">
      <div class="hj-cfb-media" aria-hidden="true">
        <?php if ($image_url): ?>
          <img class="hj-cfb-img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>" loading="lazy" />
        <?php endif; ?>
      </div>

      <div class="hj-cfb-content">
        <?php if ($heading || $heading_accent): ?>
          <h2 class="hj-cb-title hj-cfb-title">
            <span class="hj-cfb-title-mark" aria-hidden="true"></span>
            <span class="hj-cfb-title-text">
              <?php if ($heading): ?><span class="hj-cfb-title-main"><?php echo esc_html($heading); ?></span><?php endif; ?>
              <?php if ($heading_accent): ?> <span class="hj-cfb-title-accent"><?php echo esc_html($heading_accent); ?></span><?php endif; ?>
            </span>
          </h2>
        <?php endif; ?>

        <?php if ($subheading): ?>
          <p class="hj-cfb-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>

        <?php if ($form_id): ?>
          <div class="hj-cfb-form">
            <?php echo do_shortcode('[fluentform id="' . esc_attr($form_id) . '"]'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
