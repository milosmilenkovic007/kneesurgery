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
            'reviews_limit' => 5,
            'cache_hours' => 12,
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
        if ($reviews_limit <= 0) {
            $reviews_limit = 5;
        }
        $settings['reviews_limit'] = max(1, min(5, $reviews_limit));

        $cache_hours = (int) get_field('google_reviews_cache_hours', 'option');
        if ($cache_hours <= 0) {
            $cache_hours = 12;
        }
        $settings['cache_hours'] = max(1, min(168, $cache_hours));

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
            'reviews_url' => (string) ($settings['fallback_url'] ?? ''),
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

        $limit = $limit_override === null
            ? (int) ($settings['reviews_limit'] ?? 5)
            : max(1, min(5, (int) $limit_override));

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
            'reviews_url' => trim((string) ($body['googleMapsUri'] ?? '')),
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
            'reviews_url' => trim((string) ($result['url'] ?? '')),
            'reviews' => $reviews,
            'error' => '',
        ];
    }
}

if (!function_exists('hj_get_google_reviews_data')) {
    function hj_get_google_reviews_data($args = []) {
        $settings = hj_get_google_reviews_settings();
        $limit_override = isset($args['limit']) ? (int) $args['limit'] : null;
        $force_refresh = !empty($args['force_refresh']);
        $payload = hj_google_reviews_get_fallback_payload($settings);

        if (empty($settings['api_key']) || empty($settings['place_id'])) {
            return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
        }

        $cache_key = hj_google_reviews_get_cache_key($settings);
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                return hj_google_reviews_finalize_payload($cached, $settings, $limit_override);
            }
        }

        $result = hj_google_reviews_fetch_new_api($settings);
        if (is_wp_error($result)) {
            $result = hj_google_reviews_fetch_legacy_api($settings);
        }

        if (is_wp_error($result)) {
            $payload['error'] = $result->get_error_message();
            set_transient($cache_key, $payload, max(15 * MINUTE_IN_SECONDS, (int) ($settings['cache_hours'] ?? 12) * HOUR_IN_SECONDS));
            return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
        }

        $payload = array_merge($payload, $result);
        set_transient($cache_key, $payload, max(HOUR_IN_SECONDS, (int) ($settings['cache_hours'] ?? 12) * HOUR_IN_SECONDS));

        return hj_google_reviews_finalize_payload($payload, $settings, $limit_override);
    }
}

if (!function_exists('hj_get_google_reviews_summary')) {
    function hj_get_google_reviews_summary() {
        return hj_get_google_reviews_data();
    }
}

add_action('acf/save_post', function ($post_id) {
    if (!in_array($post_id, ['option', 'options'], true)) {
        return;
    }

    update_option('hj_google_reviews_cache_version', hj_google_reviews_get_cache_version() + 1, false);
}, 20);