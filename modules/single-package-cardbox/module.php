<?php
static $hj_spc_phone_assets_enqueued = false;

if (!$hj_spc_phone_assets_enqueued) {
  $vendor_base_path = get_stylesheet_directory() . '/assets/vendor/intl-tel-input';
  $vendor_base_uri = get_stylesheet_directory_uri() . '/assets/vendor/intl-tel-input';

  wp_enqueue_style(
    'hj-intl-tel-input',
    $vendor_base_uri . '/css/intlTelInput.css',
    [],
    file_exists($vendor_base_path . '/css/intlTelInput.css') ? (string) filemtime($vendor_base_path . '/css/intlTelInput.css') : null
  );

  wp_enqueue_script(
    'hj-intl-tel-input',
    $vendor_base_uri . '/js/intlTelInputWithUtils.min.js',
    [],
    file_exists($vendor_base_path . '/js/intlTelInputWithUtils.min.js') ? (string) filemtime($vendor_base_path . '/js/intlTelInputWithUtils.min.js') : null,
    false
  );

  $hj_spc_phone_assets_enqueued = true;
}

$selected_treatment = get_sub_field('treatment');
$post_id = (int) $selected_treatment;

if (!$post_id && is_singular('service')) {
  $post_id = (int) get_the_ID();
}

if (!$post_id || get_post_type($post_id) !== 'service') {
  return;
}

$default_block_content = 'Many patients choose knee surgery abroad for better affordability — but the price is only one small part of the treatment journey. Long-term surgical results depend on clinical expertise, implant quality, surgical precision and structured rehabilitation follow-up. We manage the entire process to keep your treatment consistent, medically supervised and focused on long-term mobility, stability, and joint function.';

$block_heading = trim((string) get_field('package_includes_heading', $post_id));
$block_content = trim((string) get_field('package_includes_content', $post_id));
$block_content = $block_content !== '' ? $block_content : $default_block_content;
$included_title = trim((string) get_field('package_includes_included_title', $post_id));
$included_items = get_field('package_includes_included_items', $post_id) ?: [];
$price_symbol = trim((string) get_field('package_includes_price_symbol', $post_id));
$price_symbol = $price_symbol !== '' ? $price_symbol : '£';
$package_price = trim((string) get_field('package_includes_price', $post_id));
$price_note = trim((string) get_field('package_includes_price_note', $post_id));
$treatment_title = get_the_title($post_id);
$current_page_id = get_queried_object_id();
$redirect_url = $current_page_id ? get_permalink($current_page_id) : home_url('/');
$form_feedback = function_exists('hj_spc_get_form_feedback') ? hj_spc_get_form_feedback($post_id) : ['status' => '', 'message' => ''];
$form_status_class = $form_feedback['status'] !== '' ? ' hj-spc-form--' . sanitize_html_class($form_feedback['status']) : '';

$normalized_items = [];
foreach ((array) $included_items as $item) {
  $text = is_array($item) ? ($item['text'] ?? '') : $item;
  $text = trim((string) $text);
  if ($text === '') {
    continue;
  }
  $normalized_items[] = $text;
}

$has_header = $block_heading !== '' || $block_content !== '';
$has_form = true;
$has_card = !empty($normalized_items) || $package_price !== '' || $price_note !== '' || $has_form;

if (!$has_header && !$has_card) {
  return;
}

$uid = uniqid('hj-spc-');
?>
<section class="hj-single-package-cardbox" id="<?php echo esc_attr($uid); ?>" aria-label="Single Package Cardbox">
  <div class="hj-spc-wrap">
    <?php if ($has_header): ?>
      <div class="hj-spc-header">
        <?php if ($block_heading !== ''): ?>
          <h2 class="hj-spc-title hj-hd-title hj-flex-h2"><?php echo esc_html($block_heading); ?></h2>
        <?php endif; ?>
        <?php if ($block_content !== ''): ?>
          <div class="hj-spc-content"><?php echo wpautop(esc_html($block_content)); ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($has_card): ?>
      <div class="hj-spc-grid">
        <article class="hj-spc-card">
          <div class="hj-spc-card__inner">
            <?php if (!empty($normalized_items)): ?>
              <div class="hj-spc-card__includes">
                <?php if ($included_title !== ''): ?>
                  <p class="hj-spc-card__includes-title"><?php echo esc_html($included_title); ?></p>
                <?php endif; ?>

                <ul class="hj-spc-card__list">
                  <?php foreach ($normalized_items as $include): ?>
                    <li><?php echo esc_html($include); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($has_form || $package_price !== '' || $price_note !== ''): ?>
              <div class="hj-spc-card__footer">
                <div class="hj-spc-card__form<?php echo esc_attr($form_status_class); ?>">
                  <p class="hj-spc-card__form-title"><?php echo esc_html__('Book your treatment', 'hello-elementor-child'); ?></p>
                  <p class="hj-spc-card__form-text"><?php echo esc_html__('Leave your details and our team will contact you with the next steps.', 'hello-elementor-child'); ?></p>

                  <?php if (!empty($form_feedback['message'])): ?>
                    <div class="hj-spc-form__notice" role="status">
                      <?php echo esc_html($form_feedback['message']); ?>
                    </div>
                  <?php endif; ?>

                  <form class="hj-spc-form" id="hj-spc-form-<?php echo esc_attr($uid); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="hj_spc_submit_lead">
                    <input type="hidden" name="treatment_id" value="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="treatment_title" value="<?php echo esc_attr($treatment_title); ?>">
                    <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_url); ?>">
                    <?php wp_nonce_field('hj_spc_submit_lead', 'hj_spc_nonce'); ?>

                    <div class="hj-spc-form__field hj-spc-form__field--trap" aria-hidden="true">
                      <label class="screen-reader-text" for="hj-spc-company-<?php echo esc_attr($uid); ?>"><?php echo esc_html__('Company', 'hello-elementor-child'); ?></label>
                      <input id="hj-spc-company-<?php echo esc_attr($uid); ?>" type="text" name="company" tabindex="-1" autocomplete="off" placeholder="Company">
                    </div>

                    <div class="hj-spc-form__field">
                      <label class="screen-reader-text" for="hj-spc-name-<?php echo esc_attr($uid); ?>"><?php echo esc_html__('Full name', 'hello-elementor-child'); ?></label>
                      <input id="hj-spc-name-<?php echo esc_attr($uid); ?>" type="text" name="full_name" required autocomplete="name" placeholder="Full name">
                    </div>

                    <div class="hj-spc-form__field hj-spc-form__field--phone">
                      <label class="screen-reader-text" for="hj-spc-phone-<?php echo esc_attr($uid); ?>"><?php echo esc_html__('Mobile number', 'hello-elementor-child'); ?></label>
                      <input type="hidden" name="phone" value="">
                      <input type="hidden" name="country_code" value="">
                      <input id="hj-spc-phone-<?php echo esc_attr($uid); ?>" class="hj-spc-phone-input" type="tel" name="phone_display" required autocomplete="tel" placeholder="Mobile number">
                    </div>

                    <div class="hj-spc-form__field">
                      <label class="screen-reader-text" for="hj-spc-email-<?php echo esc_attr($uid); ?>"><?php echo esc_html__('Email address', 'hello-elementor-child'); ?></label>
                      <input id="hj-spc-email-<?php echo esc_attr($uid); ?>" type="email" name="email" required autocomplete="email" placeholder="Email address">
                    </div>
                  </form>

                  <?php if ($package_price !== '' || $price_note !== ''): ?>
                    <div class="hj-spc-card__price-wrap">
                      <?php if ($package_price !== ''): ?>
                        <p class="hj-spc-card__price">
                          <span class="hj-spc-card__currency"><?php echo esc_html($price_symbol); ?></span>
                          <span class="hj-spc-card__amount"><?php echo esc_html($package_price); ?></span>
                        </p>
                      <?php endif; ?>
                      <?php if ($price_note !== ''): ?>
                        <p class="hj-spc-card__price-note"><?php echo esc_html($price_note); ?></p>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <button class="hj-spc-form__submit" id="hj-spc-submit-<?php echo esc_attr($uid); ?>" type="submit" form="hj-spc-form-<?php echo esc_attr($uid); ?>" disabled><?php echo esc_html__('Book now', 'hello-elementor-child'); ?></button>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endif; ?>
  </div>
</section>
