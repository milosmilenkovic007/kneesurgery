<?php
if (!defined('ABSPATH')) exit;
$author_id  = get_the_author_meta('ID');
$author_url = get_author_posts_url($author_id);
$avatar     = get_avatar($author_id, 64, '', get_the_author(), ['class'=>'ortho-author__avatar']);
$words      = str_word_count( wp_strip_all_tags( get_post_field('post_content', get_the_ID()) ) );
$read_min   = max(1, ceil($words / 220));
?>
<div class="ortho-author">
  <div class="ortho-author__meta">
    <?php echo $avatar; ?>
    <div class="ortho-author__text">
      <a class="ortho-author__name" href="<?php echo esc_url($author_url); ?>">
        <?php echo esc_html(get_the_author()); ?>
      </a>
      <div class="ortho-author__sub">
        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
        <span class="dot">•</span>
        <span><?php echo esc_html($read_min . ' ' . __('min read','hello-elementor-child')); ?></span>
      </div>
    </div>
  </div>
</div>
