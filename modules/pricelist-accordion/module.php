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
          <?php foreach ($sections as $i => $section): $items = $section['items'] ?? []; if (empty($items)) continue; ?>
            <div class="tab-panel <?php echo $i===0? 'is-active' : ''; ?>" role="tabpanel" id="<?php echo esc_attr($uid.'-panel-'.$i); ?>" aria-labelledby="<?php echo esc_attr($uid.'-tab-'.$i); ?>" <?php echo $i===0? '' : 'hidden'; ?>>
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
    const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
    const panels = Array.from(root.querySelectorAll('[role="tabpanel"]'));
    function initAccordions(panel){
      const items = Array.from(panel.querySelectorAll('details'));
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
  });
  </script>
</section>
