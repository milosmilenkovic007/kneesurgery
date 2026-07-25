<?php
/**
 * Reusable branding and design-system settings.
 */

if (!defined('ABSPATH')) {
    exit;
}

function hj_theme_option($name, $default = '') {
    if (!function_exists('get_field')) {
        return $default;
    }

    $value = get_field($name, 'option');
    return ($value === null || $value === false || $value === '') ? $default : $value;
}

function hj_theme_color($name, $default) {
    $value = sanitize_hex_color((string) hj_theme_option($name, $default));
    return $value ?: $default;
}

function hj_theme_font_choices() {
    return [
        'Manrope' => '"Manrope", sans-serif',
        'Inter' => '"Inter", sans-serif',
        'Roboto' => '"Roboto", sans-serif',
        'Open Sans' => '"Open Sans", sans-serif',
        'Lato' => '"Lato", sans-serif',
        'Poppins' => '"Poppins", sans-serif',
        'Source Serif 4' => '"Source Serif 4", serif',
        'System UI' => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
        'Georgia' => 'Georgia, "Times New Roman", serif',
    ];
}

function hj_theme_font_stack($name, $default = 'Manrope') {
    $choices = hj_theme_font_choices();
    return $choices[$name] ?? $choices[$default];
}

function hj_render_theme_logo($context = 'desktop') {
    $field = $context === 'mobile' ? 'mobile_logo' : 'header_logo';
    $logo = hj_theme_option($field, null);

    if ($context === 'mobile' && empty($logo)) {
        $logo = hj_theme_option('header_logo', null);
    }

    $attachment_id = is_array($logo) ? absint($logo['ID'] ?? $logo['id'] ?? 0) : absint($logo);

    if ($attachment_id) {
        printf(
            '<a class="custom-logo-link" href="%1$s" rel="home">%2$s</a>',
            esc_url(home_url('/')),
            wp_get_attachment_image($attachment_id, 'full', false, [
                'class' => 'custom-logo',
                'loading' => 'eager',
                'decoding' => 'async',
            ])
        );
        return;
    }

    if (function_exists('the_custom_logo') && has_custom_logo()) {
        the_custom_logo();
        return;
    }

    printf(
        '<a class="hj-site-header__brand-link" href="%1$s" rel="home">%2$s</a>',
        esc_url(home_url('/')),
        esc_html(get_bloginfo('name'))
    );
}

add_action('wp_enqueue_scripts', function () {
    $body_font = (string) hj_theme_option('body_font_family', 'Manrope');
    $heading_font = (string) hj_theme_option('heading_font_family', 'Manrope');
    $google_families = [];

    foreach (array_unique([$body_font, $heading_font]) as $family) {
        if (in_array($family, ['System UI', 'Georgia'], true)) {
            continue;
        }
        $google_families[] = 'family=' . rawurlencode($family) . ':wght@300;400;500;600;700;800';
    }

    if ($google_families) {
        wp_enqueue_style(
            'hj-theme-fonts',
            'https://fonts.googleapis.com/css2?' . implode('&', $google_families) . '&display=swap',
            [],
            null
        );
    }

    $variables = [
        '--hj-color-primary' => hj_theme_color('color_primary', '#4d55dd'),
        '--hj-color-secondary' => hj_theme_color('color_secondary', '#0b726d'),
        '--hj-color-accent' => hj_theme_color('color_accent', '#4d55dd'),
        '--hj-color-text' => hj_theme_color('color_text', '#333333'),
        '--hj-color-heading' => hj_theme_color('color_heading', '#22243f'),
        '--hj-color-background' => hj_theme_color('color_background', '#ffffff'),
        '--hj-font-body' => hj_theme_font_stack($body_font),
        '--hj-font-heading' => hj_theme_font_stack($heading_font),
        '--hj-header-bg' => hj_theme_color('header_background', '#f7f7ff'),
        '--hj-header-border' => hj_theme_color('header_border_color', '#d8daf7'),
        '--hj-header-text' => hj_theme_color('header_text_color', '#6f748a'),
        '--hj-header-text-strong' => hj_theme_color('header_active_color', '#22243f'),
        '--hj-header-accent' => hj_theme_color('header_button_color', '#4d55dd'),
        '--hj-footer-bg' => hj_theme_color('footer_background', '#4e56d9'),
        '--hj-footer-bg-deep' => hj_theme_color('footer_background', '#4e56d9'),
        '--hj-footer-text' => hj_theme_color('footer_text_color', '#ffffff'),
        '--hj-footer-heading' => hj_theme_color('footer_heading_color', '#ffffff'),
        '--hj-footer-link' => hj_theme_color('footer_text_color', '#ffffff'),
        '--hj-footer-link-hover' => hj_theme_color('footer_heading_color', '#ffffff'),
        '--hj-logo-width' => max(80, min(500, absint(hj_theme_option('header_logo_width', 210)))) . 'px',
        '--hj-mobile-logo-width' => max(60, min(400, absint(hj_theme_option('mobile_logo_width', 180)))) . 'px',
        '--hj-button-radius' => max(0, min(100, absint(hj_theme_option('button_border_radius', 999)))) . 'px',
    ];

    $declarations = '';
    foreach ($variables as $property => $value) {
        $declarations .= $property . ':' . $value . ';';
    }

    wp_add_inline_style(
        'hj-site-footer',
        ':root{' . $declarations . '}body{background:var(--hj-color-background);color:var(--hj-color-text);font-family:var(--hj-font-body)}h1,h2,h3,h4,h5,h6{color:var(--hj-color-heading);font-family:var(--hj-font-heading)}'
    );
}, 25);

add_action('wp_head', function () {
    $favicon = hj_theme_option('site_favicon', null);
    $url = '';

    if (is_array($favicon)) {
        $url = (string) ($favicon['sizes']['thumbnail'] ?? $favicon['url'] ?? '');
    } elseif (is_numeric($favicon)) {
        $url = (string) wp_get_attachment_image_url(absint($favicon), 'thumbnail');
    }

    if ($url !== '') {
        printf("<link rel=\"icon\" href=\"%s\">\n", esc_url($url));
    }
}, 2);
