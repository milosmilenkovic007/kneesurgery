<?php
/**
 * Fallback template.
 *
 * @package KneeSurgery
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main hj-default-page hj-default-page--content-only">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('hj-default-page__content hj-default-page__content--standalone'); ?>>
                <?php if (is_singular()) : ?>
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                <?php else : ?>
                    <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
                <?php endif; ?>

                <?php
                if (is_singular()) {
                    the_content();
                    wp_link_pages();
                } else {
                    the_excerpt();
                }
                ?>
            </article>
        <?php endwhile; ?>

        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <div class="hj-default-page__content hj-default-page__content--standalone">
            <p><?php esc_html_e('No content found.', 'kneesurgery'); ?></p>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();
