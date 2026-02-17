<?php
/*
Template Name: Price List
*/

get_header();
?>

<main id="primary" class="site-main">
    <article <?php post_class('hj-pricelist'); ?>>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php hj_render_page_modules(get_the_ID()); ?>
        <?php endwhile; endif; ?>
    </article>
</main>

<?php get_footer(); ?>
