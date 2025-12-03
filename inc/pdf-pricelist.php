<?php
/**
 * Generate Pricelist PDF from the Pricelist Accordion module using Dompdf
 */

// Handle front-end request: /?hj_pricelist_pdf=1&post=ID&_wpnonce=...
add_action('template_redirect', function(){
    if (empty($_GET['hj_pricelist_pdf'])) return;
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    if (!$post_id || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'hj_pricelist_pdf_' . $post_id)) {
        wp_die(__('Invalid request.', 'hj'));
    }

    if (!class_exists('Dompdf\\Dompdf')) {
        wp_die(__('PDF engine not found. Please run composer install in the child theme to install dompdf/dompdf.', 'hj'));
    }

    $html = hj_build_pricelist_pdf_html($post_id);

    // Generate PDF
    $dompdf = new Dompdf\Dompdf([
        'isRemoteEnabled' => true,
        'defaultPaperSize' => 'a4',
        'isHtml5ParserEnabled' => true,
    ]);
    $dompdf->loadHtml($html);
    $dompdf->render();

    $filename = 'healing-journey-pricelist-' . $post_id . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
});

// Handle front-end request for Package PDF: /?hj_package_pdf=1&post=ID&_wpnonce=...
add_action('template_redirect', function(){
  if (empty($_GET['hj_package_pdf'])) return;
  $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
  if (!$post_id || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'hj_package_pdf_' . $post_id)) {
    wp_die(__('Invalid request.', 'hj'));
  }

  if (!class_exists('Dompdf\\Dompdf')) {
    wp_die(__('PDF engine not found. Please run composer install in the child theme to install dompdf/dompdf.', 'hj'));
  }

  $html = hj_build_package_pdf_html($post_id);

  $dompdf = new Dompdf\Dompdf([
    'isRemoteEnabled' => true,
    'defaultPaperSize' => 'a4',
    'isHtml5ParserEnabled' => true,
  ]);
  $dompdf->loadHtml($html);
  $dompdf->render();

  $filename = 'healing-journey-package-' . $post_id . '.pdf';
  $dompdf->stream($filename, ['Attachment' => true]);
  exit;
});

/**
 * Build HTML for PDF rendering (A4)
 */
function hj_build_pricelist_pdf_html($post_id){
    $site = get_bloginfo('name');
    $doc_title = 'Healing Journey® - Dental Care Price List';
    $date = date_i18n(get_option('date_format'));
  $logo = get_stylesheet_directory_uri() . '/assets/img/HealingJourney-logo.svg';
  $currency = '€';

    // Find pricelist data from flexible content
    $sections = [];
    $package_summary = null;
    $rows = get_field('modules', $post_id) ?: [];
    foreach ($rows as $row){
        if (isset($row['acf_fc_layout']) && $row['acf_fc_layout'] === 'pricelist_accordion'){
        $sections = $row['sections'] ?? [];
        if (!empty($row['currency'])) { $currency = $row['currency']; }
            // Try to extract a package summary (title tab, first block and price)
            foreach ($sections as $sec){
              if (!empty($sec['package_view'])){
                $tab_title = $sec['section_title'] ?? '';
                $mode = $sec['package_content_mode'] ?? 'template';
                $pkg_title = '';
                $pkg_desc = '';
                $pkg_amount = '';
                $pkg_curr = '';
                if ($mode === 'template'){
                  $tpl = $sec['package_template'] ?? [];
                  $pkg_title = $tpl['title'] ?? '';
                  $pkg_desc = $tpl['subtitle'] ?? '';
                  $pr = $tpl['price'] ?? [];
                  $pkg_amount = $pr['amount'] ?? '';
                  $pkg_curr = $pr['currency'] ?? '';
                } elseif ($mode === 'wysiwyg') {
                  $wys = wp_strip_all_tags($sec['package_content'] ?? '');
                  $pkg_desc = trim($wys);
                } else {
                  $html = $sec['package_content_html'] ?? '';
                  $pkg_desc = trim(wp_strip_all_tags($html));
                }
                $package_summary = [
                  'tab' => $tab_title,
                  'title' => $pkg_title,
                  'desc' => $pkg_desc,
                  'amount' => $pkg_amount,
                  'curr' => $pkg_curr,
                ];
                break;
              }
            }
            break;
        }
    }

    // Partition sections: first two on page 1, others on page 2
    $first_sections = [];
    $other_sections = [];
    $c = 0;
    foreach ($sections as $s){
      $st = $s['section_title'] ?? '';
      $items = $s['items'] ?? [];
      if (!$st || empty($items)) continue;
      if ($c < 2) { $first_sections[] = $s; } else { $other_sections[] = $s; }
      $c++;
    }

    ob_start();
    ?>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        @page { margin: 24mm 16mm; }
        body{ font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#111827; font-size:12px; }
        .head{ display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 12px; gap:14px; }
        .head-left{ display:flex; align-items:center; gap:10px; }
        .head-right{ text-align:right; }
        .logo{ height:26px; }
        .logo img{ height:26px; width:auto; }
        .title{ font-size: 20px; font-weight:700; line-height:1.25; }
        .muted{ color:#6b7280; font-size:11px; margin-top:4px; }
        .card{ border:1px solid #e5e7eb; border-radius:12px; padding:14px 16px; margin:0 0 14px; page-break-inside: avoid; }
        .sect{ font-size:16px; font-weight:700; margin:12px 0 6px; page-break-after: avoid; }
        .pkg{ display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
        .pkg-main{ flex:1 1 auto; }
        .pkg-title{ font-weight:700; margin:0 0 3px; }
        .pkg-desc{ color:#374151; font-size:12px; }
        .pkg-price{ text-align:right; font-weight:800; white-space:nowrap; }
        .pkg-price .amt{ font-size:20px }
        table{ width:100%; border-collapse:collapse; font-size:12px; }
        th,td{ padding:7px 6px; border-bottom:1px dashed #3d86f5; }
        th{ text-align:left; font-size:11px; color:#6b7280; }
        td.price{ text-align:right; font-weight:600; white-space:nowrap; }
        .curr{ opacity:.8; margin-right:4px; }
        .desc{ color:#6b7280; font-size:11px; padding-top:4px; }
        .footer{ text-align:center; font-size:12px; color:#374151; margin-top: 18px; padding-top: 10px; border-top:1px solid #e5e7eb; }
        .page-break{ page-break-before: always; }
      </style>
    </head>
    <body>
      <div class="head">
        <div class="head-left">
          <span class="logo"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($site); ?>" /></span>
        </div>
        <div class="head-right">
          <div class="title"><?php echo esc_html($doc_title); ?></div>
          <div class="muted">Updated: <?php echo esc_html($date); ?></div>
        </div>
      </div>
      <?php foreach ($first_sections as $section): $st = $section['section_title'] ?? ''; $items = $section['items'] ?? []; ?>
        <div class="sect"><?php echo esc_html($st); ?></div>
        <div class="card">
          <table>
            <thead><tr><th>Service</th><th class="price">Price</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): $t = $it['item_title'] ?? ''; if (!$t) continue; $p = $it['item_price'] ?? ''; $d = $it['item_desc'] ?? ''; ?>
                <tr>
                  <td>
                    <div><?php echo esc_html($t); ?></div>
                    <?php if ($d): ?><div class="desc"><?php echo esc_html($d); ?></div><?php endif; ?>
                  </td>
                  <td class="price"><?php if ($p !== ''): ?><span class="curr"><?php echo esc_html($currency); ?></span><?php echo esc_html($p); ?><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>

      <?php if (!empty($other_sections) || $package_summary): ?>
        <div class="page-break"></div>
      <?php endif; ?>

      <?php foreach ($other_sections as $section): $st = $section['section_title'] ?? ''; $items = $section['items'] ?? []; ?>
        <div class="sect"><?php echo esc_html($st); ?></div>
        <div class="card">
          <table>
            <thead><tr><th>Service</th><th class="price">Price</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): $t = $it['item_title'] ?? ''; if (!$t) continue; $p = $it['item_price'] ?? ''; $d = $it['item_desc'] ?? ''; ?>
                <tr>
                  <td>
                    <div><?php echo esc_html($t); ?></div>
                    <?php if ($d): ?><div class="desc"><?php echo esc_html($d); ?></div><?php endif; ?>
                  </td>
                  <td class="price"><?php if ($p !== ''): ?><span class="curr"><?php echo esc_html($currency); ?></span><?php echo esc_html($p); ?><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>

      <?php if ($package_summary): ?>
        <div class="sect"><?php echo esc_html($package_summary['tab'] ?: 'Package Offer'); ?></div>
        <div class="card">
          <div class="pkg">
            <div class="pkg-main">
              <?php if ($package_summary['title']): ?><div class="pkg-title"><?php echo esc_html($package_summary['title']); ?></div><?php endif; ?>
              <?php if ($package_summary['desc']): ?><div class="pkg-desc"><?php echo nl2br(esc_html($package_summary['desc'])); ?></div><?php endif; ?>
            </div>
            <?php if ($package_summary['amount'] || $package_summary['curr']): ?>
              <div class="pkg-price"><span class="amt"><?php echo esc_html($package_summary['amount']); ?></span> <span class="curr"><?php echo esc_html($package_summary['curr']); ?></span></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="footer">
        <div>Prices are indicative; final quote after consultation.</div>
        <div style="margin-top:8px; font-weight:700;">Healing Journey®</div>
        <div>Medical Travel Facilitator</div>
        <div>Fener Mah. Fener Cd. No:11, Fener İş Merkezi, B2 Blok, kapı no:204 Muratpaşa/Antalya/TÜRKİYE</div>
        <div>(Phone +90242 323 0112)</div>
        <div>email: info@healingjourney.travel</div>
      </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Build HTML for Package PDF (A4)
 */
function hj_build_package_pdf_html($post_id){
  $site = get_bloginfo('name');
  $date = date_i18n(get_option('date_format'));
  $logo = get_stylesheet_directory_uri() . '/assets/img/HealingJourney-logo.svg';
  $template_bg = get_stylesheet_directory_uri() . '/assets/files/template/CoverPdf.png';
  
  // Get PDF settings from ACF options
  $cover_image = get_field('pdf_cover_image', 'option') ?: '';
  $cover_title = get_field('pdf_cover_title', 'option') ?: 'ACL & MCL Reconstruction Surgery';
  $cover_subtitle = get_field('pdf_cover_subtitle', 'option') ?: 'Healing Package';
  $cover_tagline = get_field('pdf_cover_tagline', 'option') ?: 'Experience Peace Of Mind Together With Us In Turkey.';
  $social_instagram = get_field('pdf_social_instagram', 'option') ?: '';
  $social_youtube = get_field('pdf_social_youtube', 'option') ?: '';
  $social_twitter = get_field('pdf_social_twitter', 'option') ?: '';
  $social_facebook = get_field('pdf_social_facebook', 'option') ?: '';
  $social_website = get_field('pdf_social_website', 'option') ?: 'www.healingjourney.travel';

  // Find first package section
  $pkg = [];
  $rows = get_field('modules', $post_id) ?: [];
  foreach ($rows as $row){
    if (($row['acf_fc_layout'] ?? '') === 'pricelist_accordion'){
      $sections = $row['sections'] ?? [];
      foreach ($sections as $sec){
        if (!empty($sec['package_view'])){
          $mode = $sec['package_content_mode'] ?? 'template';
          if ($mode === 'template') { $pkg = $sec['package_template'] ?? []; }
          elseif ($mode === 'wysiwyg') { $pkg = [ 'wysiwyg' => $sec['package_content'] ?? '' ]; }
          else { $pkg = [ 'html' => $sec['package_content_html'] ?? '' ]; }
          break 2;
        }
      }
    }
  }

  ob_start();
  ?>
  <html>
  <head>
    <meta charset="utf-8">
    <style>
      @page { margin: 0; size: A4 portrait; }
      body{ font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#111827; font-size:12px; margin:0; padding:0; }
      
      /* Cover Page with Template Background */
      .cover-page{ 
        width: 210mm;
        height: 297mm;
        page-break-after: always;
        position: relative;
        <?php if ($cover_image): ?>
        background-image: url('<?php echo esc_url($cover_image); ?>');
        background-size: 520px auto;
        background-position: center 200px;
        background-repeat: no-repeat;
        <?php endif; ?>
      }
      .cover-overlay{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo esc_url($template_bg); ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      }
      .cover-logo{ 
        position: absolute;
        top: 40px;
        left: 40px;
        z-index: 20;
      }
      .cover-logo img{ 
        height: 40px;
        width: auto;
      }
      .cover-title-section{
        position: absolute;
        top: 620px;
        left: 50%;
        margin-left: -325px;
        width: 650px;
        text-align: center;
        z-index: 10;
      }
      .cover-title{
        font-size: 28px;
        font-weight: 700;
        color: #4b8ff5;
        margin: 0 0 12px 0;
        line-height: 1.3;
      }
      .cover-description{
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        line-height: 1.4;
      }
      .cover-footer{
        position: absolute;
        bottom: 30px;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 10;
      }
      .cover-social{
        font-size: 10px;
        color: #6b7280;
        margin-bottom: 8px;
      }
      .cover-social span{
        margin: 0 8px;
      }
      .cover-website{
        font-size: 11px;
        color: #1f2937;
        font-weight: 600;
      }
      
      /* Content Pages Styles */
      .content-page{
        padding: 24mm 16mm;
      }
      .head{ margin-bottom: 12px; }
      .head table{ width: 100%; }
      .head-left{ text-align: left; }
      .head-right{ text-align: right; }
      .logo{ height:26px; }
      .logo img{ height:26px; width:auto; }
      .title{ font-size: 20px; font-weight:700; line-height:1.25; }
      .muted{ color:#6b7280; font-size:11px; margin-top:4px; }
      .card{ border:1px solid #e5e7eb; border-radius:12px; padding:12px 14px; margin:0 0 10px; page-break-inside: avoid; }
      .sect{ font-size:16px; font-weight:700; margin:10px 0 8px; page-break-after: avoid; }
      ul{ margin:8px 0 10px 16px; page-break-inside: avoid; }
      li{ margin:4px 0; }
      .note{ color:#6b7280; }
      .price{ font-weight:800; }
      .price table{ width: 100%; }
      .price-amt{ font-size:22px }
      .currency{ opacity:.85 }
      .footer{ text-align:center; font-size:12px; color:#374151; margin-top: 18px; padding-top: 10px; border-top:1px solid #e5e7eb; }
    </style>
  </head>
  <body>
    
    <!-- Cover Page -->
    <div class="cover-page">
      <div class="cover-overlay"></div>
      
      <div class="cover-logo">
        <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($site); ?>" />
      </div>
      
      <div class="cover-title-section">
        <div class="cover-title"><?php echo esc_html($cover_title); ?></div>
        <div class="cover-description"><?php echo nl2br(esc_html($cover_subtitle)); ?></div>
      </div>
      
      <div class="cover-footer">
        <div class="cover-social">
          <?php if ($social_instagram): ?>
            <span>📷 <?php echo esc_html($social_instagram); ?></span>
          <?php endif; ?>
          <?php if ($social_youtube): ?>
            <span>▶ <?php echo esc_html($social_youtube); ?></span>
          <?php endif; ?>
          <?php if ($social_twitter): ?>
            <span>𝕏 <?php echo esc_html($social_twitter); ?></span>
          <?php endif; ?>
          <?php if ($social_facebook): ?>
            <span>f <?php echo esc_html($social_facebook); ?></span>
          <?php endif; ?>
        </div>
        <div class="cover-website"><?php echo esc_html($social_website); ?></div>
      </div>
    </div>
    
    <!-- Content Pages -->
    <div class="content-page">
    <div class="head">
      <table cellpadding="0" cellspacing="0">
        <tr>
          <td class="head-left">
            <span class="logo"><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($site); ?>" /></span>
          </td>
          <td class="head-right">
            <div class="title"><?php echo esc_html($cover_title); ?></div>
            <div class="muted">Updated: <?php echo esc_html($date); ?></div>
          </td>
        </tr>
      </table>
    </div>

    <?php if (!empty($pkg['wysiwyg'])): ?>
      <div class="card"><?php echo apply_filters('the_content', $pkg['wysiwyg']); ?></div>
    <?php elseif (!empty($pkg['html'])): ?>
      <div class="card"><?php echo do_shortcode($pkg['html']); ?></div>
    <?php else: ?>
      <?php 
        $pt = $pkg['title'] ?? '';
        $ps = $pkg['subtitle'] ?? '';
        if ($pt): ?><div class="sect"><?php echo esc_html($pt); ?></div><?php endif; ?>
        <?php if ($ps): ?><div class="note"><?php echo wp_kses_post(nl2br($ps)); ?></div><?php endif; ?>

        <?php $highs = $pkg['highlights'] ?? []; if(!empty($highs)): ?>
          <div class="sect">Highlights</div>
          <div class="card">
            <ul>
              <?php foreach ($highs as $h): $t = is_array($h)?($h['text']??''):$h; if(!$t) continue; ?>
                <li><?php echo esc_html($t); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php $fd = $pkg['full_details'] ?? []; $pset = $fd['paragraphs'] ?? []; if(!empty($pset) || !empty($fd['subheading'])): ?>
          <div class="sect"><?php echo esc_html($fd['title'] ?? 'Full Details'); ?></div>
          <div class="card">
            <?php if(!empty($fd['subheading'])): ?><div><strong><?php echo esc_html($fd['subheading']); ?></strong></div><?php endif; ?>
            <?php foreach ($pset as $p): $txt = is_array($p)?($p['p']??''):$p; if(!$txt) continue; ?><p><?php echo wp_kses_post($txt); ?></p><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php $ms = $pkg['medical'] ?? []; if(!empty($ms['intro']) || !empty($ms['list']) || !empty($ms['note'])): ?>
          <div class="sect"><?php echo esc_html($ms['title'] ?? 'Medical Suitability Assessment'); ?></div>
          <div class="card">
            <?php if(!empty($ms['intro'])): ?><p><?php echo wp_kses_post($ms['intro']); ?></p><?php endif; ?>
            <?php if(!empty($ms['list'])): ?><ul><?php foreach ($ms['list'] as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if(!empty($ms['note'])): ?><p class="note"><?php echo wp_kses_post($ms['note']); ?></p><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php $ov = $pkg['overview'] ?? []; $v1 = $ov['visit1_list'] ?? []; $v2 = $ov['visit2_list'] ?? []; if(!empty($ov) && (!empty($v1) || !empty($v2) || !empty($ov['intro']))): ?>
          <div class="sect"><?php echo esc_html($ov['title'] ?? 'Package Overview'); ?></div>
          <div class="card">
            <?php if(!empty($ov['intro'])): ?><p><?php echo wp_kses_post($ov['intro']); ?></p><?php endif; ?>
            <?php if(!empty($ov['visit1_title'])): ?><div><strong><?php echo esc_html($ov['visit1_title']); ?></strong></div><?php endif; ?>
            <?php if(!empty($v1)): ?><ul><?php foreach ($v1 as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if(!empty($ov['visit2_title'])): ?><div><strong><?php echo esc_html($ov['visit2_title']); ?></strong></div><?php endif; ?>
            <?php if(!empty($v2)): ?><ul><?php foreach ($v2 as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if(!empty($ov['note'])): ?><p class="note"><?php echo esc_html($ov['note']); ?></p><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php $in = $pkg['inclusions'] ?? []; $surg = $in['surg_list'] ?? []; $sup = $in['sup_list'] ?? []; if(!empty($surg) || !empty($sup)): ?>
          <div class="sect"><?php echo esc_html($in['title'] ?? 'What the Package Includes'); ?></div>
          <div class="card">
            <?php if(!empty($in['surg_title'])): ?><div><strong><?php echo esc_html($in['surg_title']); ?></strong></div><?php endif; ?>
            <?php if(!empty($surg)): ?><ul><?php foreach ($surg as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if(!empty($in['sup_title'])): ?><div><strong><?php echo esc_html($in['sup_title']); ?></strong></div><?php endif; ?>
            <?php if(!empty($sup)): ?><ul><?php foreach ($sup as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php $tr = $pkg['travel'] ?? []; if(!empty($tr['list'])): ?>
          <div class="sect"><?php echo esc_html($tr['title'] ?? 'Travel & Accommodation'); ?></div>
          <div class="card">
            <ul><?php foreach ($tr['list'] as $li): $t=is_array($li)?($li['text']??''):$li; if(!$t) continue; ?><li><?php echo esc_html($t); ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <?php $pr = $pkg['price'] ?? []; if(!empty($pr['amount']) || !empty($pr['note'])): ?>
          <div class="sect"><?php echo esc_html($pr['title'] ?? 'Final Full-Arch Restoration (Single Arch)'); ?></div>
          <div class="card">
            <div class="price"><span class="price-amt"><?php echo esc_html($pr['amount'] ?? ''); ?></span><span class="currency"><?php echo esc_html($pr['currency'] ?? ''); ?></span></div>
            <?php if(!empty($pr['note'])): ?><p class="note"><?php echo esc_html($pr['note']); ?></p><?php endif; ?>
          </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="footer">
      <div>Prices are indicative; final quote after consultation.</div>
      <div style="margin-top:8px; font-weight:700;">Healing Journey®</div>
      <div>Medical Travel Facilitator</div>
      <div>Fener Mah. Fener Cd. No:11, Fener İş Merkezi, B2 Blok, kapı no:204 Muratpaşa/Antalya/TÜRKİYE</div>
      <div>(Phone +90242 323 0112)</div>
      <div>email: info@healingjourney.travel</div>
    </div>
    </div><!-- .content-page -->
  </body>
  </html>
  <?php
  return ob_get_clean();
}

/**
 * Helper to get signed URL for current page
 */
function hj_get_pricelist_pdf_url($post_id){
    $args = [ 'hj_pricelist_pdf' => 1, 'post' => $post_id, '_wpnonce' => wp_create_nonce('hj_pricelist_pdf_' . $post_id) ];
    return add_query_arg($args, home_url('/'));
}

/**
 * Generate a PDF file to a temporary path and return its full path
 */
function hj_generate_pricelist_pdf_to_file($post_id){
  if (!class_exists('Dompdf\\Dompdf')) return new WP_Error('no_engine','PDF engine missing');
  $html = hj_build_pricelist_pdf_html($post_id);
  $dompdf = new Dompdf\Dompdf([
    'isRemoteEnabled' => true,
    'defaultPaperSize' => 'a4',
    'isHtml5ParserEnabled' => true,
  ]);
  $dompdf->loadHtml($html);
  $dompdf->render();
  $output = $dompdf->output();
  $tmp = wp_tempnam('hj-pricelist.pdf');
  if (!$tmp) return new WP_Error('tmp_failed','Cannot create temp file');
  file_put_contents($tmp, $output);
  return $tmp;
}

// AJAX: send PDF by email
add_action('wp_ajax_hj_send_pricelist_pdf', 'hj_ajax_send_pricelist_pdf');
add_action('wp_ajax_nopriv_hj_send_pricelist_pdf', 'hj_ajax_send_pricelist_pdf');
function hj_ajax_send_pricelist_pdf(){
  check_ajax_referer('hj_send_pricelist_pdf');
  $email = sanitize_email($_POST['email'] ?? '');
  $post_id = intval($_POST['post_id'] ?? 0);
  $generate = isset($_POST['generate']) && $_POST['generate'] == '1';
  $pdf_id = intval($_POST['pdf_id'] ?? 0);
  if(!$email || !is_email($email) || !$post_id){
    wp_send_json_error('Invalid data');
  }

  $file = '';
  if($generate){
    $tmp = hj_generate_pricelist_pdf_to_file($post_id);
    if(is_wp_error($tmp)) wp_send_json_error($tmp->get_error_message());
    $file = $tmp;
  } else if($pdf_id){
    $path = get_attached_file($pdf_id);
    if(!$path || !file_exists($path)) wp_send_json_error('File missing');
    $file = $path;
  } else {
    // Try to fallback to generator anyway
    $tmp = hj_generate_pricelist_pdf_to_file($post_id);
    if(is_wp_error($tmp)) wp_send_json_error($tmp->get_error_message());
    $file = $tmp;
  }

  $subject = sprintf(__('Pricelist from %s','hj'), get_bloginfo('name'));
  $message = __('Please find attached the latest pricelist (PDF).','hj');
  $headers = ['Content-Type: text/plain; charset=UTF-8'];
  $sent = wp_mail($email, $subject, $message, $headers, [$file]);

  if($sent){
    wp_send_json_success('sent');
  } else {
    wp_send_json_error('Could not send email');
  }
}

/**
 * Helper to get signed URL for package pdf
 */
function hj_get_package_pdf_url($post_id){
  $args = [ 'hj_package_pdf' => 1, 'post' => $post_id, '_wpnonce' => wp_create_nonce('hj_package_pdf_' . $post_id) ];
  return add_query_arg($args, home_url('/'));
}
