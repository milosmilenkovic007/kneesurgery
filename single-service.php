<?php
if (!defined('ABSPATH')) exit;

get_header();
the_post();
?>

<main id="primary" class="site-main">
  <article <?php post_class('hj-service'); ?> id="post-<?php the_ID(); ?>">
    <?php
      if (function_exists('hj_render_page_modules')) {
        hj_render_page_modules(get_the_ID());
      } else {
        the_content();
      }
    ?>
  </article>
</main>

<?php
get_footer();
