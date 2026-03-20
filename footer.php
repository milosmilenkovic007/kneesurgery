<?php
/**
 * Custom child theme footer.
 */

if (!defined('ABSPATH')) {
    exit;
}

$top_image = function_exists('get_field') ? get_field('footer_col1_top_image', 'option') : null;
$col1_text = trim((string) (function_exists('get_field') ? get_field('footer_col1_text', 'option') : ''));
$bottom_image = function_exists('get_field') ? get_field('footer_col1_bottom_image', 'option') : null;
$social_links = function_exists('get_field') ? (get_field('footer_social_links', 'option') ?: []) : [];
$col2_content = (string) (function_exists('get_field') ? get_field('footer_col2_content', 'option') : '');
$col3_content = (string) (function_exists('get_field') ? get_field('footer_col3_content', 'option') : '');
$col4_content = (string) (function_exists('get_field') ? get_field('footer_col4_content', 'option') : '');
$bottom_left_text = trim((string) (function_exists('get_field') ? get_field('footer_bottom_left_text', 'option') : ''));
$bottom_links = function_exists('get_field') ? (get_field('footer_bottom_links', 'option') ?: []) : [];

$current_year = (string) gmdate('Y');

if ($col1_text === '') {
    $col1_text = __('Medical Tourism Agency Tursab No: 5422', 'hello-elementor-child');
}

if ($col2_content === '') {
    $col2_content = '<h4>Address</h4><p>Fener Mah. Fener Cd. No:11, Fener Is Merkezi, B2 Blok, kapi no:204 Muratpasa/Antalya/TURKIYE</p><h4>Contact</h4><p><a href="tel:+905550869112">+90 555 086 91 12</a><br><a href="mailto:info@kneesurgery.local">info@kneesurgery.local</a></p>';
}

if ($col3_content === '') {
    $col3_content = '<h4>Explore</h4><p><a href="' . esc_url(home_url('/')) . '">Home</a><br><a href="' . esc_url(home_url('/about/')) . '">About</a><br><a href="' . esc_url(home_url('/services/')) . '">Services</a><br><a href="' . esc_url(home_url('/patient-stories/')) . '">Patient Stories</a></p>';
}

if ($col4_content === '') {
    $col4_content = '<h4>Resources</h4><p><a href="' . esc_url(home_url('/terms/')) . '">Terms</a><br><a href="' . esc_url(home_url('/privacy-policy/')) . '">Privacy Policy</a><br><a href="' . esc_url(home_url('/contact/')) . '">Contact</a></p>';
}

if ($bottom_left_text === '') {
    $bottom_left_text = sprintf(__('Copyright © %s Knee Surgery Turkey', 'hello-elementor-child'), $current_year);
}

if (empty($social_links)) {
    $social_links = [
        [
            'network' => 'facebook',
            'url' => 'https://facebook.com/',
        ],
        [
            'network' => 'youtube',
            'url' => 'https://youtube.com/',
        ],
    ];
}

if (empty($bottom_links)) {
    $bottom_links = [
        [
            'link' => [
                'title' => __('Privacy Policy', 'hello-elementor-child'),
                'url' => home_url('/privacy-policy/'),
                'target' => '',
            ],
        ],
        [
            'link' => [
                'title' => __('Terms & Services', 'hello-elementor-child'),
                'url' => home_url('/terms/'),
                'target' => '',
            ],
        ],
    ];
}

$footer_icon = static function ($network) {
    $icons = [
        'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 20v-7H16l.4-3h-3V8.2c0-.9.2-1.5 1.5-1.5h1.6V4a20.3 20.3 0 0 0-2.4-.1c-2.4 0-4 1.4-4 4.1V10H8v3h2.1v7h3.3Z" fill="currentColor"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.6 7.2c-.2-.8-.8-1.4-1.6-1.6C16.6 5.2 12 5.2 12 5.2s-4.6 0-6 .4c-.8.2-1.4.8-1.6 1.6C4 8.6 4 12 4 12s0 3.4.4 4.8c.2.8.8 1.4 1.6 1.6 1.4.4 6 .4 6 .4s4.6 0 6-.4c.8-.2 1.4-.8 1.6-1.6.4-1.4.4-4.8.4-4.8s0-3.4-.4-4.8ZM10.5 15.4V8.6l5.2 3.4-5.2 3.4Z" fill="currentColor"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4.5" y="4.5" width="15" height="15" rx="4" stroke="currentColor" stroke-width="1.8" fill="none"/><circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8" fill="none"/><circle cx="17.3" cy="6.9" r="1" fill="currentColor"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 8.2a1.9 1.9 0 1 0 0-3.8 1.9 1.9 0 0 0 0 3.8ZM4.8 9.6h3.2V19H4.8V9.6Zm5.1 0H13v1.3h.1c.4-.8 1.5-1.6 3.1-1.6 3.3 0 3.9 2 3.9 4.7V19H17v-4.2c0-1 0-2.3-1.5-2.3s-1.7 1.1-1.7 2.2V19H9.9V9.6Z" fill="currentColor"/></svg>',
        'twitter' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.7 4H20l-5 5.7L21 20h-4.7l-3.7-5-4.4 5H6l5.4-6.1L3 4h4.8l3.4 4.7L17.7 4Zm-.8 14h1.3L7.1 5.8H5.7L16.9 18Z" fill="currentColor"/></svg>',
    ];

    return $icons[$network] ?? $icons['facebook'];
};

$render_image = static function ($image, $class_name) {
    if (empty($image)) {
        return '';
    }

    if (!empty($image['ID'])) {
        return wp_get_attachment_image((int) $image['ID'], 'large', false, [
            'class' => $class_name,
            'loading' => 'lazy',
            'decoding' => 'async',
        ]);
    }

    if (!empty($image['url'])) {
        return sprintf(
            '<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy" decoding="async">',
            esc_attr($class_name),
            esc_url((string) $image['url']),
            esc_attr((string) ($image['alt'] ?? ''))
        );
    }

    return '';
};
?>
<footer class="hj-site-footer" aria-label="<?php echo esc_attr__('Site footer', 'hello-elementor-child'); ?>">
    <div class="hj-site-footer__main">
        <div class="hj-site-footer__grid">
            <div class="hj-site-footer__column hj-site-footer__column--brand">
                <?php echo $render_image($top_image, 'hj-site-footer__brand-image hj-site-footer__brand-image--top'); ?>

                <p class="hj-site-footer__brand-copy"><?php echo esc_html($col1_text); ?></p>

                <?php echo $render_image($bottom_image, 'hj-site-footer__brand-image hj-site-footer__brand-image--bottom'); ?>

                <?php if (!empty($social_links)) : ?>
                    <div class="hj-site-footer__socials" aria-label="<?php echo esc_attr__('Social links', 'hello-elementor-child'); ?>">
                        <?php foreach ($social_links as $social) :
                            $network = sanitize_key($social['network'] ?? 'facebook');
                            $url = trim((string) ($social['url'] ?? ''));
                            if ($url === '') {
                                continue;
                            }
                            ?>
                            <a class="hj-site-footer__social-link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(ucfirst($network)); ?>">
                                <?php echo $footer_icon($network); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="hj-site-footer__column hj-site-footer__column--content"><?php echo wp_kses_post($col2_content); ?></div>
            <div class="hj-site-footer__column hj-site-footer__column--content"><?php echo wp_kses_post($col3_content); ?></div>
            <div class="hj-site-footer__column hj-site-footer__column--content"><?php echo wp_kses_post($col4_content); ?></div>
        </div>
    </div>

    <div class="hj-site-footer__bottom">
        <div class="hj-site-footer__bottom-inner">
            <div class="hj-site-footer__bottom-copy"><?php echo esc_html($bottom_left_text); ?></div>

            <?php if (!empty($bottom_links)) : ?>
                <nav class="hj-site-footer__bottom-nav" aria-label="<?php echo esc_attr__('Footer legal links', 'hello-elementor-child'); ?>">
                    <?php foreach ($bottom_links as $row) :
                        $link = $row['link'] ?? null;
                        $url = trim((string) ($link['url'] ?? ''));
                        $label = trim((string) ($link['title'] ?? ''));
                        $target = trim((string) ($link['target'] ?? ''));

                        if ($url === '' || $label === '') {
                            continue;
                        }
                        ?>
                        <a href="<?php echo esc_url($url); ?>"<?php echo $target !== '' ? ' target="' . esc_attr($target) . '" rel="noopener"' : ''; ?>><?php echo esc_html($label); ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>