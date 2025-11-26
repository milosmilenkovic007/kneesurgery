<?php
// AJAX upload for candidate photos
add_action('wp_ajax_hj_upload_candidate', 'hj_upload_candidate');
add_action('wp_ajax_nopriv_hj_upload_candidate', 'hj_upload_candidate');

function hj_upload_candidate(){
    check_ajax_referer('hj_candidate_upload');
    if (empty($_FILES['file'])) {
        wp_send_json_error('No file');
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $overrides = [
        'test_form' => false,
        'mimes' => [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'heic' => 'image/heic',
        ],
    ];

    $uploaded = wp_handle_upload($_FILES['file'], $overrides);
    if (isset($uploaded['error'])) {
        wp_send_json_error($uploaded['error']);
    }

    // Create attachment so file appears in Media Library
    $file_path = $uploaded['file'];
    $file_url  = $uploaded['url'];
    $file_type = $uploaded['type'];

    $attachment = [
        'post_mime_type' => $file_type,
        'post_title'     => sanitize_file_name(basename($file_path)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];
    $attach_id = wp_insert_attachment($attachment, $file_path);
    if (!is_wp_error($attach_id)) {
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
    }

    wp_send_json_success(['id' => $attach_id, 'url' => $file_url]);
}
