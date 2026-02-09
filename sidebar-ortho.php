<?php if (!defined('ABSPATH')) exit; ?>

<?php if ( is_active_sidebar('ortho-sidebar') ) : ?>
  <?php dynamic_sidebar('ortho-sidebar'); ?>
<?php else : ?>
  <!-- Optional fallback: prikazi kratku poruku ako nema nijednog widgeta -->
  <section class="widget">
    <h3 class="widget-title"><?php _e('Sidebar', 'hello-elementor-child'); ?></h3>
    <p><?php _e('Add widgets to the Ortho Sidebar in Appearance → Widgets.', 'hello-elementor-child'); ?></p>
  </section>
<?php endif; ?>
