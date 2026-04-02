<?php
/**
 * Default archive template for blog-related archives using the page-style wrapper.
 */

get_header();

$archive_description = get_the_archive_description();
$has_content = trim(wp_strip_all_tags((string) $archive_description)) !== '';
$page_classes = ['site-main', 'hj-default-page', 'hj-blog-archive', 'hj-default-page--content-only'];
$content_classes = ['page-content', 'hj-default-page__content', 'hj-default-page__content--standalone'];
?>

<main id="primary" class="<?php echo esc_attr(implode(' ', $page_classes)); ?>">

    <?php if (apply_filters('hello_elementor_page_title', true)) : ?>
        <div class="page-header hj-default-page__header">
            <?php the_archive_title('<h1 class="entry-title">', '</h1>'); ?>
        </div>
    <?php endif; ?>

    <?php if ($has_content) : ?>
        <div class="<?php echo esc_attr(implode(' ', $content_classes)); ?>">
            <?php echo wp_kses_post($archive_description); ?>
        </div>
    <?php endif; ?>

    <section class="hj-blog-archive__listing">
        <?php if (have_posts()) : ?>
            <div class="hj-blog-archive__grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php $categories = get_the_category(); ?>
                    <article <?php post_class('hj-blog-archive-card'); ?> id="post-<?php the_ID(); ?>">
                        <a class="hj-blog-archive-card__media" href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('ortho-card', ['loading' => 'lazy', 'decoding' => 'async']); ?>
                            <?php else : ?>
                                <span class="hj-blog-archive-card__placeholder" aria-hidden="true"></span>
                            <?php endif; ?>
                        </a>

                        <div class="hj-blog-archive-card__body">
                            <?php if (!empty($categories)) : ?>
                                <span class="hj-blog-archive-card__cat"><?php echo esc_html($categories[0]->name); ?></span>
                            <?php endif; ?>

                            <h2 class="hj-blog-archive-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <p class="hj-blog-archive-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>

                            <div class="hj-blog-archive-card__meta">
                                <?php echo get_avatar(get_the_author_meta('ID'), 28, '', '', ['loading' => 'lazy', 'style' => 'border-radius:50%']); ?>
                                <span class="name"><?php the_author(); ?></span>
                                <span class="dot">•</span>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <nav class="hj-blog-archive__pagination" aria-label="<?php esc_attr_e('Pagination', 'hello-elementor-child'); ?>">
                <?php
                the_posts_pagination([
                    'mid_size' => 1,
                    'prev_text' => __('Previous', 'hello-elementor-child'),
                    'next_text' => __('Next', 'hello-elementor-child'),
                ]);
                ?>
            </nav>
        <?php else : ?>
            <div class="hj-blog-archive__empty">
                <p><?php esc_html_e('No articles found.', 'hello-elementor-child'); ?></p>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php
get_footer();