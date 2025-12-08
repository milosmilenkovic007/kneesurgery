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
  $cover_bg = get_stylesheet_directory_uri() . '/assets/files/template/hjcover.jpg';
  $phone_icon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="#7C7C7C" stroke-width="1.5" stroke-linejoin="round"></path></svg>');
  
  // Get package title from first package section
  $package_title = 'Treatment Package';
  $rows = get_field('modules', $post_id) ?: [];
  foreach ($rows as $row){
    if (($row['acf_fc_layout'] ?? '') === 'pricelist_accordion'){
      $sections = $row['sections'] ?? [];
      foreach ($sections as $sec){
        if (!empty($sec['package_view'])){
          $mode = $sec['package_content_mode'] ?? 'template';
          if ($mode === 'template') { 
            $tpl = $sec['package_template'] ?? [];
            $package_title = $tpl['title'] ?? $package_title;
          }
          break 2;
        }
      }
    }
  }

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
      @page { 
        margin: 0; 
        size: A4 portrait; 
      }
      /* Cover page keeps full-bleed */
      @page :first { margin: 0; }
      /* All content pages get consistent top/bottom spacing (~40px top) */
      @page content { margin: 24mm 0 35mm 0; }
      body{ font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#111827; font-size:12px; margin:0; padding:0; }
      
      /* Cover Page with Background Image */
      .cover-page{ 
        width: 210mm;
        height: 297mm;
        page-break-after: always;
        position: relative;
        background-image: url('<?php echo esc_url($cover_bg); ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      }
      .cover-logo{
        position: absolute;
        top: 50px;
        left: 30px;
        z-index: 10;
      }
      .cover-logo img{
        height: 60px;
        width: auto;
      }
      .cover-title-section{
        position: absolute;
        top: 20%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 650px;
        text-align: center;
        z-index: 10;
      }
      .cover-title{
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin: 0;
        line-height: 1.3;
      }
      .cover-left-text{
        position: absolute;
        top: 45%;
        left: 30px;
        transform: translateY(-50%);
        font-size: 23px;
        font-weight: 400;
        color: #ffffffff;
        line-height: 1.4;
        max-width: 340px;
        z-index: 10;
      }
      .cover-bottom-right{
        position: absolute;
        bottom: 65px;
        right: 30px;
        font-size: 16px;
        font-weight: 400;
        color: #8caaf5;
        z-index: 10;
      }
      .cover-bottom-left{
        position: absolute;
        bottom: 20px;
        left: 30px;
        font-size: 9px;
        font-weight: 400;
        color: #111827;
        z-index: 10;
        line-height: 1.3;
      }
      .cover-phone{
        position: absolute;
        bottom: 20px;
        right: 30px;
        font-size: 10px;
        font-weight: 400;
        color: #111827;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 4px;
      }
      .cover-phone-icon{
        width: 12px;
        height: 12px;
      }
      
      /* Content Pages Styles */
      .content-page{
        padding: 0;
        position: relative;
        min-height: 297mm;
        background-image: url('<?php echo esc_url(get_stylesheet_directory_uri() . "/assets/files/template/hjcoverpage.jpg"); ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        page-break-after: always;
        display: flex;
        flex-direction: column;
        /* Attach to named page definition */
        page: content;
      }
      .content-page:last-of-type {
        page-break-after: auto;
      }
      .content-page-inner{
        /* Top/bottom spacing handled by @page content margins */
        padding: 0 24mm 0 14mm;
        position: relative;
        z-index: 5;
        flex: 1;
      }
      .content-bottom-left{
        position: fixed;
        bottom: 15px;
        left: 30px;
        font-size: 9px;
        font-weight: 400;
        color: #111827;
        z-index: 10;
        line-height: 1.3;
      }
      .content-phone{
        position: fixed;
        bottom: 15px;
        right: 30px;
        font-size: 10px;
        font-weight: 400;
        color: #111827;
        z-index: 10;
      }
      .content-bottom-right{
        position: fixed;
        bottom: 55px;
        right: 30px;
        font-size: 16px;
        font-weight: 400;
        color: #8caaf5;
        z-index: 10;
      }
      .head{ margin-bottom: 12px; }
      .head table{ width: 100%; }
      .head-left{ text-align: left; }
      .head-right{ text-align: right; }
      .logo{ height:26px; }
      .logo img{ height:26px; width:auto; }
      .title{ font-size: 20px; font-weight:700; line-height:1.25; }
      .muted{ color:#6b7280; font-size:11px; margin-top:4px; }
      .card{ border:1px solid #e5e7eb; border-radius:12px; padding:10px 12px; margin:0 0 9px; page-break-inside: avoid; }
      .sect{ font-size:15px; font-weight:700; margin:8px 0 5px; page-break-after: avoid; }
      ul{ margin:5px 0 8px 16px; page-break-inside: avoid; list-style: none; padding-left: 0; }
      li{ margin:3px 0; line-height:1.35; padding-left: 22px; position: relative; }
      li:before{ content: '✓'; position: absolute; left: 0; top: -1px; color: #2563EB; font-weight: bold; font-size: 16px; }
      .note{ color:#6b7280; font-size:11px; line-height:1.35; }
      .price{ font-weight:800; }
      .price table{ width: 100%; }
      .price-amt{ font-size:20px }
      .currency{ opacity:.85 }
      .footer{ text-align:center; font-size:11px; color:#374151; margin-top: 12px; padding-top: 8px; border-top:1px solid #e5e7eb; }
      p{ margin:5px 0; line-height:1.35; }
    </style>
  </head>
  <body>
    
    <!-- Cover Page -->
    <div class="cover-page">
      <div class="cover-logo">
        <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($site); ?>" />
      </div>
      
      <div class="cover-left-text">
        Experience Peace Of Mind<br>
        Together With Us In Turkey.
      </div>
      
      <div class="cover-title-section">
        <div class="cover-title"><?php echo esc_html($package_title); ?></div>
      </div>
      
      <div class="cover-bottom-right">
        Healing Journey®
      </div>
      
      <div class="cover-bottom-left">
        Fener Mah. Fener Cd. No:11, Fener İş Merkezi, B2 Blok, kapı no:204 Muratpaşa/Antalya/TÜRKİYE
      </div>
      
      <div class="cover-phone">
        Tel. +90 555 086 91 12
      </div>
    </div>
    
    <!-- Content Pages -->
    <div class="content-page">
    
    <div class="content-bottom-right">
      Healing Journey®
    </div>
    
    <div class="content-bottom-left">
      Fener Mah. Fener Cd. No:11, Fener İş Merkezi, B2 Blok, kapı no:204 Muratpaşa/Antalya/TÜRKİYE
    </div>
    
    <div class="content-phone">
      Tel. +90 555 086 91 12
    </div>
    
    <div class="content-page-inner">

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
          <div class="page-break"></div>
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
    </div><!-- .content-page-inner -->
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
