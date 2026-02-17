<?php
// CPT Doctors: ACF fields (name, short bio, related treatments)

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $group_array = [
        'key' => 'group_hj_doctors',
        'title' => 'Doctor Details',
        'fields' => [
            [
                'key' => 'field_hj_doctor_title',
                'label' => 'Title',
                'name' => 'doctor_title',
                'type' => 'text',
                'instructions' => 'Example: Professor of Orthopedics',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_doctor_short_bio',
                'label' => 'Short Bio',
                'name' => 'short_bio',
                'type' => 'textarea',
                'rows' => 4,
                'new_lines' => 'br',
                'required' => 0,
            ],
            [
                'key' => 'field_hj_doctor_education',
                'label' => 'Education',
                'name' => 'education',
                'type' => 'repeater',
                'button_label' => 'Add Item',
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_hj_doctor_education_item',
                        'label' => 'Item',
                        'name' => 'item',
                        'type' => 'textarea',
                        'rows' => 2,
                        'new_lines' => 'br',
                        'required' => 1,
                    ],
                ],
            ],
            [
                'key' => 'field_hj_doctor_medical_expertise',
                'label' => 'Medical Expertise',
                'name' => 'medical_expertise',
                'type' => 'repeater',
                'button_label' => 'Add Item',
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_hj_doctor_medical_expertise_item',
                        'label' => 'Item',
                        'name' => 'item',
                        'type' => 'textarea',
                        'rows' => 2,
                        'new_lines' => 'br',
                        'required' => 1,
                    ],
                ],
            ],
            [
                'key' => 'field_hj_doctor_clinical_focus',
                'label' => 'Clinical Focus',
                'name' => 'clinical_focus',
                'type' => 'repeater',
                'button_label' => 'Add Item',
                'layout' => 'table',
                'sub_fields' => [
                    [
                        'key' => 'field_hj_doctor_clinical_focus_item',
                        'label' => 'Item',
                        'name' => 'item',
                        'type' => 'textarea',
                        'rows' => 2,
                        'new_lines' => 'br',
                        'required' => 1,
                    ],
                ],
            ],
            [
                'key' => 'field_hj_doctor_current_position',
                'label' => 'Current Position',
                'name' => 'current_position',
                'type' => 'textarea',
                'rows' => 3,
                'new_lines' => 'br',
                'required' => 0,
                'instructions' => 'Example: Private Clinic\nMedstar Hospital, Antalya',
            ],
            [
                'key' => 'field_hj_doctor_treatments',
                'label' => 'Treatments',
                'name' => 'treatments',
                'type' => 'relationship',
                'instructions' => 'Select related Treatments for this doctor.',
                'post_type' => ['service'],
                'taxonomy' => [],
                'filters' => ['search'],
                'elements' => ['featured_image'],
                'return_format' => 'id',
                'min' => 0,
                'max' => 0,
            ],
        ],
        'location' => [
            [[ 'param' => 'post_type', 'operator' => '==', 'value' => 'doctor' ]],
        ],
        'position' => 'acf_after_title',
        'style' => 'seamless',
        'active' => true,
        'modified' => time(),
    ];

    acf_add_local_field_group($group_array);

    // Write to acf-json for Sync UI (best-effort).
    $json_dir = get_stylesheet_directory() . '/acf-json';
    if (!is_dir($json_dir)) {
        wp_mkdir_p($json_dir);
    }
    if (is_writable($json_dir)) {
        $json_file = $json_dir . '/group_hj_doctors.json';
        file_put_contents($json_file, wp_json_encode($group_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
});
