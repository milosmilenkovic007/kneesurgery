<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('hj_spc_get_form_feedback')) {
    function hj_spc_get_form_feedback($treatment_id = 0)
    {
        $status = isset($_GET['hj_spc_status']) ? sanitize_key(wp_unslash($_GET['hj_spc_status'])) : '';
        $submitted_treatment = isset($_GET['hj_spc_treatment']) ? absint($_GET['hj_spc_treatment']) : 0;

        if ($status === '') {
            return [
                'status' => '',
                'message' => '',
            ];
        }

        if ($treatment_id > 0 && $submitted_treatment > 0 && $submitted_treatment !== $treatment_id) {
            return [
                'status' => '',
                'message' => '',
            ];
        }

        $messages = [
            'success' => __('Your request has been sent. Our team will contact you shortly.', 'hello-elementor-child'),
            'invalid' => __('Please complete all fields with a valid email address.', 'hello-elementor-child'),
            'error' => __('We could not send your request right now. Please try again shortly.', 'hello-elementor-child'),
        ];

        return [
            'status' => array_key_exists($status, $messages) ? $status : 'error',
            'message' => $messages[$status] ?? $messages['error'],
        ];
    }
}

if (!function_exists('hj_spc_build_response_url')) {
    function hj_spc_build_response_url($redirect_to, $status, $treatment_id = 0)
    {
        $clean_url = remove_query_arg(['hj_spc_status', 'hj_spc_treatment'], $redirect_to);

        return add_query_arg(
            [
                'hj_spc_status' => sanitize_key($status),
                'hj_spc_treatment' => absint($treatment_id),
            ],
            $clean_url
        );
    }
}

if (!function_exists('hj_spc_handle_lead_submission')) {
    function hj_spc_handle_lead_submission()
    {
        $redirect_to = isset($_POST['redirect_to'])
            ? wp_validate_redirect(esc_url_raw(wp_unslash($_POST['redirect_to'])), home_url('/'))
            : home_url('/');

        $treatment_id = isset($_POST['treatment_id']) ? absint($_POST['treatment_id']) : 0;

        if (!isset($_POST['hj_spc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['hj_spc_nonce'])), 'hj_spc_submit_lead')) {
            wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'invalid', $treatment_id));
            exit;
        }

        $honeypot = isset($_POST['company']) ? trim((string) wp_unslash($_POST['company'])) : '';
        if ($honeypot !== '') {
            wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'success', $treatment_id));
            exit;
        }

        $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $country_code = isset($_POST['country_code']) ? sanitize_key(wp_unslash($_POST['country_code'])) : '';
        $phone_raw = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $formatted_phone = preg_replace('/[^0-9+()\-\s]/', '', $phone_raw);
        $treatment_title = isset($_POST['treatment_title']) ? sanitize_text_field(wp_unslash($_POST['treatment_title'])) : '';
        $treatment_title = $treatment_id > 0 ? get_the_title($treatment_id) : $treatment_title;

        if ($full_name === '' || $country_code === '' || $formatted_phone === '' || !is_email($email)) {
            wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'invalid', $treatment_id));
            exit;
        }

        $recipient = apply_filters('hj_spc_lead_recipient', get_option('admin_email'), $treatment_id);
        $subject = sprintf(
            __('Package enquiry: %s', 'hello-elementor-child'),
            $treatment_title !== '' ? $treatment_title : __('Treatment package', 'hello-elementor-child')
        );

        $message_lines = [
            __('New package booking enquiry received.', 'hello-elementor-child'),
            '',
            sprintf(__('Treatment: %s', 'hello-elementor-child'), $treatment_title !== '' ? $treatment_title : __('Not provided', 'hello-elementor-child')),
            sprintf(__('Name: %s', 'hello-elementor-child'), $full_name),
            sprintf(__('Mobile: %s', 'hello-elementor-child'), $formatted_phone),
            sprintf(__('Country: %s', 'hello-elementor-child'), strtoupper($country_code)),
            sprintf(__('Email: %s', 'hello-elementor-child'), $email),
            sprintf(__('Page: %s', 'hello-elementor-child'), $redirect_to),
        ];

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $headers[] = 'Reply-To: ' . $full_name . ' <' . $email . '>';

        $sent = wp_mail($recipient, $subject, implode("\n", $message_lines), $headers);

        wp_safe_redirect(hj_spc_build_response_url($redirect_to, $sent ? 'success' : 'error', $treatment_id));
        exit;
    }
}

add_action('admin_post_hj_spc_submit_lead', 'hj_spc_handle_lead_submission');
add_action('admin_post_nopriv_hj_spc_submit_lead', 'hj_spc_handle_lead_submission');