<?php
$module_title = get_sub_field('module_title');
$currency = get_sub_field('currency') ?: '€';
$sections = get_sub_field('sections') ?: [];
$uid = uniqid('hj-pa-');
?>
<section class="hj-pricelist-accordion" id="<?php echo esc_attr($uid); ?>" aria-label="Pricelist">
  <div class="hj-pa-wrap">
    <?php if ($module_title): ?><h2 class="hj-pa-title hj-hd-title"><?php echo esc_html($module_title); ?></h2><?php endif; ?>

    <?php if (!empty($sections)): ?>
      <div class="hj-pa-tabs">
        <div class="tab-list" role="tablist" aria-label="Price sections">
          <?php foreach ($sections as $i => $section): $st = $section['section_title'] ?? ''; if (!$st) continue; ?>
            <button type="button" class="tab <?php echo $i===0? 'is-active' : ''; ?>" role="tab" id="<?php echo esc_attr($uid.'-tab-'.$i); ?>" aria-controls="<?php echo esc_attr($uid.'-panel-'.$i); ?>" aria-selected="<?php echo $i===0? 'true':'false'; ?>">
              <?php echo esc_html($st); ?>
            </button>
          <?php endforeach; ?>
        </div>

        <div class="tab-panels">
          <?php foreach ($sections as $i => $section): 
            $st = $section['section_title'] ?? '';
            $is_package = !empty($section['package_view']);
            $items = $section['items'] ?? [];
            $has_content = $is_package || (!empty($items));
            if (!$has_content) continue;
          ?>
            <div class="tab-panel <?php echo $i===0? 'is-active' : ''; ?>" role="tabpanel" id="<?php echo esc_attr($uid.'-panel-'.$i); ?>" aria-labelledby="<?php echo esc_attr($uid.'-tab-'.$i); ?>" <?php echo $i===0? '' : 'hidden'; ?>>
              <?php if ($st): ?><button type="button" class="hj-pa-mob-section" aria-expanded="<?php echo $i===0? 'true':'false'; ?>"><?php echo esc_html($st); ?></button><?php endif; ?>

              <?php if ($is_package): ?>
                <div class="hj-pa-list hj-pa-package">
                  <div class="hj-pa-package-inner">
                    <?php 
                      $mode = $section['package_content_mode'] ?? 'template';
                      if ($mode === 'html') {
                        $html = $section['package_content_html'] ?? '';
                        if ($html) { echo do_shortcode($html); }
                      } elseif ($mode === 'wysiwyg') {
                        $pkg = $section['package_content'] ?? '';
                        if ($pkg) { echo apply_filters('the_content', $pkg); }
                      } else {
                        $pkg = $section['package_template'] ?? [];
                        $slug = isset($pkg['slug']) && $pkg['slug'] !== '' ? sanitize_title($pkg['slug']) : '';
                        $cls_mod = $slug ? ' hj-package--' . esc_attr($slug) : '';
                    ?>
                        <section class="hj-package<?php echo $cls_mod; ?>" id="<?php echo esc_attr($uid.'-pkg-'.$i); ?>">
                          <?php 
                            $pt = $pkg['title'] ?? '';
                            $ps = $pkg['subtitle'] ?? '';
                            if ($pt || $ps): ?>
                            <header class="hj-package__header">
                              <div class="hj-package__header-text">
                                <?php if ($pt): ?><h2 class="hj-package__title"><?php echo esc_html($pt); ?></h2><?php endif; ?>
                                <?php if ($ps): ?><p class="hj-package__subtitle"><?php echo wp_kses_post(nl2br($ps)); ?></p><?php endif; ?>
                              </div>
                              <div class="hj-package__actions">
                                <a class="hj-package__act hj-package__act--pdf" href="<?php echo esc_url(function_exists('hj_get_package_pdf_url') ? hj_get_package_pdf_url(get_the_ID()) : '#'); ?>" title="Download PDF" target="_blank" rel="noopener">
                                  <img src="<?php echo esc_url(get_stylesheet_directory_uri().'/assets/img/pdf-93.png'); ?>" alt="PDF" />
                                </a>
                                <a class="hj-package__act hj-package__act--print" href="#" title="Print Package Offer">
                                  <img src="<?php echo esc_url(get_stylesheet_directory_uri().'/assets/img/print.svg'); ?>" alt="Print" />
                                </a>
                              </div>
                            </header>
                          <?php endif; ?>


                          <?php 
                            $highs = $pkg['highlights']['highlights'] ?? ($pkg['highlights'] ?? []);
                            if (!empty($highs)):
                          ?>
                          <section class="hj-package__section hj-package__section--highlights">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title">Highlights</h3></summary>
                              <div class="hj-package__body">
                                <ul class="hj-package__list">
                                  <?php foreach ($highs as $h): $text = is_array($h) ? ($h['text'] ?? '') : $h; if (!$text) continue; ?>
                                    <li>
                                      <span class="ic">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                          <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                      </span>
                                      <span class="tx"><?php echo esc_html($text); ?></span>
                                    </li>
                                  <?php endforeach; ?>
                                </ul>
                              </div>
                            </details>
                          </section>
                          <?php endif; ?>

                          <?php $fd = $pkg['full_details'] ?? [];
                            $fd_title = $fd['title'] ?? '';
                            $fd_sub = $fd['subheading'] ?? '';
                            $fd_ps = $fd['paragraphs'] ?? [];
                            if ($fd_title || $fd_sub || !empty($fd_ps)):
                          ?>
                          <section class="hj-package__section">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title"><?php echo esc_html($fd_title ?: 'Full Details'); ?></h3></summary>
                              <div class="hj-package__body">
                                <?php if ($fd_sub): ?><h4 class="hj-package__subheading"><?php echo esc_html($fd_sub); ?></h4><?php endif; ?>
                                <?php foreach ($fd_ps as $p): $txt = is_array($p) ? ($p['p'] ?? '') : $p; if (!$txt) continue; ?>
                                  <p><?php echo wp_kses_post($txt); ?></p>
                                <?php endforeach; ?>
                              </div>
                            </details>
                          </section>
                          <?php endif; ?>

                          <?php $ms = $pkg['medical'] ?? [];
                            $ms_title = $ms['title'] ?? '';
                            $ms_intro = $ms['intro'] ?? '';
                            $ms_list = $ms['list'] ?? [];
                            $ms_note = $ms['note'] ?? '';
                            if ($ms_title || $ms_intro || !empty($ms_list) || $ms_note):
                          ?>
                          <section class="hj-package__section">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title"><?php echo esc_html($ms_title ?: 'Medical Suitability Assessment'); ?></h3></summary>
                              <div class="hj-package__body">
                                <?php if ($ms_intro): ?><p><?php echo wp_kses_post($ms_intro); ?></p><?php endif; ?>
                                <?php if (!empty($ms_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($ms_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                                <?php if ($ms_note): ?><p><?php echo wp_kses_post($ms_note); ?></p><?php endif; ?>
                              </div>
                            </details>
                          </section>
                          <?php endif; ?>

                          <?php $ov = $pkg['overview'] ?? [];
                            $ov_title = $ov['title'] ?? '';
                            $ov_intro = $ov['intro'] ?? '';
                            $v1_title = $ov['visit1_title'] ?? '';
                            $v1_list = $ov['visit1_list'] ?? [];
                            $v2_title = $ov['visit2_title'] ?? '';
                            $v2_list = $ov['visit2_list'] ?? [];
                            $ov_note = $ov['note'] ?? '';
                            if ($ov_title || $ov_intro || $v1_title || !empty($v1_list) || $v2_title || !empty($v2_list) || $ov_note):
                          ?>
                          <section class="hj-package__section">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title"><?php echo esc_html($ov_title ?: 'All-on-4 Package Overview (Single Arch)'); ?></h3></summary>
                              <div class="hj-package__body">
                                <?php if ($ov_intro): ?><p><?php echo wp_kses_post($ov_intro); ?></p><?php endif; ?>
                                <?php if ($v1_title): ?><h4 class="hj-package__subheading"><?php echo esc_html($v1_title); ?></h4><?php endif; ?>
                                <?php if (!empty($v1_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($v1_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                                <?php if ($v2_title): ?><h4 class="hj-package__subheading"><?php echo esc_html($v2_title); ?></h4><?php endif; ?>
                                <?php if (!empty($v2_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($v2_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                                <?php if ($ov_note): ?><p><?php echo esc_html($ov_note); ?></p><?php endif; ?>
                              </div>
                            </details>
                          </section>
                          <?php endif; ?>

                          <?php $in = $pkg['inclusions'] ?? [];
                            $in_title = $in['title'] ?? '';
                            $surg_title = $in['surg_title'] ?? '';
                            $surg_list = $in['surg_list'] ?? [];
                            $sup_title = $in['sup_title'] ?? '';
                            $sup_list = $in['sup_list'] ?? [];
                            if ($in_title || $surg_title || !empty($surg_list) || $sup_title || !empty($sup_list)):
                          ?>
                          <section class="hj-package__section">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title"><?php echo esc_html($in_title ?: 'What the Package Includes (Single Arch)'); ?></h3></summary>
                              <div class="hj-package__body">
                                <?php if ($surg_title): ?><h4 class="hj-package__subheading"><?php echo esc_html($surg_title); ?></h4><?php endif; ?>
                                <?php if (!empty($surg_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($surg_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                                <?php if ($sup_title): ?><h4 class="hj-package__subheading"><?php echo esc_html($sup_title); ?></h4><?php endif; ?>
                                <?php if (!empty($sup_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($sup_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                              </div>
                            </details>
                          </section>
                          <?php endif; ?>

                          <?php $tr = $pkg['travel'] ?? [];
                            $tr_title = $tr['title'] ?? '';
                            $tr_list = $tr['list'] ?? [];
                            if ($tr_title || !empty($tr_list)):
                          ?>
                          <section class="hj-package__section">
                            <details class="hj-package__accordion">
                              <summary><h3 class="hj-package__section-title"><?php echo esc_html($tr_title ?: 'Travel & Accommodation'); ?></h3></summary>
                              <div class="hj-package__body">
                                <?php if (!empty($tr_list)): ?>
                                  <ul class="hj-package__list">
                                    <?php foreach ($tr_list as $li): $txt = is_array($li) ? ($li['text'] ?? '') : $li; if (!$txt) continue; ?>
                                      <li>
                                        <span class="ic">
                                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="11" stroke="#60A5FA" stroke-width="2"></circle>
                                            <path d="M7 12.5l3 3 7-7" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                        </span>
                                        <span class="tx"><?php echo esc_html($txt); ?></span>
                                      </li>
                                    <?php endforeach; ?>
                                  </ul>
                                <?php endif; ?>
                              </div>
                            </details>
                          </section>
                          
                          <?php endif; ?>

                          <?php $pr = $pkg['price'] ?? [];
                            $pr_title = $pr['title'] ?? '';
                            $pr_amount = $pr['amount'] ?? '';
                            $pr_curr = $pr['currency'] ?? '';
                            $pr_note = $pr['note'] ?? '';
                            if ($pr_title || $pr_amount || $pr_note):
                          ?>
                          <section class="hj-package__section hj-package__section--price">
                            <div class="hj-package__price-header">
                              <h3 class="hj-package__section-title"><?php echo esc_html($pr_title ?: 'Final Full-Arch Restoration (Single Arch)'); ?></h3>
                              <?php if ($pr_amount || $pr_curr): ?>
                                <p class="hj-package__price">
                                  <?php if ($pr_amount): ?><span class="hj-package__price-amount"><?php echo esc_html($pr_amount); ?></span><?php endif; ?>
                                  <?php if ($pr_curr): ?><span class="hj-package__price-currency"><?php echo esc_html($pr_curr); ?></span><?php endif; ?>
                                </p>
                              <?php endif; ?>
                            </div>
                            <?php if ($pr_note): ?><p class="hj-package__price-note"><?php echo esc_html($pr_note); ?></p><?php endif; ?>
                          </section>
                          <?php endif; ?>
                        </section>
                    <?php }
                    ?>
                  </div>
                </div>
              <?php else: ?>
                <ul class="hj-pa-list" role="list">
                  <?php $idx=0; foreach ($items as $it): $t = $it['item_title'] ?? ''; if (!$t) { $idx++; continue; } $p = $it['item_price'] ?? ''; $d = $it['item_desc'] ?? ''; $is_first = ($idx===0); ?>
                    <li class="hj-pa-item">
                      <details <?php echo $is_first? 'open':''; ?>>
                        <summary>
                          <span class="ind" aria-hidden="true"></span>
                          <span class="t"><?php echo esc_html($t); ?></span>
                          <span class="dots" aria-hidden="true"></span>
                          <?php if ($p !== ''): ?>
                            <span class="price"><span class="curr"><?php echo esc_html($currency); ?></span><span class="amt"><?php echo esc_html($p); ?></span></span>
                          <?php endif; ?>
                        </summary>
                        <?php if ($d): ?><div class="desc"><p><?php echo esc_html($d); ?></p></div><?php endif; ?>
                      </details>
                    </li>
                  <?php $idx++; endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('<?php echo esc_js($uid); ?>');
    if(!root) return;
    const cssUrl = '<?php echo esc_js(get_stylesheet_directory_uri().'/assets/css/modules/pricelist-accordion.css'); ?>';
    const fontUrl = 'https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap';
    const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
    const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
    // On mobile we stack sections; transform into single-open accordion by groups
    if (window.matchMedia('(max-width: 700px)').matches) {
      panels.forEach((p,i)=>{
        p.removeAttribute('hidden');
        const header = p.querySelector('.hj-pa-mob-section');
        const list = p.querySelector('.hj-pa-list');
        if(!header || !list) return;
        if(i===0){
          header.classList.add('is-open');
          header.setAttribute('aria-expanded','true');
        } else {
          p.classList.add('mob-collapsed');
          list.style.display = 'none';
          header.setAttribute('aria-expanded','false');
        }
        header.addEventListener('click', function(){
          if(header.classList.contains('is-open')){
            // toggle close
            header.classList.remove('is-open');
            header.setAttribute('aria-expanded','false');
            list.style.display = 'none';
            p.classList.add('mob-collapsed');
          } else {
            // collapse others then open this
            panels.forEach(pp=>{
              const h = pp.querySelector('.hj-pa-mob-section');
              const l = pp.querySelector('.hj-pa-list');
              if(!h || !l) return;
              h.classList.remove('is-open');
              h.setAttribute('aria-expanded','false');
              l.style.display = 'none';
              pp.classList.add('mob-collapsed');
            });
            header.classList.add('is-open');
            header.setAttribute('aria-expanded','true');
            list.style.display = '';
            p.classList.remove('mob-collapsed');
            initAccordions(p);
          }
        });
      });
      return;
    }
    function initAccordions(panel){
      const items = Array.from(panel.querySelectorAll('details:not(.hj-package__accordion)'));
      if(items.length===0) return;
      // ensure at least first open
      if(!items.some(d=>d.open)) items[0].open = true;
      items.forEach(d=>{
        d.addEventListener('toggle', ()=>{
          if(d.open){ items.forEach(o=>{ if(o!==d) o.open=false; }); }
        });
      });
    }
    panels.forEach(p=>initAccordions(p));
    function activate(index){
      tabs.forEach((t,i)=>{
        const sel = i===index;
        t.classList.toggle('is-active', sel);
        t.setAttribute('aria-selected', sel ? 'true':'false');
      });
      panels.forEach((p,i)=>{
        const sel = i===index;
        p.classList.toggle('is-active', sel);
        if(sel){ p.removeAttribute('hidden'); initAccordions(p); } else { p.setAttribute('hidden',''); }
      });
    }
    tabs.forEach((t,i)=> t.addEventListener('click', ()=>activate(i)));

    // Print only the package section
    root.addEventListener('click', function(e){
      const btn = e.target.closest('.hj-package__act--print');
      if(!btn) return;
      e.preventDefault();
      const pkg = btn.closest('.hj-package');
      if(!pkg) return;
      const html = `<!doctype html><html><head><meta charset="utf-8"><title>Package Offer</title>
        <link rel="stylesheet" href="${cssUrl}">
        <link rel="stylesheet" href="${fontUrl}">
        <style>
          body{padding:24px}
        </style>
      </head><body>${pkg.outerHTML}</body></html>`;
      const w = window.open('', '_blank');
      if(!w) return;
      w.document.open();
      w.document.write(html);
      w.document.close();
      w.focus();
      setTimeout(()=>{ w.print(); }, 400);
    });
  });
  </script>
</section>
