<?php
/*
Template Name: Thank You Page
*/

get_header();

while (have_posts()) :
    the_post();

    $phone_display = '+90 555 087 91 12';
    $phone_href = 'tel:+905550879112';
    $whatsapp_href = 'https://wa.me/905550879112';
    ?>

<main id="primary" class="site-main hj-thank-you-page">
  <section class="hj-thank-you">
    <div class="hj-thank-you__wrap">
      <div class="hj-thank-you__message">
        <div class="hj-thank-you__content">
          <?php the_content(); ?>
        </div>
      </div>

      <section class="hj-thank-you__card" aria-label="<?php esc_attr_e('Direct contact options', 'hello-elementor-child'); ?>">
        <h2 class="hj-thank-you__card-title"><?php esc_html_e('Need to talk now?', 'hello-elementor-child'); ?></h2>
        <p class="hj-thank-you__card-copy"><?php esc_html_e('You can also reach us directly on WhatsApp:', 'hello-elementor-child'); ?></p>
        <p class="hj-thank-you__card-phone"><?php echo esc_html($phone_display); ?></p>

        <div class="hj-thank-you__actions">
          <a class="hj-thank-you__btn hj-thank-you__btn--primary" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener">
            <?php esc_html_e('Message us now', 'hello-elementor-child'); ?>
          </a>

          <a class="hj-thank-you__btn hj-thank-you__btn--secondary" href="<?php echo esc_url($phone_href); ?>">
            <?php esc_html_e('Call us', 'hello-elementor-child'); ?>
          </a>
        </div>
      </section>
    </div>
  </section>
</main>

    <?php
endwhile;

get_footer();