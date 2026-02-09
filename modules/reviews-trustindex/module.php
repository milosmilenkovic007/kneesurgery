<?php
$title = get_sub_field('title');
$shortcode = trim((string) get_sub_field('shortcode'));
$uid = uniqid('hj-rt-');
?>
<section class="hj-reviews-trustindex" id="<?php echo esc_attr($uid); ?>" aria-label="Reviews">
  <div class="hj-rt-wrap">
    <?php if ($title): ?>
      <h2 class="hj-rt-title hj-cb-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>

    <?php if ($shortcode): ?>
      <div class="hj-rt-embed">
        <?php echo apply_filters('the_content', $shortcode); ?>
      </div>
    <?php endif; ?>
  </div>
</section>
