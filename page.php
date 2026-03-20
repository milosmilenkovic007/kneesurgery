<?php
/**
 * Default page template override for regular WP pages.
 */

get_header();

while (have_posts()) :
    the_post();

    $has_modules = function_exists('have_rows') && have_rows('modules', get_the_ID());
    $content = get_the_content();
    $has_content = trim(wp_strip_all_tags((string) $content)) !== '';
    $page_classes = ['site-main', 'hj-default-page'];

    if (!$has_modules) {
        $page_classes[] = 'hj-default-page--content-only';
    }

    $content_classes = ['page-content', 'hj-default-page__content'];

    if (!$has_modules) {
        $content_classes[] = 'hj-default-page__content--standalone';
    }
    ?>

<main id="primary" <?php post_class($page_classes); ?>>

    <?php if (!$has_modules && apply_filters('hello_elementor_page_title', true)) : ?>
        <div class="page-header hj-default-page__header">
            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
        </div>
    <?php endif; ?>

    <?php if ($has_modules) : ?>
        <article class="hj-default-page__modules">
            <?php hj_render_page_modules(get_the_ID()); ?>
        </article>
    <?php endif; ?>

    <?php if ($has_content || !$has_modules) : ?>
        <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
            <?php the_content(); ?>
            <?php wp_link_pages(); ?>
        </div>
    <?php endif; ?>

    <?php comments_template(); ?>

</main>

    <?php
endwhile;

get_footer();