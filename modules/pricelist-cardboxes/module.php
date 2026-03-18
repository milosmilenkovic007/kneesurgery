<?php
$title = trim((string) get_sub_field('title'));
$subheading = trim((string) get_sub_field('subheading'));
$background_color = sanitize_hex_color((string) get_sub_field('background_color'));
$cards = get_sub_field('cards') ?: [];
$uid = uniqid('hj-pcb-');
$section_classes = ['hj-pricelist-cardboxes'];
$section_style = '';

if ($background_color) {
  $section_classes[] = 'hj-pricelist-cardboxes--has-bg';
  $section_style = ' style="' . esc_attr('--pcb-section-bg:' . $background_color . ';') . '"';
}

$default_includes = [
  'Transfers',
  'Medical Translations',
  'Knee Implant & Medications',
  'Personal Support & Coordination',
  'Unlimited Post Op Care Private Nurse Companion (For patients without a companion)',
];

$parse_lines = static function ($value) use ($default_includes) {
  $value = is_string($value) ? trim($value) : '';
  if ($value === '') {
    return $default_includes;
  }

  $lines = preg_split('/\r\n|\r|\n/', $value);
  $items = [];

  foreach ($lines as $line) {
    $line = trim((string) $line);
    if ($line === '') {
      continue;
    }
    $items[] = preg_replace('/^[-*•]\s*/u', '', $line);
  }

  return !empty($items) ? $items : $default_includes;
};
?>
<?php if (!empty($cards)): ?>
<section class="<?php echo esc_attr(implode(' ', $section_classes)); ?>" id="<?php echo esc_attr($uid); ?>" aria-label="Pricelist Cardboxes"<?php echo $section_style; ?>>
  <div class="hj-pcb-wrap">
    <?php if ($title || $subheading): ?>
      <div class="hj-pcb-header">
        <?php if ($title): ?>
          <h2 class="hj-pcb-title hj-hd-title hj-flex-h2"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        <?php if ($subheading): ?>
          <p class="hj-pcb-subheading"><?php echo esc_html($subheading); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="hj-pcb-grid">
      <?php foreach ($cards as $card):
        $treatment_name = trim((string) ($card['treatment_name'] ?? ''));
        $package_price = trim((string) ($card['package_price'] ?? ''));
        $currency_symbol = trim((string) ($card['currency_symbol'] ?? ''));
        $includes = $parse_lines($card['package_includes'] ?? '');
        $button = $card['button'] ?? [];
        $button_text = trim((string) ($button['text'] ?? '')) ?: 'Get in touch';
        $button_url = trim((string) ($button['url'] ?? '')) ?: '/contact';

        if ($treatment_name === '') {
          continue;
        }
      ?>
        <article class="hj-pcb-card">
          <div class="hj-pcb-card__inner">
            <h3 class="hj-pcb-card__title"><?php echo esc_html($treatment_name); ?></h3>

            <div class="hj-pcb-card__includes">
              <ul class="hj-pcb-card__list">
                <?php foreach ($includes as $include): ?>
                  <li><?php echo esc_html($include); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="hj-pcb-card__footer">
              <p class="hj-pcb-card__price">
                <?php if ($currency_symbol !== ''): ?>
                  <span class="hj-pcb-card__currency"><?php echo esc_html($currency_symbol); ?></span>
                <?php endif; ?>
                <span class="hj-pcb-card__amount"><?php echo esc_html($package_price); ?></span>
              </p>
              <a href="<?php echo esc_url($button_url); ?>" class="hj-pcb-card__button"<?php echo $button_url === '#candidate' ? ' data-candidate' : ''; ?>><?php echo esc_html($button_text); ?></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>