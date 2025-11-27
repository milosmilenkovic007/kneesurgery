<?php
// Seed default values for the structured Package Template when empty
// Applies to group field key: field_hj_pa_pkg_group

add_filter('acf/load_value/key=field_hj_pa_pkg_group', function ($value, $post_id, $field) {
    if (!empty($value)) return $value;

    $defaults = [
        'slug' => 'all-on-4',
        'title' => 'All-on-4 Treatment Package (Single Arch)',
        'subtitle' => "Implant Placement + Temporary Prosthesis + Final Zirconia Bridge\nTwo Visits to Antalya – Flights, Hotel, Transfers & Medical Coordination Included",
        'highlights' => [
            [ 'text' => 'Complete Single-Arch All-on-4 Treatment Abroad — implant surgery, temporary prosthesis & final zirconia bridge included.' ],
            [ 'text' => 'Two Visits to Antalya — structured around international patients’ schedules for safe, predictable outcomes.' ],
            [ 'text' => 'Round-Trip Direct Flights from the UK to Antalya Included.' ],
            [ 'text' => 'Hotel Accommodation at our partner hotel in Antalya for 7 + 7 nights (B&B).' ],
            [ 'text' => 'All Airport–Hotel–Clinic Transfers included for both visits.' ],
            [ 'text' => 'Dedicated English-speaking Patient Coordinator accompanying you at every appointment.' ],
            [ 'text' => 'Full Medical Travel Coordination from pre-assessment to post-treatment follow-up.' ],
            [ 'text' => 'Perfect for individuals seeking a fixed, long-term solution after significant tooth loss.' ],
        ],
        'full_details' => [
            'title' => 'Full Details',
            'subheading' => 'All-on-4 Treatment Abroad',
            'paragraphs' => [
                [ 'p' => 'All-on-4 is one of the most advanced fixed full-arch implant solutions, ideal for patients who require a stable, long-term alternative to removable dentures or have widespread tooth loss. Because it involves surgery, prosthetic planning and a multi-stage healing process, this treatment demands precision and coordinated care—especially when performed in another country.' ],
                [ 'p' => 'Access is not enough when it comes to treatment abroad. Successful results come from clinical expertise, structured planning and continuous support. As your medical travel facilitator, we ensure your entire journey is medically organised, safely managed and supported at every step of your dental treatment abroad.' ],
            ],
        ],
        'medical' => [
            'title' => 'Medical Suitability Assessment',
            'intro' => 'To confirm whether the All-on-4 protocol is clinically appropriate, we require:',
            'list' => [
                [ 'text' => 'A recent panoramic X-ray or CBCT scan' ],
                [ 'text' => 'An online consultation with our implant surgeons' ],
            ],
            'note' => 'A personalised treatment plan, schedule and medical briefing are prepared following your assessment.',
        ],
        'overview' => [
            'title' => 'All-on-4 Package Overview (Single Arch)',
            'intro' => 'Your program includes two clinical visits to Antalya, Turkey, typically three months apart:',
            'visit1_title' => 'Visit 1 – Surgical Phase (7 Nights)',
            'visit1_list' => [
                [ 'text' => 'Placement of 4 premium Swiss implants (R-MINSIN)' ],
                [ 'text' => 'Multi-unit abutments when required' ],
                [ 'text' => 'Immediate temporary fixed prosthesis' ],
                [ 'text' => 'Post-operative checks and healing guidance' ],
            ],
            'visit2_title' => 'Visit 2 – Prosthetic Phase (7 Nights)',
            'visit2_list' => [
                [ 'text' => 'Digital impressions and functional adjustments' ],
                [ 'text' => 'Try-in appointments (PMMA / zirconia)' ],
                [ 'text' => 'Placement of the definitive zirconia full-arch restoration' ],
                [ 'text' => 'Fit, bite and aesthetic refinements' ],
                [ 'text' => 'Final documentation before departure' ],
            ],
            'note' => 'Both visits are scheduled to ensure predictable healing and long-term stability.',
        ],
        'inclusions' => [
            'title' => 'What the Package Includes (Single Arch)',
            'surg_title' => 'Surgical & Clinical Inclusions',
            'surg_list' => [
                [ 'text' => 'All single-arch All-on-4 surgical procedures' ],
                [ 'text' => '4 premium Swiss implants (R-MINSIN)' ],
                [ 'text' => 'Multi-unit abutments' ],
                [ 'text' => 'Immediate temporary prosthesis (Visit 1)' ],
                [ 'text' => 'Definitive full-arch prosthesis (Visit 2)' ],
                [ 'text' => 'Panoramic X-rays, CBCT scans (when required), digital impressions' ],
                [ 'text' => 'All surgical materials, implant components and clinical consumables' ],
                [ 'text' => 'Post-operative medications' ],
                [ 'text' => 'Routine clinical follow-ups' ],
            ],
            'sup_title' => 'Support & Coordination',
            'sup_list' => [
                [ 'text' => 'Dedicated patient coordinator accompanying you at every appointment' ],
                [ 'text' => 'In-person guidance and English-speaking translation support' ],
                [ 'text' => 'Comprehensive medical travel coordination' ],
                [ 'text' => 'Documentation management, scheduling and structured follow-up between and after visits' ],
            ],
        ],
        'travel' => [
            'title' => 'Travel & Accommodation',
            'list' => [
                [ 'text' => 'Round-trip direct flights from the UK to Antalya' ],
                [ 'text' => 'Accommodation at our partner hotel in Antalya (7 + 7 nights, bed & breakfast)' ],
                [ 'text' => 'All airport–hotel–clinic transfers' ],
                [ 'text' => 'Local travel assistance and support throughout your stay' ],
            ],
        ],
        'price' => [
            'title' => 'Final Full-Arch Restoration (Single Arch)',
            'amount' => '3999',
            'currency' => 'GBP',
            'note' => 'A single-arch monolithic zirconia full-arch bridge on a custom titanium framework, engineered for long-term strength, stability and natural aesthetics.',
        ],
    ];

    return $defaults;
}, 10, 3);
