<?php
/**
 * Custom child theme header.
 */

if (!defined('ABSPATH')) {
    exit;
}

$viewport_content = apply_filters('hello_elementor_viewport_content', 'width=device-width, initial-scale=1');
$enable_skip_link = apply_filters('hello_elementor_enable_skip_link', true);
$skip_link_url = apply_filters('hello_elementor_skip_link_url', '#content');

$header_cta = function_exists('hj_get_header_cta') ? hj_get_header_cta() : [
    'label' => __('Let\'s Get in Touch', 'hello-elementor-child'),
    'url' => home_url('/contact/'),
    'target' => '',
];

$header_cta_label = trim((string) ($header_cta['label'] ?? ''));
$header_cta_url = trim((string) ($header_cta['url'] ?? ''));
$header_cta_target = trim((string) ($header_cta['target'] ?? ''));
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="<?php echo esc_attr($viewport_content); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ($enable_skip_link) : ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url($skip_link_url); ?>"><?php echo esc_html__('Skip to content', 'hello-elementor'); ?></a>
<?php endif; ?>

<header class="hj-site-header" data-hj-header>
    <div class="hj-site-header__inner">
        <div class="hj-site-header__brand-block">
            <div class="hj-site-header__brand">
                <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a class="hj-site-header__brand-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <span class="hj-site-header__divider" aria-hidden="true"></span>
        </div>

        <nav class="hj-site-header__nav hj-site-header__nav--desktop" aria-label="<?php echo esc_attr__('Primary menu', 'hello-elementor-child'); ?>">
            <?php
            wp_nav_menu([
                'theme_location' => 'menu-1',
                'container' => false,
                'menu_class' => 'hj-site-header__menu',
                'fallback_cb' => false,
                'depth' => 3,
            ]);
            ?>
        </nav>

        <div class="hj-site-header__actions">
            <?php if ($header_cta_label !== '' && $header_cta_url !== '') : ?>
                <a class="hj-site-header__cta" href="<?php echo esc_url($header_cta_url); ?>"<?php echo $header_cta_target !== '' ? ' target="' . esc_attr($header_cta_target) . '" rel="noopener"' : ''; ?>>
                    <?php echo esc_html($header_cta_label); ?>
                </a>
            <?php endif; ?>

            <button class="hj-site-header__toggle" type="button" aria-expanded="false" aria-controls="hj-mobile-menu" data-hj-header-toggle>
                <span class="screen-reader-text"><?php echo esc_html__('Open menu', 'hello-elementor-child'); ?></span>
                <span class="hj-site-header__toggle-lines" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </div>

    <div class="hj-site-header__mobile-shell" id="hj-mobile-menu" hidden data-hj-header-panel>
        <button class="hj-site-header__overlay" type="button" aria-label="<?php echo esc_attr__('Close menu', 'hello-elementor-child'); ?>" data-hj-header-close></button>

        <div class="hj-site-header__mobile-panel">
            <div class="hj-site-header__mobile-top">
                <div class="hj-site-header__mobile-brand">
                    <?php if (function_exists('the_custom_logo') && has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <a class="hj-site-header__brand-link" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <button class="hj-site-header__mobile-close" type="button" aria-label="<?php echo esc_attr__('Close menu', 'hello-elementor-child'); ?>" data-hj-header-close>
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <nav class="hj-site-header__nav hj-site-header__nav--mobile" aria-label="<?php echo esc_attr__('Mobile menu', 'hello-elementor-child'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'menu-1',
                    'container' => false,
                    'menu_class' => 'hj-site-header__mobile-menu',
                    'fallback_cb' => false,
                    'depth' => 3,
                ]);
                ?>
            </nav>

            <?php if ($header_cta_label !== '' && $header_cta_url !== '') : ?>
                <a class="hj-site-header__cta hj-site-header__cta--mobile" href="<?php echo esc_url($header_cta_url); ?>"<?php echo $header_cta_target !== '' ? ' target="' . esc_attr($header_cta_target) . '" rel="noopener"' : ''; ?>>
                    <?php echo esc_html($header_cta_label); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
