<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('hj_get_google_reviews_settings')) {
    function hj_get_google_reviews_settings() {
        $settings = [
            'api_key' => '',
            'place_id' => '',
            'language' => 'en',
            'reviews_sort' => 'most_relevant',
            'reviews_limit' => 0,
            'cache_hours' => 12,
            'archive_enabled' => true,
            'archive_limit' => 50,
            'archive_fetch_both_sorts' => true,
            'fallback_name' => '',
            'fallback_rating' => 0,
            'fallback_reviews_count' => 0,
            'fallback_url' => '',
        ];

        if (!function_exists('get_field')) {
            return $settings;
        }

        $settings['api_key'] = trim((string) get_field('google_reviews_api_key', 'option'));
        $settings['place_id'] = trim((string) get_field('google_reviews_place_id', 'option'));
        $settings['language'] = trim((string) get_field('google_reviews_language', 'option')) ?: 'en';
        $settings['reviews_sort'] = trim((string) get_field('google_reviews_reviews_sort', 'option')) === 'newest'
            ? 'newest'
            : 'most_relevant';

        $reviews_limit = (int) get_field('google_reviews_reviews_limit', 'option');
        if ($reviews_limit < 0) {
            $reviews_limit = 0;
        }
        $settings['reviews_limit'] = $reviews_limit;

        $cache_hours = (int) get_field('google_reviews_cache_hours', 'option');
        if ($cache_hours <= 0) {
            $cache_hours = 12;
        }
        $settings['cache_hours'] = max(1, min(168, $cache_hours));

        $archive_enabled = get_field('google_reviews_archive_enabled', 'option');
        if ($archive_enabled !== null) {
            $settings['archive_enabled'] = !empty($archive_enabled);
        }

        $archive_limit = (int) get_field('google_reviews_archive_limit', 'option');
        if ($archive_limit <= 0) {
            $archive_limit = 50;
        }
        $settings['archive_limit'] = max(5, min(250, $archive_limit));

        $archive_fetch_both_sorts = get_field('google_reviews_archive_fetch_both_sorts', 'option');
        if ($archive_fetch_both_sorts !== null) {
            $settings['archive_fetch_both_sorts'] = !empty($archive_fetch_both_sorts);
        }

        $settings['fallback_name'] = trim((string) get_field('google_reviews_fallback_name', 'option'));
        $settings['fallback_rating'] = max(0, min(5, (float) get_field('google_reviews_fallback_rating', 'option')));
        $settings['fallback_reviews_count'] = max(0, (int) get_field('google_reviews_fallback_reviews_count', 'option'));
        $settings['fallback_url'] = trim((string) get_field('google_reviews_fallback_url', 'option'));

        return $settings;
    }
}

if (!function_exists('hj_google_reviews_get_cache_version')) {
    function hj_google_reviews_get_cache_version() {
        return max(1, (int) get_option('hj_google_reviews_cache_version', 1));
    }
}

if (!function_exists('hj_google_reviews_get_cache_key')) {
    function hj_google_reviews_get_cache_key($settings) {
        return 'hj_google_reviews_' . md5(wp_json_encode([
            'version' => hj_google_reviews_get_cache_version(),
            'place_id' => (string) ($settings['place_id'] ?? ''),
            'language' => (string) ($settings['language'] ?? 'en'),
            'reviews_sort' => (string) ($settings['reviews_sort'] ?? 'most_relevant'),
        ]));
    }
}

if (!function_exists('hj_google_reviews_get_archive_key')) {
    function hj_google_reviews_get_archive_key($settings) {
        return 'hj_google_reviews_archive_' . md5(wp_json_encode([
            'place_id' => (string) ($settings['place_id'] ?? ''),
            'language' => (string) ($settings['language'] ?? 'en'),
        ]));
    }
}

if (!function_exists('hj_google_reviews_normalize_place_id')) {
    function hj_google_reviews_normalize_place_id($place_id) {
        $place_id = trim((string) $place_id);

        if (strpos($place_id, 'places/') === 0) {
            $place_id = substr($place_id, 7);
        }

        return $place_id;
    }
}

if (!function_exists('hj_google_reviews_get_public_reviews_url')) {
    function hj_google_reviews_get_public_reviews_url($settings, $fallback_url = '') {
        $place_id = hj_google_reviews_normalize_place_id($settings['place_id'] ?? '');

        if ($place_id !== '') {
            return add_query_arg([
                'placeid' => $place_id,
            ], 'https://search.google.com/local/reviews');
        }

        return trim((string) $fallback_url);
    }
}

if (!function_exists('hj_google_reviews_get_settings_page_url')) {
    function hj_google_reviews_get_settings_page_url($args = []) {
        $url = add_query_arg('page', 'theme-settings', admin_url('admin.php'));

        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }

        return $url;
    }
}

if (!function_exists('hj_google_reviews_get_refresh_url')) {
    function hj_google_reviews_get_refresh_url() {
        $request_url = add_query_arg([
            'action' => 'hj_google_reviews_refresh',
        ], admin_url('admin-post.php'));

        $request_url = add_query_arg([
            'redirect_to' => rawurlencode(hj_google_reviews_get_settings_page_url()),
        ], $request_url);

        return wp_nonce_url($request_url, 'hj_google_reviews_refresh');
    }
}

if (!function_exists('hj_google_reviews_get_last_refresh_summary')) {
    function hj_google_reviews_get_last_refresh_summary() {
        $summary = get_option('hj_google_reviews_last_refresh_summary', []);

        return is_array($summary) ? $summary : [];
    }
}

if (!function_exists('hj_google_reviews_set_last_refresh_summary')) {
    function hj_google_reviews_set_last_refresh_summary($summary) {
        if (!is_array($summary)) {
            $summary = [];
        }

        update_option('hj_google_reviews_last_refresh_summary', $summary, false);
    }
}

if (!function_exists('hj_google_reviews_get_review_signature')) {
    function hj_google_reviews_get_review_signature($review) {
        if (!is_array($review)) {
            return '';
        }

        $author_name = strtolower(trim(wp_strip_all_tags((string) ($review['author_name'] ?? ''))));
        $text = trim(wp_strip_all_tags((string) ($review['text'] ?? '')));
        $rating = max(0, min(5, (float) ($review['rating'] ?? 0)));
        $timestamp = (int) ($review['time'] ?? 0);

        if ($author_name === '' && $text === '' && $rating <= 0 && $timestamp <= 0) {
            return '';
        }

        return md5(wp_json_encode([
            'author_name' => $author_name,
            'text' => $text,
            'rating' => $rating,
            'time' => $timestamp,
        ]));
    }
}

if (!function_exists('hj_google_reviews_sort_reviews')) {
    function hj_google_reviews_sort_reviews($reviews) {
        if (!is_array($reviews)) {
            return [];
        }

        usort($reviews, static function ($left, $right) {
            $left_time = (int) ($left['time'] ?? 0);
            $right_time = (int) ($right['time'] ?? 0);

            if ($left_time !== $right_time) {
                return $right_time <=> $left_time;
            }

            $left_rating = (float) ($left['rating'] ?? 0);
            $right_rating = (float) ($right['rating'] ?? 0);

            if ($left_rating !== $right_rating) {
                return $right_rating <=> $left_rating;
            }

            return strcasecmp((string) ($left['author_name'] ?? ''), (string) ($right['author_name'] ?? ''));
        });

        return array_values($reviews);
    }
}

if (!function_exists('hj_google_reviews_merge_reviews')) {
    function hj_google_reviews_merge_reviews(...$review_sets) {
        $merged = [];
        $signatures = [];

        foreach ($review_sets as $review_set) {
            foreach ((array) $review_set as $review) {
                if (!is_array($review)) {
                    continue;
                }

                $signature = hj_google_reviews_get_review_signature($review);
                if ($signature === '' || isset($signatures[$signature])) {
                    continue;
                }

                $signatures[$signature] = true;
                $merged[] = $review;
            }
        }

        return hj_google_reviews_sort_reviews($merged);
    }
}

if (!function_exists('hj_google_reviews_get_archive')) {
    function hj_google_reviews_get_archive($settings) {
        if (empty($settings['archive_enabled'])) {
            return [];
        }

        $archive = get_option(hj_google_reviews_get_archive_key($settings), []);
        if (!is_array($archive)) {
            return [];
        }

        return hj_google_reviews_sort_reviews(array_values(array_filter($archive, 'is_array')));
    }
}

if (!function_exists('hj_google_reviews_update_archive')) {
    function hj_google_reviews_update_archive($settings, $reviews) {
        if (empty($settings['archive_enabled'])) {
            return [];
        }

        $archive_limit = max(5, (int) ($settings['archive_limit'] ?? 50));
        $archive = hj_google_reviews_merge_reviews(hj_google_reviews_get_archive($settings), $reviews);

        if ($archive_limit > 0) {
            $archive = array_slice($archive, 0, $archive_limit);
        }

        update_option(hj_google_reviews_get_archive_key($settings), $archive, false);

        return $archive;
    }
}

if (!function_exists('hj_google_reviews_apply_archive')) {
    function hj_google_reviews_apply_archive($payload, $settings) {
        if (empty($settings['archive_enabled'])) {
            return $payload;
        }

        $archive = !empty($payload['reviews'])
            ? hj_google_reviews_update_archive($settings, $payload['reviews'])
            : hj_google_reviews_get_archive($settings);

        $payload['reviews'] = hj_google_reviews_merge_reviews($payload['reviews'] ?? [], $archive);
        $payload['available_reviews_count'] = count($payload['reviews']);

        if ((int) ($payload['reviews_count'] ?? 0) <= 0 && $payload['available_reviews_count'] > 0) {
            $payload['reviews_count'] = $payload['available_reviews_count'];
        }

        return $payload;
    }
}

if (!function_exists('hj_google_reviews_format_rating')) {
    function hj_google_reviews_format_rating($rating) {
        $rating = max(0, min(5, (float) $rating));

        if ($rating <= 0) {
            return '';
        }

        return floor($rating) === $rating
            ? number_format($rating, 0)
            : number_format($rating, 1);
    }
}

if (!function_exists('hj_google_reviews_get_stars_text')) {
    function hj_google_reviews_get_stars_text($rating) {
        $rating = max(0, min(5, (float) $rating));
        $rounded = (int) round($rating);

        return $rounded > 0 ? str_repeat('★', $rounded) : '';
    }
}

if (!function_exists('hj_google_reviews_get_initials')) {
    function hj_google_reviews_get_initials($name) {
        $name = trim(wp_strip_all_tags((string) $name));

        if ($name === '') {
            return 'G';
        }

        $words = preg_split('/[\s\-&]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            $words = [$name];
        }

        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $letter = function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
            $initials .= function_exists('mb_strtoupper') ? mb_strtoupper($letter) : strtoupper($letter);
        }

        return $initials !== '' ? $initials : 'G';
    }
}

if (!function_exists('hj_google_reviews_get_fallback_payload')) {
    function hj_google_reviews_get_fallback_payload($settings) {
        return [
            'configured' => !empty($settings['api_key']) && !empty($settings['place_id']),
            'loaded' => false,
            'source' => 'fallback',
            'place_name' => (string) ($settings['fallback_name'] ?? ''),
            'rating' => (float) ($settings['fallback_rating'] ?? 0),
            'reviews_count' => (int) ($settings['fallback_reviews_count'] ?? 0),
            'available_reviews_count' => 0,
            'reviews_url' => hj_google_reviews_get_public_reviews_url($settings, (string) ($settings['fallback_url'] ?? '')),
            'reviews' => [],
            'rating_label' => '',
            'stars_text' => '',
            'has_summary' => false,
            'has_reviews' => false,
            'has_content' => false,
            'error' => '',
        ];
    }
}

if (!function_exists('hj_google_reviews_normalize_legacy_review')) {
    function hj_google_reviews_normalize_legacy_review($review) {
        if (!is_array($review)) {
            return null;
        }

        $author_name = trim(wp_strip_all_tags((string) ($review['author_name'] ?? '')));
        $text = trim(wp_strip_all_tags((string) ($review['text'] ?? '')));
        $rating = max(0, min(5, (float) ($review['rating'] ?? 0)));

        if ($author_name === '' && $text === '' && $rating <= 0) {
            return null;
        }

        return [
            'author_name' => $author_name !== '' ? $author_name : __('Google user', 'hello-elementor-child'),
            'author_initials' => hj_google_reviews_get_initials($author_name),
            'author_url' => trim((string) ($review['author_url'] ?? '')),
            'author_avatar' => trim((string) ($review['profile_photo_url'] ?? '')),
            'rating' => $rating,
            'stars_text' => hj_google_reviews_get_stars_text($rating),
            'relative_time' => trim(wp_strip_all_tags((string) ($review['relative_time_description'] ?? ''))),
            'text' => $text,
            'time' => !empty($review['time']) ? (int) $review['time'] : 0,
        ];
    }
}

if (!function_exists('hj_google_reviews_normalize_new_review')) {
    function hj_google_reviews_normalize_new_review($review) {
        if (!is_array($review)) {
            return null;
        }

        $author = is_array($review['authorAttribution'] ?? null) ? $review['authorAttribution'] : [];
        $text_block = is_array($review['text'] ?? null) ? $review['text'] : [];
        $original_text_block = is_array($review['originalText'] ?? null) ? $review['originalText'] : [];

        $author_name = trim(wp_strip_all_tags((string) ($author['displayName'] ?? '')));
        $text = trim(wp_strip_all_tags((string) ($text_block['text'] ?? $original_text_block['text'] ?? '')));
        $rating = max(0, min(5, (float) ($review['rating'] ?? 0)));

        if ($author_name === '' && $text === '' && $rating <= 0) {
            return null;
        }

        $publish_time = trim((string) ($review['publishTime'] ?? ''));
        $timestamp = $publish_time !== '' ? strtotime($publish_time) : false;

        return [
            'author_name' => $author_name !== '' ? $author_name : __('Google user', 'hello-elementor-child'),
            'author_initials' => hj_google_reviews_get_initials($author_name),
            'author_url' => trim((string) ($author['uri'] ?? '')),
            'author_avatar' => trim((string) ($author['photoUri'] ?? '')),
            'rating' => $rating,
            'stars_text' => hj_google_reviews_get_stars_text($rating),
            'relative_time' => trim(wp_strip_all_tags((string) ($review['relativePublishTimeDescription'] ?? ''))),
            'text' => $text,
            'time' => $timestamp ? (int) $timestamp : 0,
        ];
    }
}

if (!function_exists('hj_google_reviews_finalize_payload')) {
    function hj_google_reviews_finalize_payload($payload, $settings, $limit_override = null) {
        $payload['place_name'] = trim((string) ($payload['place_name'] ?? ''));
        $payload['rating'] = max(0, min(5, (float) ($payload['rating'] ?? 0)));
        $payload['reviews_count'] = max(0, (int) ($payload['reviews_count'] ?? 0));
        $payload['reviews_url'] = trim((string) ($payload['reviews_url'] ?? ''));
        $payload['reviews'] = is_array($payload['reviews'] ?? null) ? array_values($payload['reviews']) : [];
        $payload['available_reviews_count'] = max(0, (int) ($payload['available_reviews_count'] ?? count($payload['reviews'])));

        $limit = $limit_override === null
            ? max(0, (int) ($settings['reviews_limit'] ?? 0))
            : max(0, (int) $limit_override);

        if ($limit > 0) {
            $payload['reviews'] = array_slice($payload['reviews'], 0, $limit);
        }

        $formatted_rating = hj_google_reviews_format_rating($payload['rating']);
        $payload['rating_label'] = $formatted_rating !== ''
            ? sprintf(__('%s stars', 'hello-elementor-child'), $formatted_rating)
            : '';
        $payload['stars_text'] = hj_google_reviews_get_stars_text($payload['rating']);
        $payload['has_summary'] = $payload['place_name'] !== '' || $payload['rating'] > 0 || $payload['reviews_count'] > 0;
        $payload['has_reviews'] = !empty($payload['reviews']);
        $payload['has_content'] = $payload['has_summary'] || $payload['has_reviews'];

        return $payload;
    }
}

if (!function_exists('hj_google_reviews_fetch_new_api')) {
    function hj_google_reviews_fetch_new_api($settings) {
        $sort = ($settings['reviews_sort'] ?? 'most_relevant') === 'newest' ? 'NEWEST' : 'MOST_RELEVANT';
        $request_url = add_query_arg([
            'languageCode' => (string) ($settings['language'] ?? 'en'),
            'reviewsSort' => $sort,
        ], 'https://places.googleapis.com/v1/places/' . rawurlencode((string) ($settings['place_id'] ?? '')));

        $response = wp_remote_get($request_url, [
            'timeout' => 15,
            'headers' => [
                'X-Goog-Api-Key' => (string) ($settings['api_key'] ?? ''),
                'X-Goog-FieldMask' => 'displayName,rating,userRatingCount,reviews,googleMapsUri',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($status_code !== 200 || !is_array($body)) {
            $message = is_array($body) ? trim((string) ($body['error']['message'] ?? '')) : '';
            return new WP_Error('hj_google_reviews_new_api_failed', $message !== '' ? $message : __('Google Places API (new) request failed.', 'hello-elementor-child'));
        }

        $display_name = is_array($body['displayName'] ?? null) ? $body['displayName'] : [];
        $reviews = [];
        foreach ((array) ($body['reviews'] ?? []) as $review) {
            $normalized = hj_google_reviews_normalize_new_review($review);
            if ($normalized !== null) {
                $reviews[] = $normalized;
            }
        }

        return [
            'configured' => true,
            'loaded' => true,
            'source' => 'google_api_new',
            'place_name' => trim((string) ($display_name['text'] ?? '')),
            'rating' => (float) ($body['rating'] ?? 0),
            'reviews_count' => (int) ($body['userRatingCount'] ?? 0),
            'available_reviews_count' => count($reviews),
            'reviews_url' => hj_google_reviews_get_public_reviews_url($settings, trim((string) ($body['googleMapsUri'] ?? ''))),
            'reviews' => $reviews,
            'error' => '',
        ];
    }
}

if (!function_exists('hj_google_reviews_fetch_legacy_api')) {
    function hj_google_reviews_fetch_legacy_api($settings) {
        $request_url = add_query_arg([
            'place_id' => (string) ($settings['place_id'] ?? ''),
            'fields' => 'name,rating,user_ratings_total,reviews,url',
            'language' => (string) ($settings['language'] ?? 'en'),
            'reviews_sort' => (string) ($settings['reviews_sort'] ?? 'most_relevant'),
            'reviews_no_translations' => 'true',
            'key' => (string) ($settings['api_key'] ?? ''),
        ], 'https://maps.googleapis.com/maps/api/place/details/json');

        $response = wp_remote_get($request_url, [
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $api_status = is_array($body) ? trim((string) ($body['status'] ?? '')) : '';

        if ($status_code !== 200 || !is_array($body) || ($api_status !== '' && $api_status !== 'OK')) {
            $message = is_array($body) ? trim((string) ($body['error_message'] ?? $api_status)) : '';
            return new WP_Error('hj_google_reviews_legacy_api_failed', $message !== '' ? $message : __('Google Place Details request failed.', 'hello-elementor-child'));
        }

        $result = is_array($body['result'] ?? null) ? $body['result'] : [];
        $reviews = [];
        foreach ((array) ($result['reviews'] ?? []) as $review) {
            $normalized = hj_google_reviews_normalize_legacy_review($review);
            if ($normalized !== null) {
                $reviews[] = $normalized;
            }
        }

        return [
            'configured' => true,
            'loaded' => true,
            'source' => 'google_api_legacy',
            'place_name' => trim((string) ($result['name'] ?? '')),
            'rating' => (float) ($result['rating'] ?? 0),
            'reviews_count' => (int) ($result['user_ratings_total'] ?? 0),
            'available_reviews_count' => count($reviews),
            'reviews_url' => hj_google_reviews_get_public_reviews_url($settings, trim((string) ($result['url'] ?? ''))),
            'reviews' => $reviews,
            'error' => '',
        ];
    }
}

if (!function_exists('hj_google_reviews_fetch_live_payload')) {
    function hj_google_reviews_fetch_live_payload($settings) {
        $result = hj_google_reviews_fetch_new_api($settings);
        if (is_wp_error($result)) {
            $result = hj_google_reviews_fetch_legacy_api($settings);
        }

        if (is_wp_error($result) || empty($settings['archive_enabled']) || empty($settings['archive_fetch_both_sorts'])) {
            return $result;
        }

        $alternate_sort = ($settings['reviews_sort'] ?? 'most_relevant') === 'newest'
            ? 'most_relevant'
            : 'newest';
        $alternate_settings = $settings;
        $alternate_settings['reviews_sort'] = $alternate_sort;

        $alternate_result = hj_google_reviews_fetch_new_api($alternate_settings);
        if (is_wp_error($alternate_result)) {
            $alternate_result = hj_google_reviews_fetch_legacy_api($alternate_settings);
        }

        if (is_wp_error($alternate_result)) {
            return $result;
        }

        $result['reviews'] = hj_google_reviews_merge_reviews($result['reviews'] ?? [], $alternate_result['reviews'] ?? []);
        $result['available_reviews_count'] = count($result['reviews']);

        if (empty($result['reviews_url']) && !empty($alternate_result['reviews_url'])) {
            $result['reviews_url'] = $alternate_result['reviews_url'];
        }

        if (empty($result['place_name']) && !empty($alternate_result['place_name'])) {
            $result['place_name'] = $alternate_result['place_name'];
        }

        return $result;
    }
}

if (!function_exists('hj_get_google_reviews_data')) {
    function hj_get_google_reviews_data($args = []) {
        $settings = hj_get_google_reviews_settings();
        $limit_override = isset($args['limit']) ? (int) $args['limit'] : null;
        $force_refresh = !empty($args['force_refresh']);
        $payload = hj_google_reviews_get_fallback_payload($settings);

        if (empty($settings['api_key']) || empty($settings['place_id'])) {
            $payload = hj_google_reviews_apply_archive($payload, $settings);

            return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
        }

        $cache_key = hj_google_reviews_get_cache_key($settings);
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                return hj_google_reviews_finalize_payload($cached, $settings, $limit_override);
            }
        }

        $result = hj_google_reviews_fetch_live_payload($settings);

        if (is_wp_error($result)) {
            $payload['error'] = $result->get_error_message();
            $payload = hj_google_reviews_apply_archive($payload, $settings);
            set_transient($cache_key, $payload, max(15 * MINUTE_IN_SECONDS, (int) ($settings['cache_hours'] ?? 12) * HOUR_IN_SECONDS));
            return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
        }

        $payload = array_merge($payload, $result);
        $payload = hj_google_reviews_apply_archive($payload, $settings);
        set_transient($cache_key, $payload, max(HOUR_IN_SECONDS, (int) ($settings['cache_hours'] ?? 12) * HOUR_IN_SECONDS));

        return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
    }
}

if (!function_exists('hj_get_google_reviews_summary')) {
    function hj_get_google_reviews_summary() {
        return hj_get_google_reviews_data();
    }
}

if (!function_exists('hj_google_reviews_get_settings_message_html')) {
    function hj_google_reviews_get_settings_message_html() {
        $settings = hj_get_google_reviews_settings();
        $archive_count = count(hj_google_reviews_get_archive($settings));
        $last_refresh = hj_google_reviews_get_last_refresh_summary();
        $status = trim((string) ($last_refresh['status'] ?? ''));
        $status_message = trim((string) ($last_refresh['message'] ?? ''));
        $refreshed_at = !empty($last_refresh['refreshed_at']) ? (int) $last_refresh['refreshed_at'] : 0;
        $google_count = max(0, (int) ($last_refresh['google_count'] ?? 0));
        $available_count = max(0, (int) ($last_refresh['available_count'] ?? 0));

        $parts = [
            '<p>Direct Google Reviews integration for the theme. Add a server-side Places API key and Place ID. The theme caches responses, can archive fetched reviews inside WordPress, and uses the fallback values below if Google is unavailable.</p>',
        ];

        if (!empty($settings['api_key']) && !empty($settings['place_id'])) {
            $parts[] = '<p><a class="button button-secondary" href="' . esc_url(hj_google_reviews_get_refresh_url()) . '">' . esc_html__('Refresh Google Reviews Now', 'hello-elementor-child') . '</a></p>';
            $parts[] = '<p><strong>' . esc_html__('Local archive:', 'hello-elementor-child') . '</strong> ' . esc_html(sprintf(_n('%d unique review', '%d unique reviews', $archive_count, 'hello-elementor-child'), $archive_count)) . '.</p>';
        } else {
            $parts[] = '<p><strong>' . esc_html__('Manual refresh unavailable.', 'hello-elementor-child') . '</strong> ' . esc_html__('Add both the API key and Place ID first.', 'hello-elementor-child') . '</p>';
        }

        if ($status !== '' || $status_message !== '' || $refreshed_at > 0) {
            $status_label = $status === 'success'
                ? esc_html__('Last manual refresh succeeded.', 'hello-elementor-child')
                : esc_html__('Last manual refresh failed.', 'hello-elementor-child');

            $meta = [];
            if ($google_count > 0) {
                $meta[] = sprintf(
                    _n('%d review reported by Google', '%d reviews reported by Google', $google_count, 'hello-elementor-child'),
                    $google_count
                );
            }
            if ($available_count > 0) {
                $meta[] = sprintf(
                    _n('%d review currently available to the theme', '%d reviews currently available to the theme', $available_count, 'hello-elementor-child'),
                    $available_count
                );
            }
            if ($refreshed_at > 0) {
                $meta[] = sprintf(
                    __('updated %s ago', 'hello-elementor-child'),
                    human_time_diff($refreshed_at, current_time('timestamp'))
                );
            }

            $details = $status_message;
            if (!empty($meta)) {
                $details = trim($details . ' ' . implode(' | ', $meta));
            }

            $parts[] = '<p><strong>' . esc_html($status_label) . '</strong>' . ($details !== '' ? ' ' . esc_html($details) : '') . '</p>';
        }

        return implode('', $parts);
    }
}

add_filter('acf/load_field/key=field_google_reviews_message', function ($field) {
    if (!is_admin()) {
        return $field;
    }

    $field['message'] = hj_google_reviews_get_settings_message_html();

    return $field;
});

if (!function_exists('hj_google_reviews_get_dynamic_summary_message_html')) {
    function hj_google_reviews_get_dynamic_summary_message_html() {
        $summary = function_exists('hj_get_google_reviews_summary') ? (array) hj_get_google_reviews_summary() : [];
        $stars_text = trim((string) ($summary['stars_text'] ?? ''));
        $rating_label = trim((string) ($summary['rating_label'] ?? ''));
        $reviews_count = max(0, (int) ($summary['reviews_count'] ?? 0));

        if ($stars_text === '' && $rating_label === '' && $reviews_count <= 0) {
            return '<p>' . esc_html__('This block pulls the Google rating summary automatically from Theme Settings > Google Reviews. Configure the integration or fallback values there to populate it.', 'hello-elementor-child') . '</p>';
        }

        $parts = [];

        if ($stars_text !== '') {
            $parts[] = '<p><strong>' . esc_html($stars_text) . '</strong></p>';
        }

        if ($rating_label !== '') {
            $parts[] = '<p>' . esc_html($rating_label) . '</p>';
        }

        if ($reviews_count > 0) {
            $parts[] = '<p>' . esc_html(sprintf(_n('%d review', '%d reviews', $reviews_count, 'hello-elementor-child'), $reviews_count)) . '</p>';
        }

        $parts[] = '<p>' . esc_html__('Pulled automatically from Theme Settings > Google Reviews.', 'hello-elementor-child') . '</p>';

        return implode('', $parts);
    }
}

add_filter('acf/load_field/key=field_hj_vss_rating_dynamic_message', function ($field) {
    if (!is_admin()) {
        return $field;
    }

    $field['message'] = hj_google_reviews_get_dynamic_summary_message_html();

    return $field;
});

add_filter('acf/load_field/key=field_hj_tpi_rating_dynamic_message', function ($field) {
    if (!is_admin()) {
        return $field;
    }

    $field['message'] = hj_google_reviews_get_dynamic_summary_message_html();

    return $field;
});

add_action('admin_post_hj_google_reviews_refresh', function () {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to refresh Google reviews.', 'hello-elementor-child'), 403);
    }

    check_admin_referer('hj_google_reviews_refresh');

    $redirect_to = isset($_GET['redirect_to']) ? rawurldecode(wp_unslash((string) $_GET['redirect_to'])) : '';
    if ($redirect_to === '' || !wp_validate_redirect($redirect_to, false)) {
        $redirect_to = hj_google_reviews_get_settings_page_url();
    }

    $settings = hj_get_google_reviews_settings();
    if (empty($settings['api_key']) || empty($settings['place_id'])) {
        hj_google_reviews_set_last_refresh_summary([
            'status' => 'error',
            'message' => __('Missing Google Reviews API credentials.', 'hello-elementor-child'),
            'refreshed_at' => current_time('timestamp'),
            'google_count' => 0,
            'available_count' => 0,
        ]);

        wp_safe_redirect(add_query_arg('hj_google_reviews_refresh', 'error', $redirect_to));
        exit;
    }

    update_option('hj_google_reviews_cache_version', hj_google_reviews_get_cache_version() + 1, false);

    $payload = hj_get_google_reviews_data([
        'force_refresh' => true,
        'limit' => 0,
    ]);
    $summary = [
        'status' => empty($payload['error']) ? 'success' : 'error',
        'message' => trim((string) ($payload['error'] ?? '')),
        'refreshed_at' => current_time('timestamp'),
        'google_count' => max(0, (int) ($payload['reviews_count'] ?? 0)),
        'available_count' => max(0, (int) ($payload['available_reviews_count'] ?? count((array) ($payload['reviews'] ?? [])))),
    ];

    if ($summary['status'] === 'success' && $summary['message'] === '') {
        $summary['message'] = __('Live Google reviews fetched and local archive updated.', 'hello-elementor-child');
    }

    hj_google_reviews_set_last_refresh_summary($summary);

    wp_safe_redirect(add_query_arg('hj_google_reviews_refresh', $summary['status'], $redirect_to));
    exit;
});

add_action('admin_notices', function () {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : '';
    $refresh_status = isset($_GET['hj_google_reviews_refresh']) ? sanitize_key(wp_unslash((string) $_GET['hj_google_reviews_refresh'])) : '';

    if ($page !== 'theme-settings' || !in_array($refresh_status, ['success', 'error'], true)) {
        return;
    }

    $summary = hj_google_reviews_get_last_refresh_summary();
    $notice_class = $refresh_status === 'success' ? 'notice notice-success is-dismissible' : 'notice notice-error';
    $message = trim((string) ($summary['message'] ?? ''));

    if ($message === '') {
        $message = $refresh_status === 'success'
            ? __('Google reviews refreshed.', 'hello-elementor-child')
            : __('Google reviews refresh failed.', 'hello-elementor-child');
    }

    echo '<div class="' . esc_attr($notice_class) . '"><p>' . esc_html($message) . '</p></div>';
});

add_action('acf/save_post', function ($post_id) {
    if (!in_array($post_id, ['option', 'options'], true)) {
        return;
    }

    update_option('hj_google_reviews_cache_version', hj_google_reviews_get_cache_version() + 1, false);
}, 20);