<?php
/**
 * Export current page content (modules) to a Word .doc file using HTML format
 */
add_action('template_redirect', function(){
    if (empty($_GET['hj_export_doc'])) return;

    // Determine the post ID to export
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    if (!$post_id) {
        $post = get_queried_object();
        if ($post && !empty($post->ID)) { $post_id = intval($post->ID); }
    }
    if (!$post_id) { wp_die(__('Nothing to export.','hj')); }

    // Compose HTML using our flexible modules renderer
    ob_start();
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.esc_html(get_the_title($post_id))."</title>";
    // Basic inline styles for doc legibility
    echo '<style>body{font-family:Segoe UI, Arial, Helvetica, sans-serif; color:#111827;} h1,h2,h3{color:#111827} img{max-width:100%;height:auto} .hj-pa-item, .hj-hd-bullet{margin-bottom:6px}</style>';
    echo '</head><body>';
    echo '<h1>'.esc_html(get_the_title($post_id)).'</h1>';
    if (function_exists('hj_render_page_modules')) {
        hj_render_page_modules($post_id);
    } else {
        echo apply_filters('the_content', get_post_field('post_content', $post_id));
    }
    echo '</body></html>';
    $html = ob_get_clean();

    // Download headers for .doc
    $filename = sanitize_title(get_the_title($post_id)).'-export.doc';
    header('Content-Type: application/vnd.ms-word; charset=UTF-8');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $html;
    exit;
});
