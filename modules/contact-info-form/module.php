<?php
$heading = trim((string) get_sub_field('heading'));
$intro = trim((string) get_sub_field('intro'));
$form_shortcode = trim((string) get_sub_field('form_shortcode'));
$cards = get_sub_field('cards') ?: [];
$social_links = get_sub_field('social_links') ?: [];

$contact_icon_url = static function ($type) {
  $base_uri = get_stylesheet_directory_uri() . '/assets/img/icons/';
  $icons = [
    'phone' => $base_uri . 'phone-outgoing.svg',
    'address' => $base_uri . 'address-pin.svg',
    'whatsapp' => $base_uri . 'whatsapp.svg',
    'email' => $base_uri . 'email.svg',
  ];

  return $icons[$type] ?? '';
};

$contact_icon_svg = static function ($type) {
    $icons = [
        'phone' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.9 4.5h2.2c.4 0 .8.3.9.7l.7 3c.1.4 0 .8-.3 1.1l-1.5 1.5a15.4 15.4 0 0 0 4.4 4.4l1.5-1.5c.3-.3.7-.4 1.1-.3l3 .7c.4.1.7.5.7.9v2.2c0 .6-.4 1-.9 1.1-.6.1-1.2.2-1.8.2C10.2 19.5 4.5 13.8 4.5 6.9c0-.6.1-1.2.2-1.8.1-.5.5-.9 1.1-.9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'address' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21c3.5-4.1 5.2-7.3 5.2-9.7A5.2 5.2 0 1 0 6.8 11.3C6.8 13.7 8.5 16.9 12 21Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 13.2a2.2 2.2 0 1 0 0-4.4 2.2 2.2 0 0 0 0 4.4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 11.5a8.2 8.2 0 0 1-12 7.3L4 20l1.3-3.7A8.2 8.2 0 1 1 20 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.3 8.8c-.2.4-.6 1-.6 1.5 0 .4.2.8.4 1.2a8.1 8.1 0 0 0 3.4 3.4c.4.2.8.4 1.2.4.5 0 1.1-.4 1.5-.6.2-.1.5-.1.7 0l1 .5c.2.1.3.4.2.7-.3.9-1.2 1.6-2.2 1.6-1.3 0-2.7-.6-4.7-2.6s-2.6-3.4-2.6-4.7c0-1 .7-1.9 1.6-2.2.3-.1.6 0 .7.2l.5 1c.1.2.1.5 0 .7Z" fill="currentColor"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7.5h16v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 16.5v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="m5 8 7 5 7-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M13.4 20v-7H16l.4-3h-3V8.2c0-.9.2-1.5 1.5-1.5h1.6V4a20.3 20.3 0 0 0-2.4-.1c-2.4 0-4 1.4-4 4.1V10H8v3h2.1v7h3.3Z"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.7 4H20l-5 5.7L21 20h-4.7l-3.7-5-4.4 5H6l5.4-6.1L3 4h4.8l3.4 4.7L17.7 4Zm-.8 14h1.3L7.1 5.8H5.7L16.9 18Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M19.6 7.2c-.2-.8-.8-1.4-1.6-1.6C16.6 5.2 12 5.2 12 5.2s-4.6 0-6 .4c-.8.2-1.4.8-1.6 1.6C4 8.6 4 12 4 12s0 3.4.4 4.8c.2.8.8 1.4 1.6 1.6 1.4.4 6 .4 6 .4s4.6 0 6-.4c.8-.2 1.4-.8 1.6-1.6.4-1.4.4-4.8.4-4.8s0-3.4-.4-4.8ZM10.5 15.4V8.6l5.2 3.4-5.2 3.4Z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M6.4 8.2a1.9 1.9 0 1 0 0-3.8 1.9 1.9 0 0 0 0 3.8ZM4.8 9.6h3.2V19H4.8V9.6Zm5.1 0H13v1.3h.1c.4-.8 1.5-1.6 3.1-1.6 3.3 0 3.9 2 3.9 4.7V19H17v-4.2c0-1 0-2.3-1.5-2.3s-1.7 1.1-1.7 2.2V19H9.9V9.6Z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="4.5" y="4.5" width="15" height="15" rx="4" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8"/><circle cx="17.3" cy="6.9" r="1" fill="currentColor"/></svg>',
        'custom' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><path d="M12 8.5v4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="16.6" r="1" fill="currentColor"/></svg>',
    ];

    return $icons[$type] ?? $icons['custom'];
};

if ($heading === '' && $intro === '' && empty($cards) && $form_shortcode === '') {
    return;
}
?>
<section class="hj-contact-info-form" aria-label="Contact information and form">
  <div class="hj-cif-wrap">
    <div class="hj-cif-grid">
      <div class="hj-cif-info">
        <?php if (!empty($cards)) : ?>
          <div class="hj-cif-cards">
            <?php foreach ($cards as $card) :
                $type = sanitize_key($card['type'] ?? 'custom');
                $title = trim((string) ($card['title'] ?? ''));
                $content = trim((string) ($card['content'] ?? ''));
                if ($title === '' && $content === '') {
                    continue;
                }
            ?>
              <article class="hj-cif-card hj-cif-card--<?php echo esc_attr($type); ?>">
                <div class="hj-cif-card__head">
                  <span class="hj-cif-card__icon" aria-hidden="true">
                    <?php $card_icon_url = $contact_icon_url($type); ?>
                    <?php if ($card_icon_url !== '') : ?>
                      <img src="<?php echo esc_url($card_icon_url); ?>" alt="" loading="lazy" />
                    <?php else : ?>
                      <?php echo $contact_icon_svg($type); ?>
                    <?php endif; ?>
                  </span>
                  <?php if ($title !== '') : ?><h3 class="hj-cif-card__title"><?php echo esc_html($title); ?></h3><?php endif; ?>
                </div>
                <?php if ($content !== '') : ?>
                  <div class="hj-cif-card__content"><?php echo nl2br(esc_html($content)); ?></div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($social_links)) : ?>
          <div class="hj-cif-socials">
            <?php foreach ($social_links as $social) :
                $network = sanitize_key($social['network'] ?? 'custom');
                $url = trim((string) ($social['url'] ?? ''));
                $label = trim((string) ($social['label'] ?? '')) ?: ucfirst($network);
                if ($url === '') {
                    continue;
                }
            ?>
              <a class="hj-cif-socials__link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($label); ?>">
                <span aria-hidden="true"><?php echo $contact_icon_svg($network); ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="hj-cif-form-col">
        <?php if ($heading !== '') : ?><h2 class="hj-cif-title"><?php echo esc_html($heading); ?></h2><?php endif; ?>
        <?php if ($intro !== '') : ?><p class="hj-cif-intro"><?php echo esc_html($intro); ?></p><?php endif; ?>

        <?php if ($form_shortcode !== '') : ?>
          <div class="hj-cif-form">
            <?php echo do_shortcode($form_shortcode); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>