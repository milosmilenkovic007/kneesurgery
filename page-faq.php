<?php
/*
Template Name: FAQ Page
*/

get_header();

while (have_posts()) :
    the_post();

    $faq_title = function_exists('get_field') ? trim((string) get_field('faq_page_title')) : '';
    $faq_intro = function_exists('get_field') ? trim((string) get_field('faq_page_intro')) : '';
    $search_placeholder = function_exists('get_field') ? trim((string) get_field('faq_search_placeholder')) : '';
    $results_label = function_exists('get_field') ? trim((string) get_field('faq_results_label')) : '';
    $results_all_label = function_exists('get_field') ? trim((string) get_field('faq_results_all_label')) : '';
    $empty_results_text = function_exists('get_field') ? trim((string) get_field('faq_empty_results_text')) : '';
    $faq_sections = function_exists('hj_get_faq_sections')
        ? hj_get_faq_sections(['include_uncategorized' => true])
        : [];

    if (!is_array($faq_sections) || empty($faq_sections)) {
        if (function_exists('hj_get_legacy_faq_page_sections')) {
            $faq_sections = hj_get_legacy_faq_page_sections(get_the_ID());
        } elseif (function_exists('get_field')) {
            $faq_sections = get_field('faq_sections');
        }
    }

    $faq_sections = is_array($faq_sections) ? array_values($faq_sections) : [];
    $normalized_faq_sections = [];
    $used_section_anchors = [];

    foreach ($faq_sections as $section_index => $section) {
        if (!is_array($section)) {
            continue;
        }

        $section_title = trim((string) ($section['title'] ?? ''));
        $section_items = is_array($section['items'] ?? null) ? array_values($section['items']) : [];

        if ($section_title === '' && empty($section_items)) {
            continue;
        }

        $section_anchor_base = function_exists('hj_get_faq_section_anchor_id')
            ? hj_get_faq_section_anchor_id($section, $section_index + 1)
            : sanitize_title($section_title);

        if ($section_anchor_base === '') {
            $section_anchor_base = 'faq-section-' . ($section_index + 1);
        }

        $section_anchor = $section_anchor_base;
        $anchor_suffix = 2;

        while (isset($used_section_anchors[$section_anchor])) {
            $section_anchor = $section_anchor_base . '-' . $anchor_suffix;
            $anchor_suffix++;
        }

        $used_section_anchors[$section_anchor] = true;

        $normalized_faq_sections[] = [
            'title' => $section_title,
            'items' => $section_items,
            'anchor' => $section_anchor,
        ];
    }

    $faq_sections = $normalized_faq_sections;

    $faq_title = $faq_title !== '' ? $faq_title : get_the_title();
    $search_placeholder = $search_placeholder !== '' ? $search_placeholder : __('Search questions...', 'hello-elementor-child');
    $results_label = $results_label !== '' ? $results_label : __('Found:', 'hello-elementor-child');
    $results_all_label = $results_all_label !== '' ? $results_all_label : __('all', 'hello-elementor-child');
    $empty_results_text = $empty_results_text !== '' ? $empty_results_text : __('No questions match your search.', 'hello-elementor-child');
    ?>

    <main id="primary" <?php post_class('site-main hj-faq-page'); ?>>
        <article class="hj-faq-page__article" data-hj-faq-page>
            <div class="hj-faq-page__wrap">
                <header class="hj-faq-page__hero">
                    <?php if ($faq_title !== '') : ?>
                        <h1 class="hj-faq-page__title"><?php echo esc_html($faq_title); ?></h1>
                    <?php endif; ?>

                    <?php if ($faq_intro !== '') : ?>
                        <div class="hj-faq-page__intro"><?php echo wp_kses_post(wpautop($faq_intro)); ?></div>
                    <?php endif; ?>

                    <?php if (count($faq_sections) > 1) : ?>
                        <nav class="hj-faq-page__nav" aria-label="<?php esc_attr_e('FAQ categories', 'hello-elementor-child'); ?>">
                            <div class="hj-faq-page__nav-list">
                                <?php foreach ($faq_sections as $section) : ?>
                                    <?php
                                    $section_title = trim((string) ($section['title'] ?? ''));
                                    $section_anchor = trim((string) ($section['anchor'] ?? ''));

                                    if ($section_title === '' || $section_anchor === '') {
                                        continue;
                                    }
                                    ?>
                                    <a class="hj-faq-page__nav-link" href="#<?php echo esc_attr($section_anchor); ?>">
                                        <?php echo esc_html($section_title); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </nav>
                    <?php endif; ?>

                    <div class="hj-faq-page__toolbar">
                        <label class="hj-faq-page__search" aria-label="<?php esc_attr_e('Search FAQ', 'hello-elementor-child'); ?>">
                            <input type="search" placeholder="<?php echo esc_attr($search_placeholder); ?>" data-hj-faq-search>
                        </label>

                        <div class="hj-faq-page__status">
                            <span class="hj-faq-page__status-label"><?php echo esc_html($results_label); ?></span>
                            <span data-hj-faq-results><?php echo esc_html($results_all_label); ?></span>
                        </div>
                    </div>
                </header>

                <?php if (!empty($faq_sections)) : ?>
                    <div class="hj-faq-page__sections" data-hj-faq-sections>
                        <?php foreach ($faq_sections as $section_index => $section) : ?>
                            <?php
                            $section_title = trim((string) ($section['title'] ?? ''));
                            $section_items = is_array($section['items'] ?? null) ? $section['items'] : [];
                            $section_anchor = trim((string) ($section['anchor'] ?? ''));

                            if ($section_title === '' && empty($section_items)) {
                                continue;
                            }
                            ?>
                            <section class="hj-faq-page__section" id="<?php echo esc_attr($section_anchor); ?>" data-hj-faq-section>
                                <?php if ($section_title !== '') : ?>
                                    <div class="hj-faq-page__section-head">
                                        <h2 class="hj-faq-page__section-title"><?php echo esc_html($section_title); ?></h2>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($section_items)) : ?>
                                    <div class="hj-faq-page__grid">
                                        <?php foreach ($section_items as $item_index => $item) : ?>
                                            <?php
                                            $question = trim((string) ($item['question'] ?? ''));
                                            $answer = trim((string) ($item['answer'] ?? ''));

                                            if ($question === '') {
                                                continue;
                                            }
                                            ?>
                                            <details class="hj-faq-page__item" data-hj-faq-item data-faq-search="<?php echo esc_attr(strtolower($section_title . ' ' . $question . ' ' . $answer)); ?>">
                                                <summary class="hj-faq-page__summary">
                                                    <span class="hj-faq-page__question"><?php echo esc_html($question); ?></span>
                                                    <span class="hj-faq-page__toggle" aria-hidden="true"></span>
                                                </summary>

                                                <?php if ($answer !== '') : ?>
                                                    <div class="hj-faq-page__answer">
                                                        <?php echo wp_kses_post(wpautop($answer)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </details>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <div class="hj-faq-page__empty" data-hj-faq-empty hidden>
                        <p><?php echo esc_html($empty_results_text); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <?php
endwhile;

get_footer();