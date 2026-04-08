<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('hj_spc_normalize_phone_input')) {
    function hj_spc_normalize_phone_input($value)
    {
        $value = trim((string) $value);

        return preg_replace('/[^0-9+()\-\s]/', '', $value);
    }
}

if (!function_exists('hj_spc_guess_country_code_from_phone')) {
    function hj_spc_guess_country_code_from_phone($phone)
    {
        $phone = trim((string) $phone);
        if ($phone === '' || strpos($phone, '+') !== 0) {
            return '';
        }

        $normalized_phone = preg_replace('/\s+/', '', $phone);
        $country_map = [
            '+44' => 'gb',
            '+1' => 'us',
            '+353' => 'ie',
            '+49' => 'de',
            '+33' => 'fr',
            '+34' => 'es',
            '+39' => 'it',
            '+381' => 'rs',
            '+385' => 'hr',
            '+387' => 'ba',
            '+382' => 'me',
            '+389' => 'mk',
            '+355' => 'al',
        ];

        uksort($country_map, static function ($left, $right) {
            return strlen($right) <=> strlen($left);
        });

        foreach ($country_map as $prefix => $country_code) {
            if (strpos($normalized_phone, $prefix) === 0) {
                return $country_code;
            }
        }

        return '';
    }
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

if (!function_exists('hj_spc_get_success_redirect_url')) {
    function hj_spc_get_success_redirect_url($treatment_id = 0)
    {
        $thank_you_page = get_page_by_path('thank-you');
        $thank_you_url = $thank_you_page instanceof WP_Post
            ? get_permalink($thank_you_page)
            : home_url('/thank-you/');

        return apply_filters('hj_spc_success_redirect_url', $thank_you_url, $treatment_id);
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
            wp_safe_redirect(hj_spc_get_success_redirect_url($treatment_id));
            exit;
        }

        $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $preferred_contact_method = isset($_POST['preferred_contact_method']) ? sanitize_key(wp_unslash($_POST['preferred_contact_method'])) : '';
        $country_code = isset($_POST['country_code']) ? sanitize_key(wp_unslash($_POST['country_code'])) : '';
        $phone_raw = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $phone_display = isset($_POST['phone_display']) ? sanitize_text_field(wp_unslash($_POST['phone_display'])) : '';
        $formatted_phone = hj_spc_normalize_phone_input($phone_raw);
        if ($formatted_phone === '') {
            $formatted_phone = hj_spc_normalize_phone_input($phone_display);
        }

        $allowed_contact_methods = [
            'phone_call' => __('Phone call', 'hello-elementor-child'),
            'whatsapp' => __('Whatsapp', 'hello-elementor-child'),
            'email' => __('Email', 'hello-elementor-child'),
        ];

        if (!array_key_exists($preferred_contact_method, $allowed_contact_methods)) {
            $preferred_contact_method = '';
        }

        if ($country_code === '') {
            $country_code = hj_spc_guess_country_code_from_phone($formatted_phone);
        }
        $treatment_title = isset($_POST['treatment_title']) ? sanitize_text_field(wp_unslash($_POST['treatment_title'])) : '';
        $treatment_title = $treatment_id > 0 ? get_the_title($treatment_id) : $treatment_title;

        if ($full_name === '' || $formatted_phone === '' || !is_email($email) || $preferred_contact_method === '') {
            wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'invalid', $treatment_id));
            exit;
        }

        $submission_id = function_exists('hj_bfs_create_submission')
            ? hj_bfs_create_submission([
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $formatted_phone,
                'preferred_contact_method' => $preferred_contact_method,
                'country_code' => $country_code,
                'treatment_id' => $treatment_id,
                'treatment_title' => $treatment_title,
                'source_url' => $redirect_to,
            ])
            : 0;

        if (is_wp_error($submission_id)) {
            wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'error', $treatment_id));
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
            sprintf(__('Preferred contact method: %s', 'hello-elementor-child'), $allowed_contact_methods[$preferred_contact_method]),
            sprintf(__('Country: %s', 'hello-elementor-child'), strtoupper($country_code)),
            sprintf(__('Email: %s', 'hello-elementor-child'), $email),
            sprintf(__('Page: %s', 'hello-elementor-child'), $redirect_to),
        ];

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        $headers[] = 'Reply-To: ' . $full_name . ' <' . $email . '>';

        $sent = wp_mail($recipient, $subject, implode("\n", $message_lines), $headers);

        if ($sent) {
            wp_safe_redirect(hj_spc_get_success_redirect_url($treatment_id));
            exit;
        }

        wp_safe_redirect(hj_spc_build_response_url($redirect_to, 'error', $treatment_id));
        exit;
    }
}

add_action('admin_post_hj_spc_submit_lead', 'hj_spc_handle_lead_submission');
add_action('admin_post_nopriv_hj_spc_submit_lead', 'hj_spc_handle_lead_submission');