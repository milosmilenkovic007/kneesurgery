<?php
$title = get_sub_field('title');
$mode  = get_sub_field('mode') ?: 'category';
$terms = get_sub_field('category'); // ids
$pick  = get_sub_field('posts'); // ids
$count = (int) (get_sub_field('count') ?: 3);
$columns = (int) (get_sub_field('columns') ?: 3);
$columns = max(1, min(6, $columns));
$columns_md = (int) (get_sub_field('columns_tablet') ?: 2);
$columns_md = max(1, min(4, $columns_md));
$columns_sm = (int) (get_sub_field('columns_mobile') ?: 1);
$columns_sm = max(1, min(2, $columns_sm));
$cta   = get_sub_field('cta');

$uid = uniqid('hj-ag-');

function hj_ag_get_posts($mode, $terms, $pick, $count){
  if($mode === 'manual' && !empty($pick)){
    $ids = array_map('intval', (array)$pick);
    $ids = array_slice($ids, 0, $count);
    $q = new WP_Query([
      'post_type' => 'post',
      'post__in' => $ids,
      'orderby' => 'post__in',
      'ignore_sticky_posts' => true,
      'posts_per_page' => $count,
    ]);
    return $q->posts;
  }
  // category mode
  $tax_args = [];
  $term_ids = array_filter(array_map('intval', (array)$terms));
  if(!empty($term_ids)){
    $tax_args = [
      [
        'taxonomy' => 'category',
        'field'    => 'term_id',
        'terms'    => $term_ids,
      ]
    ];
  }
  $q = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => $count,
    'ignore_sticky_posts' => true,
    'tax_query' => $tax_args,
  ]);
  return $q->posts;
}

$posts = hj_ag_get_posts($mode, $terms, $pick, $count);
?>
<section class="hj-articles-grid" id="<?php echo esc_attr($uid); ?>" aria-label="Articles">
  <div class="hj-ag-wrap">
    <div class="hj-ag-head">
      <?php if ($title): ?>
        <h2 class="hj-ag-title hj-hd-title">
          <span class="accent" aria-hidden="true"></span><?php echo esc_html($title); ?>
        </h2>
      <?php endif; ?>
      <?php if (!empty($cta['label']) && !empty($cta['url'])): ?>
        <a class="hj-ag-all" href="<?php echo esc_url($cta['url']); ?>">
          <?php echo esc_html($cta['label']); ?> <span aria-hidden="true">↗</span>
        </a>
      <?php endif; ?>
    </div>

    <?php if (!empty($posts)): ?>
      <ul class="hj-ag-grid" role="list" style="--ag-cols: <?php echo esc_attr($columns); ?>; --ag-cols-md: <?php echo esc_attr($columns_md); ?>; --ag-cols-sm: <?php echo esc_attr($columns_sm); ?>;">
        <?php foreach ($posts as $p): setup_postdata($p); ?>
          <li class="hj-ag-card">
            <a class="card-link" href="<?php echo esc_url(get_permalink($p)); ?>">
              <figure class="card-media">
                <?php 
                  $thumb = get_the_post_thumbnail($p, 'large', ['class' => 'img', 'alt' => esc_attr(get_the_title($p))]);
                  if ($thumb) {
                    echo $thumb; 
                  } else {
                    // Placeholder when no featured image
                    ?>
                    <div class="img placeholder" aria-hidden="true">
                      <svg width="56" height="56" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="4" stroke="#94a3b8" stroke-width="1.5"/>
                        <path d="M7 15l3-3 4 4 3-3 2 2" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="8" cy="8" r="1.5" fill="#94a3b8"/>
                      </svg>
                    </div>
                    <?php
                  }
                ?>
              </figure>
              <div class="card-meta">
                <span class="author-ico" aria-hidden="true">
                  <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/author-icon.svg' ); ?>" alt="">
                </span>
                <span class="author">
                  <?php echo esc_html(get_the_author_meta('display_name', $p->post_author)); ?>
                </span>
                <span class="date">
                  <?php echo esc_html(get_the_date('j M, Y', $p)); ?>
                </span>
              </div>
              <h3 class="card-title"><?php echo esc_html(get_the_title($p)); ?></h3>
              <p class="card-excerpt">
                <?php echo esc_html(wp_trim_words(get_the_excerpt($p), 24)); ?>
              </p>
              <?php $cats = get_the_category($p->ID); if (!empty($cats)): $c = $cats[0]; ?>
                <span class="card-tag"><?php echo esc_html($c->name); ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; wp_reset_postdata(); ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
