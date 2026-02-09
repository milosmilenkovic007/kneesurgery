<?php
$title = get_sub_field('title');
$mode  = get_sub_field('mode') ?: 'category';
$terms = get_sub_field('category'); // ids
$pick  = get_sub_field('posts'); // ids
$filters = get_sub_field('filter_categories'); // ids for filter buttons
$count = (int) (get_sub_field('count') ?: 3);
$columns = (int) (get_sub_field('columns') ?: 3);
$columns = max(1, min(6, $columns));
$columns_md = (int) (get_sub_field('columns_tablet') ?: 2);
$columns_md = max(1, min(4, $columns_md));
$columns_sm = (int) (get_sub_field('columns_mobile') ?: 1);
$columns_sm = max(1, min(2, $columns_sm));
$cta   = get_sub_field('cta');

$uid = uniqid('hj-ag-');

$filter_ids = array_slice(hj_ag_normalize_ids($filters), 0, 3);
$filter_terms = [];
foreach ($filter_ids as $tid) {
  $term_obj = get_term($tid, 'category');
  if ($term_obj && !is_wp_error($term_obj)) { $filter_terms[] = $term_obj; }
}

// Front-end script for AJAX filtering
if (!wp_script_is('hj-articles-grid', 'enqueued')) {
  wp_enqueue_script(
    'hj-articles-grid',
    get_stylesheet_directory_uri() . '/assets/js/articles-grid.js',
    [],
    wp_get_theme()->get('Version'),
    true
  );
  wp_localize_script('hj-articles-grid', 'hjAG', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('hj_ag_filter'),
  ]);
}

$config = [
  'mode'   => $mode,
  'terms'  => hj_ag_normalize_ids($terms),
  'pick'   => hj_ag_normalize_ids($pick),
  'count'  => $count,
];

$posts = hj_ag_get_posts_filtered($mode, $terms, $pick, $count, 0);
$cards_html = hj_ag_render_cards($posts);
?>
<section class="hj-articles-grid" id="<?php echo esc_attr($uid); ?>" aria-label="Articles" data-config="<?php echo esc_attr(wp_json_encode($config)); ?>">
  <div class="hj-ag-wrap">
    <div class="hj-ag-head">
      <?php if ($title): ?>
        <h2 class="hj-ag-title hj-hd-title hj-flex-h2">
          <span class="accent" aria-hidden="true"></span><?php echo esc_html($title); ?>
        </h2>
      <?php endif; ?>
      <?php if (!empty($cta['label']) && !empty($cta['url'])): ?>
        <a class="hj-ag-all" href="<?php echo esc_url($cta['url']); ?>">
          <?php echo esc_html($cta['label']); ?> <span aria-hidden="true">↗</span>
        </a>
      <?php endif; ?>
    </div>

    <?php if (!empty($filter_terms)): ?>
      <div class="hj-ag-filters" role="tablist" aria-label="Article filters">
        <button class="hj-ag-filter is-active" type="button" data-term="0" aria-pressed="true">All</button>
        <?php foreach ($filter_terms as $ft): ?>
          <button class="hj-ag-filter" type="button" data-term="<?php echo esc_attr($ft->term_id); ?>" aria-pressed="false"><?php echo esc_html($ft->name); ?></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($cards_html)): ?>
      <ul class="hj-ag-grid" role="list" style="--ag-cols: <?php echo esc_attr($columns); ?>; --ag-cols-md: <?php echo esc_attr($columns_md); ?>; --ag-cols-sm: <?php echo esc_attr($columns_sm); ?>;">
        <?php echo $cards_html; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
