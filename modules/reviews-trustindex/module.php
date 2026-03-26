<?php
$subheading = trim((string) get_sub_field('subheading'));
$title = trim((string) get_sub_field('title'));
$shortcode = trim((string) get_sub_field('shortcode'));
$uid = uniqid('hj-rt-');

if ($subheading === '' && $title === '' && $shortcode === '') {
    return;
}
?>
<section class="hj-reviews-trustindex" id="<?php echo esc_attr($uid); ?>" aria-label="Reviews">
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

    <?php if ($shortcode !== ''): ?>
      <div class="hj-rt-embed">
        <?php echo apply_filters('the_content', $shortcode); ?>
      </div>
    <?php endif; ?>
  </div>
</section>
