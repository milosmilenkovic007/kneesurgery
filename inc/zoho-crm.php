<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('HJ_ZOHO_CRM_STATUS_OPTION')) {
    define('HJ_ZOHO_CRM_STATUS_OPTION', 'hj_zoho_crm_last_status');
}

if (!defined('HJ_ZOHO_CRM_RETRY_QUEUE_OPTION')) {
    define('HJ_ZOHO_CRM_RETRY_QUEUE_OPTION', 'hj_zoho_crm_retry_queue');
}

if (!defined('HJ_ZOHO_CRM_RETRY_HOOK')) {
    define('HJ_ZOHO_CRM_RETRY_HOOK', 'hj_zoho_crm_process_retry_queue');
}

if (!defined('HJ_ZOHO_CRM_TOKEN_OPTION')) {
    define('HJ_ZOHO_CRM_TOKEN_OPTION', 'hj_zoho_crm_oauth_token');
}

if (!defined('HJ_ZOHO_CRM_OAUTH_STATE_OPTION')) {
    define('HJ_ZOHO_CRM_OAUTH_STATE_OPTION', 'hj_zoho_crm_oauth_state');
}

if (!defined('HJ_ZOHO_CRM_OAUTH_STATUS_OPTION')) {
    define('HJ_ZOHO_CRM_OAUTH_STATUS_OPTION', 'hj_zoho_crm_oauth_status');
}

if (!defined('HJ_ZOHO_CRM_OAUTH_DEBUG_OPTION')) {
    define('HJ_ZOHO_CRM_OAUTH_DEBUG_OPTION', 'hj_zoho_crm_oauth_debug');
}

if (!function_exists('hj_get_zoho_crm_defaults')) {
    function hj_get_zoho_crm_defaults()
    {
        return [
            'enabled' => false,
            'webhook_url' => '',
            'module' => 'Leads',
            'auth_mode' => 'zoho_oauth',
            'auth_token' => '',
            'oauth_accounts_url' => 'https://accounts.zoho.com',
            'oauth_client_id' => '',
            'oauth_client_secret' => '',
            'oauth_refresh_token' => '',
            'header_name' => '',
            'header_value' => '',
            'source' => 'KST',
            'retry_max_attempts' => 3,
            'retry_delay_minutes' => 10,
            'field_first_name' => 'First_Name',
            'field_last_name' => 'Last_Name',
            'field_full_name' => 'full_name',
            'field_email' => 'Email',
            'field_phone' => 'Phone',
            'field_preferred_contact_method' => 'preferred_contact_method',
            'field_country_code' => 'Country',
            'field_service' => 'service',
            'field_price' => 'price',
            'field_source' => 'Lead_Source',
            'field_source_url' => 'Website',
        ];
    }
}

if (!function_exists('hj_get_zoho_crm_settings')) {
    function hj_get_zoho_crm_settings()
    {
        $defaults = hj_get_zoho_crm_defaults();
        $get_option_value = static function ($field_name, $default = '') {
            if (!function_exists('get_field')) {
                return $default;
            }

            $value = get_field($field_name, 'option');

            return ($value === null || $value === '') ? $default : $value;
        };

        return [
            'enabled' => !empty($get_option_value('zoho_crm_enabled', $defaults['enabled'])),
            'webhook_url' => esc_url_raw((string) $get_option_value('zoho_crm_webhook_url', $defaults['webhook_url'])),
            'module' => trim((string) $get_option_value('zoho_crm_module', $defaults['module'])),
            'auth_mode' => sanitize_key((string) $get_option_value('zoho_crm_auth_mode', $defaults['auth_mode'])),
            'auth_token' => trim((string) $get_option_value('zoho_crm_auth_token', $defaults['auth_token'])),
            'oauth_accounts_url' => esc_url_raw((string) $get_option_value('zoho_crm_oauth_accounts_url', $defaults['oauth_accounts_url'])),
            'oauth_client_id' => trim((string) $get_option_value('zoho_crm_oauth_client_id', $defaults['oauth_client_id'])),
            'oauth_client_secret' => trim((string) $get_option_value('zoho_crm_oauth_client_secret', $defaults['oauth_client_secret'])),
            'oauth_refresh_token' => trim((string) $get_option_value('zoho_crm_oauth_refresh_token', $defaults['oauth_refresh_token'])),
            'header_name' => trim((string) $get_option_value('zoho_crm_header_name', $defaults['header_name'])),
            'header_value' => trim((string) $get_option_value('zoho_crm_header_value', $defaults['header_value'])),
            'source' => trim((string) $get_option_value('zoho_crm_source', $defaults['source'])),
            'retry_max_attempts' => max(1, min(10, (int) $get_option_value('zoho_crm_retry_max_attempts', $defaults['retry_max_attempts']))),
            'retry_delay_minutes' => max(1, min(1440, (int) $get_option_value('zoho_crm_retry_delay_minutes', $defaults['retry_delay_minutes']))),
            'field_first_name' => trim((string) $get_option_value('zoho_crm_field_first_name', $defaults['field_first_name'])),
            'field_last_name' => trim((string) $get_option_value('zoho_crm_field_last_name', $defaults['field_last_name'])),
            'field_full_name' => trim((string) $get_option_value('zoho_crm_field_full_name', $defaults['field_full_name'])),
            'field_email' => trim((string) $get_option_value('zoho_crm_field_email', $defaults['field_email'])),
            'field_phone' => trim((string) $get_option_value('zoho_crm_field_phone', $defaults['field_phone'])),
            'field_preferred_contact_method' => trim((string) $get_option_value('zoho_crm_field_preferred_contact_method', $defaults['field_preferred_contact_method'])),
            'field_country_code' => trim((string) $get_option_value('zoho_crm_field_country_code', $defaults['field_country_code'])),
            'field_service' => trim((string) $get_option_value('zoho_crm_field_service', $defaults['field_service'])),
            'field_price' => trim((string) $get_option_value('zoho_crm_field_price', $defaults['field_price'])),
            'field_source' => trim((string) $get_option_value('zoho_crm_field_source', $defaults['field_source'])),
            'field_source_url' => trim((string) $get_option_value('zoho_crm_field_source_url', $defaults['field_source_url'])),
        ];
    }
}

if (!function_exists('hj_zoho_crm_get_cached_access_token')) {
    function hj_zoho_crm_get_cached_access_token()
    {
        $cached = get_option(HJ_ZOHO_CRM_TOKEN_OPTION, []);
        if (!is_array($cached)) {
            return '';
        }

        $token = trim((string) ($cached['access_token'] ?? ''));
        $expires_at = (int) ($cached['expires_at'] ?? 0);

        if ($token === '' || $expires_at <= time()) {
            return '';
        }

        return $token;
    }
}

if (!function_exists('hj_zoho_crm_update_refresh_token_value')) {
    function hj_zoho_crm_update_refresh_token_value($refresh_token)
    {
        $refresh_token = trim((string) $refresh_token);
        if ($refresh_token === '') {
            return;
        }

        if (function_exists('update_field')) {
            update_field('zoho_crm_oauth_refresh_token', $refresh_token, 'option');
        }

        update_option('options_zoho_crm_oauth_refresh_token', $refresh_token, false);
    }
}

if (!function_exists('hj_zoho_crm_set_oauth_status')) {
    function hj_zoho_crm_set_oauth_status($status, $message)
    {
        update_option(HJ_ZOHO_CRM_OAUTH_STATUS_OPTION, [
            'status' => sanitize_key((string) $status),
            'message' => (string) $message,
            'updated_at' => current_time('mysql'),
        ], false);
    }
}

if (!function_exists('hj_zoho_crm_get_oauth_status')) {
    function hj_zoho_crm_get_oauth_status()
    {
        $status = get_option(HJ_ZOHO_CRM_OAUTH_STATUS_OPTION, []);

        return is_array($status) ? $status : [];
    }
}

if (!function_exists('hj_zoho_crm_set_oauth_debug')) {
    function hj_zoho_crm_set_oauth_debug(array $debug)
    {
        update_option(HJ_ZOHO_CRM_OAUTH_DEBUG_OPTION, $debug, false);
    }
}

if (!function_exists('hj_zoho_crm_get_oauth_debug')) {
    function hj_zoho_crm_get_oauth_debug()
    {
        $debug = get_option(HJ_ZOHO_CRM_OAUTH_DEBUG_OPTION, []);

        return is_array($debug) ? $debug : [];
    }
}

if (!function_exists('hj_zoho_crm_set_cached_access_token')) {
    function hj_zoho_crm_set_cached_access_token($access_token, $expires_in, $api_domain = '')
    {
        $expires_at = time() + max(60, (int) $expires_in - 60);
        $cached = [
            'access_token' => (string) $access_token,
            'expires_at' => $expires_at,
        ];

        $api_domain = esc_url_raw((string) $api_domain);
        if ($api_domain !== '') {
            $cached['api_domain'] = $api_domain;
        }

        update_option(HJ_ZOHO_CRM_TOKEN_OPTION, $cached, false);
    }
}

if (!function_exists('hj_zoho_crm_get_oauth_access_token')) {
    function hj_zoho_crm_get_oauth_access_token(array $settings)
    {
        $cached = hj_zoho_crm_get_cached_access_token();
        if ($cached !== '') {
            return $cached;
        }

        $accounts_url = rtrim((string) ($settings['oauth_accounts_url'] ?? ''), '/');
        $client_id = (string) ($settings['oauth_client_id'] ?? '');
        $client_secret = (string) ($settings['oauth_client_secret'] ?? '');
        $refresh_token = (string) ($settings['oauth_refresh_token'] ?? '');

        if ($accounts_url === '' || $client_id === '' || $client_secret === '' || $refresh_token === '') {
            return new WP_Error('zoho_oauth_missing', __('Zoho OAuth settings are incomplete.', 'kneesurgery'));
        }

        $response = wp_remote_post($accounts_url . '/oauth/v2/token', [
            'timeout' => 15,
            'body' => [
                'grant_type' => 'refresh_token',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('zoho_oauth_request', $response->get_error_message());
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($body)) {
            $body = [];
        }

        $access_token = trim((string) ($body['access_token'] ?? ''));
        $expires_in = (int) ($body['expires_in'] ?? $body['expires_in_sec'] ?? 3600);
        $api_domain = esc_url_raw((string) ($body['api_domain'] ?? ''));

        if ($code < 200 || $code >= 300 || $access_token === '') {
            $error_message = trim((string) ($body['error'] ?? $body['error_description'] ?? 'OAuth token refresh failed.'));
            delete_option(HJ_ZOHO_CRM_TOKEN_OPTION);
            return new WP_Error('zoho_oauth_failed', $error_message);
        }

        hj_zoho_crm_set_cached_access_token($access_token, $expires_in, $api_domain);

        return $access_token;
    }
}

if (!function_exists('hj_zoho_crm_get_oauth_redirect_uri')) {
    function hj_zoho_crm_get_oauth_redirect_uri()
    {
        return add_query_arg(
            ['action' => 'hj_zoho_crm_oauth_callback'],
            admin_url('admin-post.php')
        );
    }
}

if (!function_exists('hj_zoho_crm_get_oauth_connect_url')) {
    function hj_zoho_crm_get_oauth_connect_url()
    {
        return wp_nonce_url(
            add_query_arg(['action' => 'hj_zoho_crm_oauth_connect'], admin_url('admin-post.php')),
            'hj_zoho_crm_oauth_connect'
        );
    }
}

if (!function_exists('hj_zoho_crm_build_oauth_authorize_url')) {
    function hj_zoho_crm_build_oauth_authorize_url(array $settings)
    {
        $accounts_url = rtrim((string) ($settings['oauth_accounts_url'] ?? ''), '/');
        $client_id = trim((string) ($settings['oauth_client_id'] ?? ''));

        if ($accounts_url === '' || $client_id === '') {
            return '';
        }

        $state = wp_generate_password(32, false, false);
        update_option(HJ_ZOHO_CRM_OAUTH_STATE_OPTION, [
            'value' => $state,
            'expires_at' => time() + 900,
            'user_id' => get_current_user_id(),
        ], false);

        $query = [
            'scope' => 'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL',
            'client_id' => $client_id,
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'redirect_uri' => hj_zoho_crm_get_oauth_redirect_uri(),
            'state' => $state,
        ];

        return add_query_arg($query, $accounts_url . '/oauth/v2/auth');
    }
}

if (!function_exists('hj_zoho_crm_exchange_authorization_code')) {
    function hj_zoho_crm_exchange_authorization_code($code, array $settings)
    {
        $accounts_url = rtrim((string) ($settings['oauth_accounts_url'] ?? ''), '/');
        $client_id = trim((string) ($settings['oauth_client_id'] ?? ''));
        $client_secret = trim((string) ($settings['oauth_client_secret'] ?? ''));

        if ($accounts_url === '' || $client_id === '' || $client_secret === '' || $code === '') {
            return new WP_Error('zoho_oauth_missing', __('Zoho OAuth credentials are incomplete.', 'kneesurgery'));
        }

        $response = wp_remote_post($accounts_url . '/oauth/v2/token', [
            'timeout' => 15,
            'body' => [
                'grant_type' => 'authorization_code',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => hj_zoho_crm_get_oauth_redirect_uri(),
                'code' => $code,
            ],
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('zoho_oauth_exchange_failed', $response->get_error_message());
        }

        $http_code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = (string) wp_remote_retrieve_body($response);
        $body = json_decode($raw_body, true);
        if (!is_array($body)) {
            $body = [];
        }

        hj_zoho_crm_set_oauth_debug([
            'stage' => 'authorization_code_exchange',
            'http_code' => $http_code,
            'raw_body' => $raw_body,
            'parsed_body' => $body,
            'updated_at' => current_time('mysql'),
        ]);

        if ($http_code < 200 || $http_code >= 300) {
            $message = trim((string) ($body['error_description'] ?? $body['error'] ?? 'OAuth exchange failed.'));
            return new WP_Error('zoho_oauth_exchange_error', $message);
        }

        $access_token = trim((string) ($body['access_token'] ?? ''));
        $refresh_token = trim((string) ($body['refresh_token'] ?? ''));
        $expires_in = (int) ($body['expires_in'] ?? $body['expires_in_sec'] ?? 3600);
        $api_domain = esc_url_raw((string) ($body['api_domain'] ?? ''));

        if ($access_token !== '') {
            hj_zoho_crm_set_cached_access_token($access_token, $expires_in, $api_domain);
        }

        if ($refresh_token !== '') {
            hj_zoho_crm_update_refresh_token_value($refresh_token);
        }

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_in' => $expires_in,
            'api_domain' => $api_domain,
        ];
    }
}

if (!function_exists('hj_zoho_crm_extract_numeric_price')) {
    function hj_zoho_crm_extract_numeric_price($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $matches = [];
        if (!preg_match('/[-+]?[0-9][0-9.,\s]*/', $value, $matches)) {
            return '';
        }

        $numeric = preg_replace('/\s+/', '', (string) $matches[0]);
        $numeric = str_replace(',', '.', $numeric);

        if (substr_count($numeric, '.') > 1) {
            $last_dot = strrpos($numeric, '.');
            $integer = str_replace('.', '', substr($numeric, 0, $last_dot));
            $decimal = substr($numeric, $last_dot + 1);
            $numeric = $integer . ($decimal !== '' ? '.' . $decimal : '');
        }

        if (!is_numeric($numeric)) {
            return '';
        }

        $normalized = number_format((float) $numeric, 2, '.', '');
        $normalized = rtrim(rtrim($normalized, '0'), '.');

        return $normalized;
    }
}

if (!function_exists('hj_zoho_crm_prepare_source_url')) {
    function hj_zoho_crm_prepare_source_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return substr($url, 0, 255);
        }

        $normalized_url = '';

        if (!empty($parts['scheme'])) {
            $normalized_url .= $parts['scheme'] . '://';
        }

        $normalized_url .= $parts['host'];

        if (!empty($parts['port'])) {
            $normalized_url .= ':' . (int) $parts['port'];
        }

        $normalized_url .= isset($parts['path']) ? (string) $parts['path'] : '/';

        return substr($normalized_url, 0, 255);
    }
}

if (!function_exists('hj_zoho_crm_get_last_status')) {
    function hj_zoho_crm_get_last_status()
    {
        $status = get_option(HJ_ZOHO_CRM_STATUS_OPTION, []);

        return is_array($status) ? $status : [];
    }
}

if (!function_exists('hj_zoho_crm_set_last_status')) {
    function hj_zoho_crm_set_last_status(array $status)
    {
        $status['updated_at'] = current_time('mysql');
        update_option(HJ_ZOHO_CRM_STATUS_OPTION, $status, false);
    }
}

if (!function_exists('hj_zoho_crm_get_retry_queue')) {
    function hj_zoho_crm_get_retry_queue()
    {
        $queue = get_option(HJ_ZOHO_CRM_RETRY_QUEUE_OPTION, []);

        return is_array($queue) ? $queue : [];
    }
}

if (!function_exists('hj_zoho_crm_set_retry_queue')) {
    function hj_zoho_crm_set_retry_queue(array $queue)
    {
        update_option(HJ_ZOHO_CRM_RETRY_QUEUE_OPTION, array_values($queue), false);
    }
}

if (!function_exists('hj_zoho_crm_schedule_queue_processor')) {
    function hj_zoho_crm_schedule_queue_processor($timestamp = 0)
    {
        $timestamp = $timestamp > 0 ? (int) $timestamp : (time() + 60);
        if (!wp_next_scheduled(HJ_ZOHO_CRM_RETRY_HOOK)) {
            wp_schedule_single_event($timestamp, HJ_ZOHO_CRM_RETRY_HOOK);
        }
    }
}

if (!function_exists('hj_zoho_crm_enqueue_retry')) {
    function hj_zoho_crm_enqueue_retry(array $lead, array $result = [])
    {
        $settings = hj_get_zoho_crm_settings();
        $delay_seconds = max(60, ((int) $settings['retry_delay_minutes']) * 60);

        $queue = hj_zoho_crm_get_retry_queue();
        $queue[] = [
            'id' => wp_generate_uuid4(),
            'attempts' => 0,
            'next_run' => time() + $delay_seconds,
            'lead' => $lead,
            'last_error' => (string) ($result['body'] ?? ''),
            'last_code' => (int) ($result['code'] ?? 0),
        ];

        hj_zoho_crm_set_retry_queue($queue);
        hj_zoho_crm_schedule_queue_processor(time() + $delay_seconds);

        hj_zoho_crm_set_last_status([
            'status' => 'queued',
            'code' => (int) ($result['code'] ?? 0),
            'message' => __('Zoho lead queued for retry.', 'kneesurgery'),
            'queue_count' => count($queue),
        ]);
    }
}

if (!function_exists('hj_zoho_crm_build_headers')) {
    function hj_zoho_crm_build_headers(array $settings)
    {
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        $auth_mode = (string) ($settings['auth_mode'] ?? 'custom_header');

        if ($auth_mode === 'bearer' && !empty($settings['auth_token'])) {
            $headers['Authorization'] = 'Bearer ' . trim((string) $settings['auth_token']);
        } elseif ($auth_mode === 'zoho_oauth') {
            $access_token = hj_zoho_crm_get_oauth_access_token($settings);
            if (is_wp_error($access_token)) {
                return $access_token;
            }
            $headers['Authorization'] = 'Bearer ' . $access_token;
        } elseif ($auth_mode === 'custom_header' && !empty($settings['header_name']) && !empty($settings['header_value'])) {
            $headers[(string) $settings['header_name']] = (string) $settings['header_value'];
        }

        return $headers;
    }
}

if (!function_exists('hj_zoho_crm_is_direct_mode')) {
    function hj_zoho_crm_is_direct_mode(array $settings)
    {
        return (string) ($settings['auth_mode'] ?? '') === 'zoho_oauth';
    }
}

if (!function_exists('hj_zoho_crm_get_api_base_url')) {
    function hj_zoho_crm_get_api_base_url(array $settings)
    {
        $cached = get_option(HJ_ZOHO_CRM_TOKEN_OPTION, []);
        if (is_array($cached)) {
            $api_domain = esc_url_raw((string) ($cached['api_domain'] ?? ''));
            if ($api_domain !== '') {
                return rtrim($api_domain, '/');
            }
        }

        $accounts_url = rtrim((string) ($settings['oauth_accounts_url'] ?? ''), '/');
        if ($accounts_url === '') {
            return 'https://www.zohoapis.com';
        }

        $host = (string) parse_url($accounts_url, PHP_URL_HOST);
        $host = strtolower(trim($host));
        if ($host === '') {
            return 'https://www.zohoapis.com';
        }

        if (strpos($host, 'accounts.') === 0) {
            $suffix = substr($host, strlen('accounts.'));
            return 'https://www.zohoapis.' . $suffix;
        }

        return 'https://www.zohoapis.com';
    }
}

if (!function_exists('hj_zoho_crm_send_direct_request')) {
    function hj_zoho_crm_send_direct_request(array $lead, array $settings)
    {
        $payload = hj_zoho_crm_build_payload($lead, $settings);
        $headers = hj_zoho_crm_build_headers($settings);
        if (is_wp_error($headers)) {
            return [
                'status' => 'failed',
                'code' => 0,
                'body' => $headers->get_error_message(),
            ];
        }

        $module = trim((string) ($payload['module'] ?? 'Leads'));
        if ($module === '') {
            $module = 'Leads';
        }

        $record = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        if (!isset($record['Last_Name']) || trim((string) $record['Last_Name']) === '') {
            $fallback_name = trim((string) ($lead['full_name'] ?? ''));
            if ($fallback_name === '') {
                $fallback_name = 'Website Lead';
            }
            $record['Last_Name'] = $fallback_name;
        }

        $endpoint = rtrim(hj_zoho_crm_get_api_base_url($settings), '/') . '/crm/v6/' . rawurlencode($module);

        $request_payload = [
            'data' => [$record],
            'trigger' => ['workflow'],
        ];

        $response = wp_remote_post($endpoint, [
            'timeout' => 15,
            'headers' => $headers,
            'body' => wp_json_encode($request_payload),
        ]);

        if (is_wp_error($response)) {
            return [
                'status' => 'failed',
                'code' => 0,
                'body' => $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);

        $decoded = json_decode($body, true);
        $entry = is_array($decoded['data'][0] ?? null) ? $decoded['data'][0] : [];
        $entry_status = strtolower((string) ($entry['status'] ?? ''));
        $entry_code = strtoupper((string) ($entry['code'] ?? ''));

        if ($entry_status === 'success') {
            return [
                'status' => 'sent',
                'code' => $code,
                'body' => $body,
            ];
        }

        if ($entry_code === 'DUPLICATE_DATA') {
            $existing_id = trim((string) ($entry['details']['id'] ?? ''));
            if ($existing_id !== '') {
                $update_endpoint = $endpoint . '/' . rawurlencode($existing_id);
                $update_response = wp_remote_request($update_endpoint, [
                    'method' => 'PUT',
                    'timeout' => 15,
                    'headers' => $headers,
                    'body' => wp_json_encode($request_payload),
                ]);

                if (is_wp_error($update_response)) {
                    return [
                        'status' => 'failed',
                        'code' => 0,
                        'body' => $update_response->get_error_message(),
                    ];
                }

                $update_code = (int) wp_remote_retrieve_response_code($update_response);
                $update_body = (string) wp_remote_retrieve_body($update_response);
                $update_decoded = json_decode($update_body, true);
                $update_entry = is_array($update_decoded['data'][0] ?? null) ? $update_decoded['data'][0] : [];
                $update_status = strtolower((string) ($update_entry['status'] ?? ''));

                if ($update_status === 'success') {
                    return [
                        'status' => 'sent',
                        'code' => $update_code,
                        'body' => $update_body,
                    ];
                }

                return [
                    'status' => 'failed',
                    'code' => $update_code,
                    'body' => $update_body,
                ];
            }
        }

        return [
            'status' => ($code >= 200 && $code < 300 && $entry_status === '') ? 'sent' : 'failed',
            'code' => $code,
            'body' => $body,
        ];
    }
}

if (!function_exists('hj_zoho_crm_send_request')) {
    function hj_zoho_crm_send_request(array $lead, array $settings)
    {
        if (hj_zoho_crm_is_direct_mode($settings)) {
            return hj_zoho_crm_send_direct_request($lead, $settings);
        }

        $payload = hj_zoho_crm_build_payload($lead, $settings);
        $headers = hj_zoho_crm_build_headers($settings);
        if (is_wp_error($headers)) {
            return [
                'status' => 'failed',
                'code' => 0,
                'body' => $headers->get_error_message(),
            ];
        }

        $response = wp_remote_post($settings['webhook_url'], [
            'timeout' => 15,
            'headers' => $headers,
            'body' => wp_json_encode($payload),
        ]);

        if (is_wp_error($response)) {
            return [
                'status' => 'failed',
                'code' => 0,
                'body' => $response->get_error_message(),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);

        return [
            'status' => ($code >= 200 && $code < 300) ? 'sent' : 'failed',
            'code' => $code,
            'body' => (string) wp_remote_retrieve_body($response),
        ];
    }
}

if (!function_exists('hj_zoho_crm_resolve_field_key')) {
    function hj_zoho_crm_resolve_field_key(array $settings, $key, $fallback)
    {
        $value = trim((string) ($settings[$key] ?? ''));

        return $value !== '' ? $value : $fallback;
    }
}

if (!function_exists('hj_zoho_crm_build_payload')) {
    function hj_zoho_crm_build_payload(array $lead, array $settings)
    {
        $module = trim((string) ($settings['module'] ?? ''));
        if ($module === '') {
            $module = 'Leads';
        }

        $source = trim((string) ($settings['source'] ?? ''));
        if ($source === '') {
            $source = 'KST';
        }

        $full_name = trim((string) ($lead['full_name'] ?? ''));
        $name_parts = preg_split('/\s+/', $full_name, 2);
        $first_name = trim((string) ($name_parts[0] ?? ''));
        $last_name = trim((string) ($name_parts[1] ?? ''));
        if ($last_name === '') {
            $last_name = $full_name;
            $first_name = '';
        }

        $mapped = [];
    $source_url = hj_zoho_crm_prepare_source_url((string) ($lead['source_url'] ?? ''));
        $first_name_key = hj_zoho_crm_resolve_field_key($settings, 'field_first_name', 'First_Name');
        $last_name_key = hj_zoho_crm_resolve_field_key($settings, 'field_last_name', 'Last_Name');
        $full_name_key = trim((string) ($settings['field_full_name'] ?? ''));

        if ($first_name_key !== '') {
            $mapped[$first_name_key] = $first_name;
        }
        if ($last_name_key !== '') {
            $mapped[$last_name_key] = $last_name !== '' ? $last_name : 'Website Lead';
        }
        if ($full_name_key !== '') {
            $mapped[$full_name_key] = $full_name;
        }

        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_email', 'email')] = (string) ($lead['email'] ?? '');
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_phone', 'phone')] = (string) ($lead['phone'] ?? '');
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_preferred_contact_method', 'preferred_contact_method')] = (string) ($lead['preferred_contact_method'] ?? '');
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_country_code', 'country_code')] = strtoupper((string) ($lead['country_code'] ?? ''));
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_service', 'service')] = (string) ($lead['service'] ?? '');
        $price = hj_zoho_crm_extract_numeric_price((string) ($lead['price'] ?? ''));
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_price', 'price')] = $price !== '' ? $price : (string) ($lead['price'] ?? '');
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_source', 'source')] = $source;
        $mapped[hj_zoho_crm_resolve_field_key($settings, 'field_source_url', 'source_url')] = $source_url;

        return [
            'module' => $module,
            'data' => $mapped,
            'raw' => [
                'full_name' => $full_name,
                'first_name' => $first_name,
                'last_name' => $last_name !== '' ? $last_name : 'Website Lead',
                'email' => (string) ($lead['email'] ?? ''),
                'phone' => (string) ($lead['phone'] ?? ''),
                'preferred_contact_method' => (string) ($lead['preferred_contact_method'] ?? ''),
                'country_code' => strtoupper((string) ($lead['country_code'] ?? '')),
                'service' => (string) ($lead['service'] ?? ''),
                'price' => $price !== '' ? $price : (string) ($lead['price'] ?? ''),
                'source' => $source,
                'source_url' => $source_url,
            ],
            'submitted_at' => current_time('mysql'),
        ];
    }
}

if (!function_exists('hj_send_zoho_crm_lead')) {
    function hj_send_zoho_crm_lead(array $lead, $allow_queue = true)
    {
        $settings = hj_get_zoho_crm_settings();
        $is_direct_mode = hj_zoho_crm_is_direct_mode($settings);
        if (empty($settings['enabled'])) {
            $result = [
                'status' => 'disabled',
                'code' => 0,
                'body' => '',
            ];
            hj_zoho_crm_set_last_status([
                'status' => 'disabled',
                'code' => 0,
                'message' => __('Zoho integration is disabled.', 'kneesurgery'),
                'queue_count' => count(hj_zoho_crm_get_retry_queue()),
            ]);

            return $result;
        }

        if (!$is_direct_mode && empty($settings['webhook_url'])) {
            $result = [
                'status' => 'misconfigured',
                'code' => 0,
                'body' => __('Missing Zoho webhook URL.', 'kneesurgery'),
            ];
            hj_zoho_crm_set_last_status([
                'status' => 'misconfigured',
                'code' => 0,
                'message' => __('Zoho is enabled but webhook URL is missing for webhook mode.', 'kneesurgery'),
                'queue_count' => count(hj_zoho_crm_get_retry_queue()),
            ]);

            return $result;
        }

        $result = hj_zoho_crm_send_request($lead, $settings);

        if ($result['status'] === 'sent') {
            hj_zoho_crm_set_last_status([
                'status' => 'sent',
                'code' => (int) $result['code'],
                'message' => __('Zoho lead sent successfully.', 'kneesurgery'),
                'queue_count' => count(hj_zoho_crm_get_retry_queue()),
            ]);

            return [
                'status' => 'sent',
                'code' => (int) $result['code'],
                'body' => (string) $result['body'],
            ];
        }

        if ($allow_queue) {
            hj_zoho_crm_enqueue_retry($lead, $result);
            return [
                'status' => 'queued',
                'code' => (int) $result['code'],
                'body' => (string) $result['body'],
            ];
        }

        hj_zoho_crm_set_last_status([
            'status' => 'failed',
            'code' => (int) $result['code'],
            'message' => __('Zoho retry failed.', 'kneesurgery'),
            'queue_count' => count(hj_zoho_crm_get_retry_queue()),
        ]);

        return [
            'status' => 'failed',
            'code' => (int) $result['code'],
            'body' => (string) $result['body'],
        ];
    }
}

if (!function_exists('hj_zoho_crm_process_retry_queue')) {
    function hj_zoho_crm_process_retry_queue($force = false)
    {
        $settings = hj_get_zoho_crm_settings();
        $queue = hj_zoho_crm_get_retry_queue();

        if (empty($queue)) {
            hj_zoho_crm_set_last_status([
                'status' => 'idle',
                'code' => 0,
                'message' => __('Zoho retry queue is empty.', 'kneesurgery'),
                'queue_count' => 0,
            ]);
            return;
        }

        $updated_queue = [];
        $now = time();
        $delay_seconds = max(60, ((int) $settings['retry_delay_minutes']) * 60);
        $max_attempts = max(1, (int) $settings['retry_max_attempts']);

        foreach ($queue as $item) {
            $next_run = isset($item['next_run']) ? (int) $item['next_run'] : $now;
            if (!$force && $next_run > $now) {
                $updated_queue[] = $item;
                continue;
            }

            $lead = is_array($item['lead'] ?? null) ? $item['lead'] : [];
            $attempts = (int) ($item['attempts'] ?? 0);
            $result = hj_send_zoho_crm_lead($lead, false);

            if (($result['status'] ?? '') === 'sent') {
                continue;
            }

            $attempts++;
            if ($attempts >= $max_attempts) {
                hj_zoho_crm_set_last_status([
                    'status' => 'failed',
                    'code' => (int) ($result['code'] ?? 0),
                    'message' => __('Zoho retry attempts exhausted.', 'kneesurgery'),
                    'queue_count' => max(0, count($updated_queue)),
                ]);
                continue;
            }

            $item['attempts'] = $attempts;
            $item['next_run'] = $now + $delay_seconds;
            $item['last_error'] = (string) ($result['body'] ?? '');
            $item['last_code'] = (int) ($result['code'] ?? 0);
            $updated_queue[] = $item;
        }

        hj_zoho_crm_set_retry_queue($updated_queue);

        if (!empty($updated_queue)) {
            hj_zoho_crm_schedule_queue_processor(time() + $delay_seconds);
        }

        hj_zoho_crm_set_last_status([
            'status' => empty($updated_queue) ? 'idle' : 'queued',
            'code' => 0,
            'message' => empty($updated_queue)
                ? __('Zoho retry queue processed successfully.', 'kneesurgery')
                : __('Zoho retry queue processed. Pending items remain.', 'kneesurgery'),
            'queue_count' => count($updated_queue),
        ]);
    }
}

add_action(HJ_ZOHO_CRM_RETRY_HOOK, 'hj_zoho_crm_process_retry_queue');

if (!function_exists('hj_zoho_crm_get_settings_page_url')) {
    function hj_zoho_crm_get_settings_page_url()
    {
        return admin_url('admin.php?page=theme-settings');
    }
}

if (!function_exists('hj_zoho_crm_get_retry_url')) {
    function hj_zoho_crm_get_retry_url()
    {
        return wp_nonce_url(
            add_query_arg(['action' => 'hj_zoho_crm_retry_now'], admin_url('admin-post.php')),
            'hj_zoho_crm_retry_now'
        );
    }
}

if (!function_exists('hj_zoho_crm_get_test_url')) {
    function hj_zoho_crm_get_test_url()
    {
        return wp_nonce_url(
            add_query_arg(['action' => 'hj_zoho_crm_test_connection'], admin_url('admin-post.php')),
            'hj_zoho_crm_test_connection'
        );
    }
}

add_action('admin_post_hj_zoho_crm_oauth_connect', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to authenticate Zoho.', 'kneesurgery'), 403);
    }

    check_admin_referer('hj_zoho_crm_oauth_connect');

    $settings = hj_get_zoho_crm_settings();
    $auth_url = hj_zoho_crm_build_oauth_authorize_url($settings);
    if ($auth_url === '') {
        hj_zoho_crm_set_oauth_status('missing_config', __('Missing OAuth config: Accounts URL or Client ID.', 'kneesurgery'));
        wp_safe_redirect(add_query_arg('hj_zoho_oauth', 'missing_config', hj_zoho_crm_get_settings_page_url()));
        exit;
    }

    hj_zoho_crm_set_oauth_status('started', __('OAuth connect started. Waiting for Zoho callback...', 'kneesurgery'));

    wp_redirect($auth_url);
    exit;
});

if (!function_exists('hj_zoho_crm_handle_oauth_callback')) {
    function hj_zoho_crm_handle_oauth_callback()
    {
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash((string) $_GET['state'])) : '';
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash((string) $_GET['code'])) : '';
    $error = isset($_GET['error']) ? sanitize_text_field(wp_unslash((string) $_GET['error'])) : '';

    $stored_state = get_option(HJ_ZOHO_CRM_OAUTH_STATE_OPTION, []);
    $stored_value = is_array($stored_state) ? (string) ($stored_state['value'] ?? '') : '';
    $stored_user = is_array($stored_state) ? (int) ($stored_state['user_id'] ?? 0) : 0;
    $expires_at = is_array($stored_state) ? (int) ($stored_state['expires_at'] ?? 0) : 0;
    $current_user = get_current_user_id();

    delete_option(HJ_ZOHO_CRM_OAUTH_STATE_OPTION);

    if ($error !== '') {
        hj_zoho_crm_set_oauth_status('denied', sprintf(__('Zoho returned error: %s', 'kneesurgery'), $error));
        wp_safe_redirect(add_query_arg('hj_zoho_oauth', 'denied', hj_zoho_crm_get_settings_page_url()));
        exit;
    }

    $user_mismatch = $stored_user > 0 && $current_user > 0 && $stored_user !== $current_user;
    if ($state === '' || $stored_value === '' || !hash_equals($stored_value, $state) || $user_mismatch || $expires_at < time()) {
        hj_zoho_crm_set_oauth_status('invalid_state', __('Invalid OAuth state, user mismatch, or expired session.', 'kneesurgery'));
        wp_safe_redirect(add_query_arg('hj_zoho_oauth', 'invalid_state', hj_zoho_crm_get_settings_page_url()));
        exit;
    }

    $settings = hj_get_zoho_crm_settings();
    $result = hj_zoho_crm_exchange_authorization_code($code, $settings);
    if (is_wp_error($result)) {
        hj_zoho_crm_set_oauth_status('exchange_failed', $result->get_error_message());
        wp_safe_redirect(add_query_arg('hj_zoho_oauth', 'exchange_failed', hj_zoho_crm_get_settings_page_url()));
        exit;
    }

    $status = empty($result['refresh_token']) ? 'connected_no_refresh' : 'connected';
    hj_zoho_crm_set_oauth_status(
        $status,
        empty($result['refresh_token'])
            ? __('Connected but refresh token was not returned.', 'kneesurgery')
            : __('Connected successfully and refresh token saved.', 'kneesurgery')
    );
    wp_safe_redirect(add_query_arg('hj_zoho_oauth', $status, hj_zoho_crm_get_settings_page_url()));
    exit;
    }
}

add_action('admin_post_hj_zoho_crm_oauth_callback', 'hj_zoho_crm_handle_oauth_callback');
add_action('admin_post_nopriv_hj_zoho_crm_oauth_callback', 'hj_zoho_crm_handle_oauth_callback');

add_action('admin_post_hj_zoho_crm_retry_now', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to retry Zoho queue.', 'kneesurgery'), 403);
    }

    check_admin_referer('hj_zoho_crm_retry_now');
    hj_zoho_crm_process_retry_queue(true);

    wp_safe_redirect(add_query_arg('hj_zoho_retry', 'done', hj_zoho_crm_get_settings_page_url()));
    exit;
});

add_action('admin_post_hj_zoho_crm_test_connection', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to test Zoho connection.', 'kneesurgery'), 403);
    }

    check_admin_referer('hj_zoho_crm_test_connection');

    $result = hj_send_zoho_crm_lead([
        'full_name' => 'KST Zoho Test',
        'email' => get_option('admin_email'),
        'phone' => '+000000000',
        'preferred_contact_method' => 'email',
        'country_code' => 'TR',
        'service' => 'Zoho Integration Test',
        'price' => '0',
        'source' => 'KST',
        'source_url' => hj_zoho_crm_get_settings_page_url(),
    ], false);

    $redirect = add_query_arg([
        'hj_zoho_test' => sanitize_key((string) ($result['status'] ?? 'failed')),
        'hj_zoho_test_code' => (int) ($result['code'] ?? 0),
    ], hj_zoho_crm_get_settings_page_url());

    wp_safe_redirect($redirect);
    exit;
});

add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'toplevel_page_theme-settings') {
        return;
    }

    $settings = hj_get_zoho_crm_settings();
    if (!empty($settings['enabled']) && !hj_zoho_crm_is_direct_mode($settings) && empty($settings['webhook_url'])) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Zoho CRM webhook mode is enabled, but webhook URL is missing. Leads cannot be delivered until you configure it.', 'kneesurgery') . '</p></div>';
    }

    $retry_status = isset($_GET['hj_zoho_retry']) ? sanitize_key(wp_unslash((string) $_GET['hj_zoho_retry'])) : '';
    if ($retry_status === 'done') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Zoho retry queue processed.', 'kneesurgery') . '</p></div>';
    }

    $test_status = isset($_GET['hj_zoho_test']) ? sanitize_key(wp_unslash((string) $_GET['hj_zoho_test'])) : '';
    $test_code = isset($_GET['hj_zoho_test_code']) ? absint($_GET['hj_zoho_test_code']) : 0;
    if ($test_status !== '') {
        $is_ok = in_array($test_status, ['sent', 'queued'], true);
        $class = $is_ok ? 'notice-success' : 'notice-error';
        $label = $is_ok
            ? __('Zoho test connection successful.', 'kneesurgery')
            : __('Zoho test connection failed.', 'kneesurgery');
        $suffix = $test_code > 0 ? ' (HTTP ' . $test_code . ')' : '';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($label . $suffix) . '</p></div>';
    }

    $oauth_status = isset($_GET['hj_zoho_oauth']) ? sanitize_key(wp_unslash((string) $_GET['hj_zoho_oauth'])) : '';
    if ($oauth_status !== '') {
        $messages = [
            'connected' => ['class' => 'notice-success', 'text' => __('Zoho OAuth connected and refresh token saved.', 'kneesurgery')],
            'connected_no_refresh' => ['class' => 'notice-warning', 'text' => __('Zoho OAuth connected, but refresh token was not returned. Re-authenticate and ensure consent is granted.', 'kneesurgery')],
            'missing_config' => ['class' => 'notice-error', 'text' => __('Missing Zoho OAuth configuration. Fill Accounts URL, Client ID and Client Secret first.', 'kneesurgery')],
            'invalid_state' => ['class' => 'notice-error', 'text' => __('Invalid or expired Zoho OAuth state. Please try connect again.', 'kneesurgery')],
            'exchange_failed' => ['class' => 'notice-error', 'text' => __('Zoho OAuth token exchange failed. Check credentials and redirect URI.', 'kneesurgery')],
            'denied' => ['class' => 'notice-error', 'text' => __('Zoho OAuth authorization was denied.', 'kneesurgery')],
        ];

        if (isset($messages[$oauth_status])) {
            $notice = $messages[$oauth_status];
            echo '<div class="notice ' . esc_attr($notice['class']) . ' is-dismissible"><p>' . esc_html($notice['text']) . '</p></div>';
        }
    }
});

if (!function_exists('hj_zoho_crm_get_status_message_html')) {
    function hj_zoho_crm_get_status_message_html()
    {
        $status = hj_zoho_crm_get_last_status();
        $queue_count = count(hj_zoho_crm_get_retry_queue());
        $status_label = (string) ($status['status'] ?? 'idle');
        $message = (string) ($status['message'] ?? __('No Zoho activity yet.', 'kneesurgery'));
        $code = (int) ($status['code'] ?? 0);
        $updated_at = (string) ($status['updated_at'] ?? '');

        $parts = [];
        $parts[] = '<p><strong>' . esc_html__('Zoho Status', 'kneesurgery') . ':</strong> ' . esc_html($status_label) . '</p>';
        $parts[] = '<p><strong>' . esc_html__('Message', 'kneesurgery') . ':</strong> ' . esc_html($message) . '</p>';
        $parts[] = '<p><strong>' . esc_html__('HTTP Code', 'kneesurgery') . ':</strong> ' . esc_html((string) $code) . '</p>';
        $parts[] = '<p><strong>' . esc_html__('Queue Size', 'kneesurgery') . ':</strong> ' . esc_html((string) $queue_count) . '</p>';
        $parts[] = '<p><strong>' . esc_html__('Delivery Mode', 'kneesurgery') . ':</strong> ' . esc_html(hj_zoho_crm_is_direct_mode(hj_get_zoho_crm_settings()) ? 'direct-crm-api' : 'webhook') . '</p>';
        $parts[] = '<p><strong>' . esc_html__('OAuth Redirect URI', 'kneesurgery') . ':</strong> <code>' . esc_html(hj_zoho_crm_get_oauth_redirect_uri()) . '</code></p>';

        $oauth = hj_zoho_crm_get_oauth_status();
        if (!empty($oauth)) {
            $parts[] = '<p><strong>' . esc_html__('OAuth Status', 'kneesurgery') . ':</strong> ' . esc_html((string) ($oauth['status'] ?? '-')) . '</p>';
            $parts[] = '<p><strong>' . esc_html__('OAuth Message', 'kneesurgery') . ':</strong> ' . esc_html((string) ($oauth['message'] ?? '-')) . '</p>';
            $parts[] = '<p><strong>' . esc_html__('OAuth Updated', 'kneesurgery') . ':</strong> ' . esc_html((string) ($oauth['updated_at'] ?? '-')) . '</p>';
        }

        $oauth_debug = hj_zoho_crm_get_oauth_debug();
        if (!empty($oauth_debug)) {
            $parts[] = '<p><strong>' . esc_html__('OAuth Debug HTTP', 'kneesurgery') . ':</strong> ' . esc_html((string) ($oauth_debug['http_code'] ?? '-')) . '</p>';
            $parts[] = '<p><strong>' . esc_html__('OAuth Debug Updated', 'kneesurgery') . ':</strong> ' . esc_html((string) ($oauth_debug['updated_at'] ?? '-')) . '</p>';
            $parts[] = '<p><strong>' . esc_html__('OAuth Debug Body', 'kneesurgery') . ':</strong> <code style="display:block;white-space:pre-wrap;">' . esc_html((string) ($oauth_debug['raw_body'] ?? '')) . '</code></p>';
        }

        if ($updated_at !== '') {
            $parts[] = '<p><strong>' . esc_html__('Last Updated', 'kneesurgery') . ':</strong> ' . esc_html($updated_at) . '</p>';
        }

        if ($queue_count > 0) {
            $parts[] = '<p><a class="button button-secondary" href="' . esc_url(hj_zoho_crm_get_retry_url()) . '">' . esc_html__('Retry Queue Now', 'kneesurgery') . '</a></p>';
        }

        $parts[] = '<p><a class="button button-secondary" href="' . esc_url(hj_zoho_crm_get_oauth_connect_url()) . '">' . esc_html__('Authenticate with Zoho', 'kneesurgery') . '</a></p>';
        $parts[] = '<p><a class="button button-primary" href="' . esc_url(hj_zoho_crm_get_test_url()) . '">' . esc_html__('Test Zoho Connection', 'kneesurgery') . '</a></p>';

        return implode('', $parts);
    }
}

add_filter('acf/load_field/key=field_zoho_crm_status_message', function ($field) {
    if (!function_exists('hj_zoho_crm_get_status_message_html')) {
        return $field;
    }

    $field['message'] = hj_zoho_crm_get_status_message_html();
    return $field;
});
