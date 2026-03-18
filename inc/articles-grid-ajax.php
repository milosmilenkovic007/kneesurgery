<?php
/**
 * Articles Grid: shared helpers and AJAX filtering.
 */

if (!function_exists('hj_ag_normalize_ids')) {
    function hj_ag_normalize_ids($ids): array {
        if (!is_array($ids)) { $ids = (array) $ids; }
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function ($v) { return $v > 0; });
        return array_values(array_unique($ids));
    }
}

if (!function_exists('hj_ag_get_posts_filtered')) {
    function hj_ag_get_posts_filtered(string $mode, $terms, $pick, int $count, int $filter_term = 0): array {
        $count = max(1, min(24, $count));

        $args = [
            'post_type'           => 'post',
            'posts_per_page'      => $count,
            'ignore_sticky_posts' => true,
        ];

        $tax_terms  = [];
        $base_terms = hj_ag_normalize_ids($terms);

        if ($filter_term > 0) {
            $tax_terms[] = $filter_term;
        } elseif (!empty($base_terms)) {
            $tax_terms = $base_terms;
        }

        if (!empty($tax_terms)) {
            $args['tax_query'] = [[
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $tax_terms,
            ]];
        }

        if ($mode === 'manual') {
            $picked = hj_ag_normalize_ids($pick);
            if (!empty($picked)) {
                $args['post__in'] = $picked;
                $args['orderby']  = 'post__in';
            } else {
                $args['post__in'] = [0];
            }
        }

        $q = new WP_Query($args);
        return $q->posts;
    }
}

if (!function_exists('hj_ag_render_cards')) {
    function hj_ag_render_cards(array $posts): string {
        if (empty($posts)) { return ''; }
        ob_start();
        foreach ($posts as $p) {
            setup_postdata($p);
            $cats = get_the_category($p->ID);
            ?>
            <li class="hj-ag-card">
              <a class="hj-ag-card__link" href="<?php echo esc_url(get_permalink($p)); ?>">
                <figure class="hj-ag-card__media">
                  <?php
                    $thumb = get_the_post_thumbnail($p, 'large', ['class' => 'img', 'alt' => esc_attr(get_the_title($p))]);
                    if ($thumb) {
                      echo $thumb;
                    } else {
                      ?>
                      <span class="hj-ag-card__media-fallback" aria-hidden="true"></span>
                      <?php
                    }
                  ?>
                </figure>
                <div class="hj-ag-card__content">
                  <div class="hj-ag-card__meta">
                    <span class="hj-ag-card__author-ico" aria-hidden="true">
                      <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/author-icon.svg'); ?>" alt="">
                    </span>
                    <span class="hj-ag-card__author"><?php echo esc_html(get_the_author_meta('display_name', $p->post_author)); ?></span>
                    <span class="hj-ag-card__date"><?php echo esc_html(get_the_date('j M, Y', $p)); ?></span>
                  </div>
                  <h3 class="hj-ag-card__title"><?php echo esc_html(get_the_title($p)); ?></h3>
                  <p class="hj-ag-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt($p), 24)); ?></p>
                  <?php if (!empty($cats)): $c = $cats[0]; ?>
                    <span class="hj-ag-card__tag"><?php echo esc_html($c->name); ?></span>
                  <?php endif; ?>
                  <span class="hj-ag-card__button">Learn More</span>
                </div>
              </a>
            </li>
            <?php
        }
        wp_reset_postdata();
        return (string) ob_get_clean();
    }
}

if (!function_exists('hj_ag_ajax_filter')) {
    function hj_ag_ajax_filter() {
        check_ajax_referer('hj_ag_filter', 'nonce');

        $config_raw = isset($_POST['config']) ? wp_unslash($_POST['config']) : '{}';
        $config     = json_decode($config_raw, true);
        if (!is_array($config)) {
            wp_send_json_error(['message' => 'Invalid config']);
        }

        $mode  = ($config['mode'] ?? 'category') === 'manual' ? 'manual' : 'category';
        $terms = $config['terms'] ?? [];
        $pick  = $config['pick'] ?? [];
        $count = isset($config['count']) ? (int) $config['count'] : 3;
        $term  = isset($_POST['term']) ? (int) $_POST['term'] : 0;

        $posts = hj_ag_get_posts_filtered($mode, $terms, $pick, $count, $term);
        $html  = hj_ag_render_cards($posts);

        wp_send_json_success(['html' => $html]);
    }

    add_action('wp_ajax_hj_ag_filter', 'hj_ag_ajax_filter');
    add_action('wp_ajax_nopriv_hj_ag_filter', 'hj_ag_ajax_filter');
}
