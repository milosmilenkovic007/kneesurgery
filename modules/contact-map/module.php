<?php
$embed_code = (string) get_sub_field('embed_code');
$height = (int) get_sub_field('height');
$mobile_height = (int) get_sub_field('mobile_height');

if (trim($embed_code) === '') {
    return;
}

$height = $height > 0 ? $height : 720;
$mobile_height = $mobile_height > 0 ? $mobile_height : 420;

$allowed_html = wp_kses_allowed_html('post');
$allowed_html['iframe'] = [
    'src' => true,
    'width' => true,
    'height' => true,
    'style' => true,
    'allowfullscreen' => true,
    'loading' => true,
    'referrerpolicy' => true,
    'title' => true,
    'allow' => true,
];
?>
<section class="hj-contact-map" style="--hj-contact-map-height:<?php echo esc_attr((string) $height); ?>px;--hj-contact-map-mobile-height:<?php echo esc_attr((string) $mobile_height); ?>px;" aria-label="Contact map">
  <div class="hj-contact-map__embed">
    <?php echo wp_kses($embed_code, $allowed_html); ?>
  </div>
</section>